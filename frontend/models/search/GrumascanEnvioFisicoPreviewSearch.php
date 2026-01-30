<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\db\Expression;
use frontend\models\Grumascanconteodetalle;

class GrumascanEnvioFisicoPreviewSearch extends Model
{
    public $codigoBodega;  // ej: 210
    public $fechaDesde;    // YYYY-MM-DD (desde DatePicker)

    private $_lastCommand = null;

    public function rules(): array
    {
        return [
            [['codigoBodega', 'fechaDesde'], 'required'],
            ['codigoBodega', 'string', 'max' => 10],
            ['codigoBodega', 'trim'],
            ['fechaDesde', 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'codigoBodega' => 'Código bodega',
            'fechaDesde'   => 'Fecha (día)',
        ];
    }

    /**
     * Rango de fecha INVARIANTE para SQL Server: YYYYMMDD (style 112).
     */
    private function getDateRangeSafeYmd(): array
    {
        $ymd = str_replace('-', '', $this->fechaDesde); // 20260109
        $ymdNext = date('Ymd', strtotime($this->fechaDesde . ' +1 day')); // 20260110
        return [$ymd, $ymdNext];
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
            'bodega_nombre'   => 'b.nombre',
            'bodega_codigo'   => 'b.codigo',
            'item'            => 'i.item',
            'color'           => 'c.codigo',
            'talla'           => 't.codigo',
            'cantidad_unidad' => $cantidadUnidad,
            'cantidad_paquetes' => 'det.cantidad',
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
                'totales' => ['lineas' => 0, 'total_unidades' => 0],
                'errors' => $this->getErrors(),
            ];
        }

        $codigo = trim((string)$this->codigoBodega);
        [$desdeYmd, $hastaYmd] = $this->getDateRangeSafeYmd();

        $query = $this->buildQuery();

        // b.codigo tiene padding (CHAR/NCHAR) => TRIM
        $query->andWhere(new Expression('LTRIM(RTRIM(b.codigo)) = :cod', [':cod' => $codigo]));

        /**
         * FILTRO FECHA INVARIANTE:
         * Usamos CONVERT(datetime, :param, 112) donde :param es YYYYMMDD.
         */
        $query->andWhere(new Expression(
            "con.created_at >= CONVERT(datetime, :desde, 112)",
            [':desde' => $desdeYmd]
        ));
        $query->andWhere(new Expression(
            "con.created_at <  CONVERT(datetime, :hasta, 112)",
            [':hasta' => $hastaYmd]
        ));

        // Consolidado por SKU lógico
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

        // Guardar command (por si necesitas debug)
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
                'desdeYmd' => $desdeYmd,
                'hastaYmd' => $hastaYmd,
            ],
        ];
    }

    /**
     * Debug opcional: SQL con parámetros ya interpolados.
     */
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
