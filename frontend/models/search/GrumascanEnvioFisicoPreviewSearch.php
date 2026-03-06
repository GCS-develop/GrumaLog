<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\db\Expression;
use frontend\models\Grumascanconteodetalle;

class GrumascanEnvioFisicoPreviewSearch extends Model
{
    public $codigoBodega;  // ej: 210
    public $fechaDesde;    // YYYY-MM-DD
    public $fechaHasta;    // YYYY-MM-DD

    private $_lastCommand = null;

    public function rules(): array
    {
        return [
            [['codigoBodega'], 'required'],
            [['codigoBodega'], 'string', 'max' => 10],
            [['codigoBodega'], 'trim'],

            // Fechas opcionales (pero validadas si vienen)
            [['fechaDesde', 'fechaHasta'], 'trim'],
            [['fechaDesde', 'fechaHasta'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'codigoBodega' => 'Código bodega',
            'fechaDesde'   => 'Fecha desde',
            'fechaHasta'   => 'Fecha hasta',
        ];
    }

    private function buildQuery()
    {
        $query = Grumascanconteodetalle::find()->alias('det');

        // JOINs directos (tu patrón)
        $query->innerJoin('grumascanconteo con', 'con.id = det.idgrumascanconteo');
        $query->innerJoin('grumascanmarcacion mar', 'mar.id = con.idmarcacion');
        $query->innerJoin('bodegas b', 'b.id = mar.idbodega');
        $query->innerJoin('item i', 'i.id = det.iditem');
        $query->innerJoin('color c', 'c.id = i.idColor');
        $query->innerJoin('talla t', 't.id = i.idTalla');
        $query->leftJoin('unidadempaque und', 'und.codigo = i.unidadEmpaque');

        // Conversión a unidades
        $factor = new Expression("
            CASE
                WHEN i.unidadEmpaque IS NULL THEN 1
                ELSE ISNULL(und.equivalencia, 1)
            END
        ");

        $cantidadUnidad = new Expression("SUM(det.cantidad * {$factor})");

        $query->select([
            'bodega_nombre'     => 'b.nombre',
            'bodega_codigo'     => 'b.codigo',
            'item'              => 'i.item',
            'color'             => 'c.codigo',
            'talla'             => 't.codigo',
            'cantidad_unidad'   => $cantidadUnidad,
            'cantidad_paquetes' => 'det.cantidad', // lo dejo igual para no alterar tu salida actual
        ]);

        // Terminado (estado=1)
        $query->andWhere(['con.idestado' => 1]);

        return $query;
    }

    /**
     * Ejecuta preview consolidado.
     * Retorna rows + totales.
     */
    public function preview(array $params): array
    {
        $this->load($params);

        if (!$this->validate()) {
            return [
                'rows' => [],
                'totales' => ['lineas' => 0, 'total_unidades' => 0, 'total_paquetes' => 0],
                'errors' => $this->getErrors(),
            ];
        }

        $codigo = trim((string)$this->codigoBodega);
        $desde  = trim((string)$this->fechaDesde);
        $hasta  = trim((string)$this->fechaHasta);

        $query = $this->buildQuery();

        // b.codigo tiene padding (CHAR/NCHAR) => TRIM
        $query->andWhere(new Expression('LTRIM(RTRIM(b.codigo)) = :cod', [':cod' => $codigo]));

        /**
         * ✅ FILTRO FECHA ESTÁNDAR TIPO GRUMASCAN (SQL Server)
         * - Ignora hora: CAST(con.created_at AS DATE)
         * - desde solo: >=
         * - hasta solo: <=
         * - ambas: BETWEEN inclusive
         * - vacías: no filtra
         */
        if ($desde !== '' && $hasta !== '') {
            $query->andWhere(new Expression(
                "CAST(con.created_at AS DATE) BETWEEN :desde AND :hasta",
                [':desde' => $desde, ':hasta' => $hasta]
            ));
        } elseif ($desde !== '') {
            $query->andWhere(new Expression(
                "CAST(con.created_at AS DATE) >= :desde",
                [':desde' => $desde]
            ));
        } elseif ($hasta !== '') {
            $query->andWhere(new Expression(
                "CAST(con.created_at AS DATE) <= :hasta",
                [':hasta' => $hasta]
            ));
        }
        // si ambas vacías -> no aplica filtro

        // Consolidado por SKU lógico (lo dejo igual a tu versión)
        $query->groupBy([
            'b.nombre',
            'b.codigo',
            'i.item',
            'c.codigo',
            't.codigo',
            'det.cantidad',
        ]);

        $query->orderBy([
            'i.item' => SORT_ASC,
            'c.codigo' => SORT_ASC,
            't.codigo' => SORT_ASC,
        ]);

        $cmd = $query->createCommand();
        $this->_lastCommand = $cmd;

        $rows = $cmd->queryAll();

        $sum = 0;
        $paq = 0;
        foreach ($rows as $r) {
            $sum += (int)$r['cantidad_unidad'];
            $paq += (int)$r['cantidad_paquetes'];
        }

        return [
            'rows' => $rows,
            'totales' => [
                'lineas' => count($rows),
                'total_unidades' => $sum,
                'total_paquetes' => $paq,
            ],
            'errors' => [],
            'debug' => [
                'codigo' => $codigo,
                'fechaDesde' => $desde,
                'fechaHasta' => $hasta,
            ],
        ];
    }

    public function debugSql(): array
    {
        if ($this->_lastCommand === null) {
            return ['sql' => null, 'params' => null];
        }
        return [
            'sql' => $this->_lastCommand->getRawSql(),
            'params' => $this->_lastCommand->params,
        ];
    }
}
