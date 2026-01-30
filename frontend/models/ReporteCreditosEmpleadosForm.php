<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;

class ReporteCreditosEmpleadosForm extends Model
{
    // 🔹 Filtros principales
    public $fecha_inicio;
    public $fecha_fin;

    // 🔎 Filtros por columna (deben coincidir con los alias del SELECT)
    public $ID_TERCERO;
    public $RAZON_SOCIAL;
    public $SUCURSAL_CLIENTE;
    public $CENTRO_OPERACION;
    public $UNIDAD_DE_NEGOCIO;
    public $TIPO_DOCUMENTO_CRUCE;
    public $NUMERO_DOCUMENTO_CRUCE;
    public $NUMERO_CUOTA_CRUCE;
    public $NUM_CUOTAS;   // ⭐ NUEVO ⭐
    public $CONDICION_PAGO;
    public $VALOR;
    public $FECHA;

    public function rules()
    {
        return [
            [['fecha_inicio', 'fecha_fin'], 'required'],
            [['fecha_inicio', 'fecha_fin', 'FECHA'], 'date', 'format' => 'php:Y-m-d'],

            [[
                'ID_TERCERO','RAZON_SOCIAL','SUCURSAL_CLIENTE',
                'CENTRO_OPERACION','UNIDAD_DE_NEGOCIO',
                'TIPO_DOCUMENTO_CRUCE','NUMERO_DOCUMENTO_CRUCE',
                'NUMERO_CUOTA_CRUCE','CONDICION_PAGO', 'NUM_CUOTAS'
            ], 'trim'],

            [[
                'ID_TERCERO','RAZON_SOCIAL','SUCURSAL_CLIENTE',
                'CENTRO_OPERACION','UNIDAD_DE_NEGOCIO',
                'TIPO_DOCUMENTO_CRUCE','NUMERO_DOCUMENTO_CRUCE',
                'NUMERO_CUOTA_CRUCE','CONDICION_PAGO','NUM_CUOTAS'
            ], 'string'],

            [['VALOR'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fecha_inicio'           => 'Fecha inicio',
            'fecha_fin'              => 'Fecha fin',
            'ID_TERCERO'             => 'ID Tercero',
            'RAZON_SOCIAL'           => 'Razón Social',
            'SUCURSAL_CLIENTE'       => 'Sucursal Cliente',
            'CENTRO_OPERACION'       => 'Centro Operación',
            'UNIDAD_DE_NEGOCIO'      => 'Unidad de Negocio',
            'TIPO_DOCUMENTO_CRUCE'   => 'Tipo Documento Cruce',
            'NUMERO_DOCUMENTO_CRUCE' => 'Número Documento Cruce',
            'NUMERO_CUOTA_CRUCE'     => 'Número Cuota Cruce',
            'NUM_CUOTAS'             => 'Total Cuotas', // ⭐ NUEVO ⭐
            'CONDICION_PAGO'         => 'Condición de Pago',
            'VALOR'                  => 'Valor',
            'FECHA'                  => 'Fecha',
        ];
    }

    /**
     * 🔹 SQL principal del reporte
     */
   private function baseSql()
{
    return <<<SQL
SELECT DISTINCT 
    T.f200_nit AS ID_TERCERO,
    T.f200_razon_social AS RAZON_SOCIAL,
    SA.f353_id_sucursal AS SUCURSAL_CLIENTE,
    SA.F353_ID_CO_CRUCE AS CENTRO_OPERACION,
    SA.F353_ID_UN_CRUCE AS UNIDAD_DE_NEGOCIO,
    SA.f353_id_tipo_docto_cruce AS TIPO_DOCUMENTO_CRUCE,
    SA.F353_CONSEC_DOCTO_CRUCE AS NUMERO_DOCUMENTO_CRUCE,
    SA.F353_NRO_CUOTA_CRUCE AS NUMERO_CUOTA_CRUCE,
    SA.f353_id_cond_pago AS CONDICION_PAGO,
    SA.F353_TOTAL_DB AS VALOR,
    SA.F353_FECHA AS FECHA,

    COUNT(*) OVER (
        PARTITION BY 
            SA.f353_id_tipo_docto_cruce,
            SA.F353_CONSEC_DOCTO_CRUCE
    ) AS NUM_CUOTAS

FROM t353_co_saldo_abierto AS SA
INNER JOIN t200_mm_terceros AS T 
    ON SA.f353_rowid_tercero = T.f200_rowid

WHERE 
    SA.F353_FECHA >= :fini
    AND SA.F353_FECHA < DATEADD(day, 1, :ffin)
    AND SA.f353_rowid_auxiliar = 20805
    AND SA.f353_ind_anticipo = 0
    AND SA.F353_TOTAL_CR = 0
SQL;
}

    /**
     * 🔍 Construye los filtros dinámicos
     */
    private function buildFiltersSql(array &$params): string
    {
        $and = [];

        if ($this->ID_TERCERO) {
            $and[] = 'T.f200_nit LIKE :f_tercero';
            $params[':f_tercero'] = '%' . $this->ID_TERCERO . '%';
        }
        if ($this->RAZON_SOCIAL) {
            $and[] = 'T.f200_razon_social LIKE :f_razon';
            $params[':f_razon'] = '%' . $this->RAZON_SOCIAL . '%';
        }
        if ($this->SUCURSAL_CLIENTE) {
            $and[] = 'SA.f353_id_sucursal LIKE :f_suc';
            $params[':f_suc'] = '%' . $this->SUCURSAL_CLIENTE . '%';
        }
        if ($this->CENTRO_OPERACION) {
            $and[] = 'SA.F353_ID_CO_CRUCE LIKE :f_co';
            $params[':f_co'] = '%' . $this->CENTRO_OPERACION . '%';
        }
        if ($this->UNIDAD_DE_NEGOCIO) {
            $and[] = 'SA.F353_ID_UN_CRUCE LIKE :f_un';
            $params[':f_un'] = '%' . $this->UNIDAD_DE_NEGOCIO . '%';
        }
        if ($this->TIPO_DOCUMENTO_CRUCE) {
            $and[] = 'SA.f353_id_tipo_docto_cruce LIKE :f_tipodoc';
            $params[':f_tipodoc'] = '%' . $this->TIPO_DOCUMENTO_CRUCE . '%';
        }
        if ($this->NUMERO_DOCUMENTO_CRUCE) {
            $and[] = 'CAST(SA.F353_CONSEC_DOCTO_CRUCE AS VARCHAR(20)) LIKE :f_doc';
            $params[':f_doc'] = '%' . $this->NUMERO_DOCUMENTO_CRUCE . '%';
        }
        if ($this->NUMERO_CUOTA_CRUCE) {
            $and[] = 'CAST(SA.F353_NRO_CUOTA_CRUCE AS VARCHAR(10)) LIKE :f_cuota';
            $params[':f_cuota'] = '%' . $this->NUMERO_CUOTA_CRUCE . '%';
        }
        if ($this->NUM_CUOTAS) {
            $and[] = 'CAST(COUNT(*) OVER (PARTITION BY SA.f353_id_tipo_docto_cruce, SA.F353_CONSEC_DOCTO_CRUCE) AS VARCHAR(10)) LIKE :f_totcuotas';
            $params[':f_totcuotas'] = '%' . $this->NUM_CUOTAS . '%';
        }
        if ($this->CONDICION_PAGO) {
            $and[] = 'SA.f353_id_cond_pago LIKE :f_condpago';
            $params[':f_condpago'] = '%' . $this->CONDICION_PAGO . '%';
        }
        if ($this->VALOR) {
            $and[] = 'SA.F353_TOTAL_DB = :f_valor';
            $params[':f_valor'] = $this->VALOR;
        }
        if ($this->FECHA) {
            $and[] = 'CONVERT(date, SA.F353_FECHA, 120) = CONVERT(date, :f_fecha, 120)';
            $params[':f_fecha'] = $this->FECHA;
        }

        return $and ? "\nAND " . implode("\nAND ", $and) : '';
    }

    /**
     * 🔹 DataProvider principal
     */
    public function dataProvider()
    {
        $params = [
            ':fini' => $this->fecha_inicio,
            ':ffin' => $this->fecha_fin,
        ];

        $filtersSql = $this->buildFiltersSql($params);

        $sqlMainBase = $this->baseSql() . $filtersSql;

        $sqlCount = "SELECT COUNT(*) FROM (" . $sqlMainBase . ") AS conteo";
        $count = (int) Yii::$app->dbSiesa->createCommand($sqlCount, $params)->queryScalar();

        $sqlMain = $sqlMainBase;

        return new SqlDataProvider([
            'sql' => $sqlMain,
            'params' => $params,
            'totalCount' => $count,
            'db' => Yii::$app->dbSiesa,
            'pagination' => false,
            'sort' => [
                'attributes' => [
                    'ID_TERCERO','RAZON_SOCIAL','SUCURSAL_CLIENTE','CENTRO_OPERACION',
                    'UNIDAD_DE_NEGOCIO','TIPO_DOCUMENTO_CRUCE','NUMERO_DOCUMENTO_CRUCE',
                    'NUMERO_CUOTA_CRUCE','NUM_CUOTAS','CONDICION_PAGO','VALOR','FECHA',
                ],
                'defaultOrder' => [
                    'ID_TERCERO' => SORT_ASC,
                    'NUMERO_DOCUMENTO_CRUCE' => SORT_ASC,
                    'NUMERO_CUOTA_CRUCE' => SORT_ASC,
                ],
            ],
        ]);
    }

    /**
     * 🔹 Total general del valor
     */
    public function getTotalValor()
    {
        $params = [
            ':fini' => $this->fecha_inicio,
            ':ffin' => $this->fecha_fin,
        ];

        $filtersSql = $this->buildFiltersSql($params);

     $sql = <<<SQL
SELECT SUM(SA.F353_TOTAL_DB) AS total_valor
FROM t353_co_saldo_abierto AS SA
INNER JOIN t200_mm_terceros AS T 
    ON SA.f353_rowid_tercero = T.f200_rowid
WHERE 
    SA.F353_FECHA >= :fini
    AND SA.F353_FECHA < DATEADD(day, 1, :ffin)
    AND SA.f353_rowid_auxiliar = 20805
    AND SA.f353_ind_anticipo = 0
    AND SA.F353_TOTAL_CR = 0
{$filtersSql}
SQL;


        $total = Yii::$app->dbSiesa->createCommand($sql, $params)->queryScalar();
        return $total ?: 0;
    }
}
