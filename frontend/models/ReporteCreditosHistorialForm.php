<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;

/**
 * Reporte de TODAS las facturas crédito de empleados
 * (saldos abiertos + ya pagadas)
 */
class ReporteCreditosHistorialForm extends Model
{
    public $fecha_inicio;
    public $fecha_fin;

    public $ID_TERCERO;
    public $RAZON_SOCIAL;
    public $SUCURSAL_CLIENTE;
    public $CENTRO_OPERACION;
    public $UNIDAD_DE_NEGOCIO;
    public $TIPO_DOCUMENTO_CRUCE;
    public $NUMERO_DOCUMENTO_CRUCE;
    public $NUMERO_CUOTA_CRUCE;
    public $CONDICION_PAGO;
    public $VALOR;
    public $SALDO_PENDIENTE;
    public $FECHA;
    public $FECHA_VCTO;
    public $ESTADO;

    public function rules()
    {
        return [
            [['fecha_inicio', 'fecha_fin'], 'required'],
            [['fecha_inicio', 'fecha_fin', 'FECHA'], 'date', 'format' => 'php:Y-m-d'],
            [[
                'ID_TERCERO', 'RAZON_SOCIAL', 'SUCURSAL_CLIENTE',
                'CENTRO_OPERACION', 'UNIDAD_DE_NEGOCIO',
                'TIPO_DOCUMENTO_CRUCE', 'NUMERO_DOCUMENTO_CRUCE',
                'NUMERO_CUOTA_CRUCE', 'CONDICION_PAGO', 'ESTADO',
            ], 'trim'],
            [[
                'ID_TERCERO', 'RAZON_SOCIAL', 'SUCURSAL_CLIENTE',
                'CENTRO_OPERACION', 'UNIDAD_DE_NEGOCIO',
                'TIPO_DOCUMENTO_CRUCE', 'NUMERO_DOCUMENTO_CRUCE',
                'NUMERO_CUOTA_CRUCE', 'CONDICION_PAGO', 'ESTADO',
            ], 'string'],
            [['VALOR', 'SALDO_PENDIENTE'], 'number'],
            [['FECHA_VCTO'], 'date', 'format' => 'php:Y-m-d'],
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
            'NUMERO_CUOTA_CRUCE'     => 'N° Cuota',
            'CONDICION_PAGO'         => 'Condición de Pago',
            'VALOR'                  => 'Valor Original',
            'SALDO_PENDIENTE'        => 'Saldo Pendiente',
            'FECHA'                  => 'Fecha',
            'FECHA_VCTO'             => 'Fecha Vcto.',
            'ESTADO'                 => 'Estado',
        ];
    }

    private function baseSql()
    {
        return <<<SQL
SELECT
    T.f200_nit                          AS ID_TERCERO,
    T.f200_razon_social                 AS RAZON_SOCIAL,
    SA.f353_id_sucursal                 AS SUCURSAL_CLIENTE,
    SA.F353_ID_CO_CRUCE                 AS CENTRO_OPERACION,
    SA.F353_ID_UN_CRUCE                 AS UNIDAD_DE_NEGOCIO,
    SA.f353_id_tipo_docto_cruce         AS TIPO_DOCUMENTO_CRUCE,
    SA.F353_CONSEC_DOCTO_CRUCE          AS NUMERO_DOCUMENTO_CRUCE,
    SA.F353_NRO_CUOTA_CRUCE             AS NUMERO_CUOTA_CRUCE,
    SA.f353_id_cond_pago                AS CONDICION_PAGO,
    SA.f353_total_db                    AS VALOR,
    CASE WHEN SA.f353_total_cr = 0
         THEN SA.f353_total_db
         ELSE 0
    END                                 AS SALDO_PENDIENTE,
    SA.f353_fecha                       AS FECHA,
    SA.f353_fecha_vcto                  AS FECHA_VCTO,
    CASE WHEN SA.f353_total_cr = 0
         THEN 'Pendiente'
         ELSE 'Pagado'
    END                                 AS ESTADO
FROM t353_co_saldo_abierto AS SA
INNER JOIN t200_mm_terceros AS T
    ON SA.f353_rowid_tercero = T.f200_rowid
WHERE
    SA.F353_FECHA >= :fini
    AND SA.F353_FECHA < DATEADD(day, 1, :ffin)
    AND SA.f353_rowid_auxiliar = 20805
    AND SA.f353_ind_anticipo = 0
    AND SA.f353_id_tipo_docto_cruce <> 'NC'

SQL;
    }

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
        if ($this->CONDICION_PAGO) {
            $and[] = 'SA.f353_id_cond_pago LIKE :f_condpago';
            $params[':f_condpago'] = '%' . $this->CONDICION_PAGO . '%';
        }
        if ($this->VALOR) {
            $and[] = 'SA.f353_total_db = :f_valor';
            $params[':f_valor'] = $this->VALOR;
        }
        if ($this->FECHA) {
            $and[] = 'CONVERT(date, SA.f353_fecha, 120) = CONVERT(date, :f_fecha, 120)';
            $params[':f_fecha'] = $this->FECHA;
        }
        if ($this->FECHA_VCTO) {
            $and[] = 'CONVERT(date, SA.f353_fecha_vcto, 120) = CONVERT(date, :f_fecha_vcto, 120)';
            $params[':f_fecha_vcto'] = $this->FECHA_VCTO;
        }
        if ($this->ESTADO) {
            if (stripos($this->ESTADO, 'pend') !== false) {
                $and[] = 'SA.f353_total_cr = 0';
            } elseif (stripos($this->ESTADO, 'pag') !== false) {
                $and[] = 'SA.f353_total_cr <> 0';
            }
        }

        return $and ? "\nAND " . implode("\nAND ", $and) : '';
    }

    public function dataProvider()
    {
        $params = [
            ':fini' => $this->fecha_inicio,
            ':ffin' => $this->fecha_fin,
        ];

        $filtersSql = $this->buildFiltersSql($params);
        $sqlBase     = $this->baseSql() . $filtersSql;
        $sqlCount    = "SELECT COUNT(*) FROM ({$sqlBase}) AS conteo";
        $count       = (int) Yii::$app->dbSiesa->createCommand($sqlCount, $params)->queryScalar();

        return new SqlDataProvider([
            'sql'        => $sqlBase,
            'params'     => $params,
            'totalCount' => $count,
            'db'         => Yii::$app->dbSiesa,
            'pagination' => ['pageSize' => 100],
            'sort'       => [
                'attributes'   => [
                    'ID_TERCERO', 'RAZON_SOCIAL', 'SUCURSAL_CLIENTE',
                    'CENTRO_OPERACION', 'UNIDAD_DE_NEGOCIO',
                    'TIPO_DOCUMENTO_CRUCE', 'NUMERO_DOCUMENTO_CRUCE',
                    'NUMERO_CUOTA_CRUCE', 'CONDICION_PAGO', 'VALOR',
                    'SALDO_PENDIENTE', 'FECHA', 'FECHA_VCTO', 'ESTADO',
                ],
                'defaultOrder' => [
                    'ID_TERCERO'             => SORT_ASC,
                    'NUMERO_DOCUMENTO_CRUCE' => SORT_ASC,
                    'NUMERO_CUOTA_CRUCE'     => SORT_ASC,
                ],
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // VISTA AGRUPADA: una fila por factura
    // ─────────────────────────────────────────────────────────────────────

    private function baseSqlAgrupado(): string
    {
        return <<<SQL
SELECT
    T.f200_nit                                                                      AS ID_TERCERO,
    T.f200_razon_social                                                             AS RAZON_SOCIAL,
    SA.f353_id_sucursal                                                             AS SUCURSAL_CLIENTE,
    SA.F353_ID_CO_CRUCE                                                             AS CENTRO_OPERACION,
    SA.F353_ID_UN_CRUCE                                                             AS UNIDAD_DE_NEGOCIO,
    SA.f353_id_tipo_docto_cruce                                                     AS TIPO_DOCUMENTO_CRUCE,
    SA.F353_CONSEC_DOCTO_CRUCE                                                      AS NUMERO_DOCUMENTO_CRUCE,
    SA.f353_id_cond_pago                                                            AS CONDICION_PAGO,
    MIN(SA.f353_fecha)                                                              AS FECHA,
    MAX(SA.f353_fecha_vcto)                                                         AS ULTIMA_FECHA_VCTO,
    SUM(SA.f353_total_db)                                                           AS VALOR_TOTAL,
    SUM(CASE WHEN SA.f353_total_cr = 0 THEN SA.f353_total_db ELSE 0 END)           AS SALDO_PENDIENTE,
    COUNT(*)                                                                        AS TOTAL_CUOTAS,
    SUM(CASE WHEN SA.f353_total_cr <> 0 THEN 1 ELSE 0 END)                        AS CUOTAS_PAGADAS,
    SUM(CASE WHEN SA.f353_total_cr = 0  THEN 1 ELSE 0 END)                        AS CUOTAS_PENDIENTES,
    CASE WHEN SUM(CASE WHEN SA.f353_total_cr = 0 THEN SA.f353_total_db ELSE 0 END) > 0
         THEN 'Pendiente'
         ELSE 'Pagado'
    END                                                                             AS ESTADO
FROM t353_co_saldo_abierto AS SA
INNER JOIN t200_mm_terceros AS T
    ON SA.f353_rowid_tercero = T.f200_rowid
WHERE
    SA.F353_FECHA >= :fini
    AND SA.F353_FECHA < DATEADD(day, 1, :ffin)
    AND SA.f353_rowid_auxiliar = 20805
    AND SA.f353_ind_anticipo = 0
    AND SA.f353_id_tipo_docto_cruce <> 'NC'
SQL;
    }

    private function groupBySql(): string
    {
        return <<<SQL

GROUP BY
    T.f200_nit,
    T.f200_razon_social,
    SA.f353_id_sucursal,
    SA.F353_ID_CO_CRUCE,
    SA.F353_ID_UN_CRUCE,
    SA.f353_id_tipo_docto_cruce,
    SA.F353_CONSEC_DOCTO_CRUCE,
    SA.f353_id_cond_pago
SQL;
    }

    private function buildFiltersAgrupadoSql(array &$params): string
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
        if ($this->CONDICION_PAGO) {
            $and[] = 'SA.f353_id_cond_pago LIKE :f_condpago';
            $params[':f_condpago'] = '%' . $this->CONDICION_PAGO . '%';
        }
        if ($this->FECHA) {
            $and[] = 'CONVERT(date, SA.f353_fecha, 120) = CONVERT(date, :f_fecha, 120)';
            $params[':f_fecha'] = $this->FECHA;
        }

        return $and ? "\nAND " . implode("\nAND ", $and) : '';
    }

    public function dataProviderAgrupado(): SqlDataProvider
    {
        $params = [
            ':fini' => $this->fecha_inicio,
            ':ffin' => $this->fecha_fin,
        ];

        $innerFilters = $this->buildFiltersAgrupadoSql($params);
        $sqlInner     = $this->baseSqlAgrupado() . $innerFilters . $this->groupBySql();

        // Filtro ESTADO sobre la consulta agrupada (columna calculada)
        $outerWhere = '';
        if ($this->ESTADO) {
            if (stripos($this->ESTADO, 'pend') !== false) {
                $outerWhere = "\nWHERE SALDO_PENDIENTE > 0";
            } elseif (stripos($this->ESTADO, 'pag') !== false) {
                $outerWhere = "\nWHERE SALDO_PENDIENTE = 0";
            }
        }

        $sqlBase  = "SELECT * FROM ({$sqlInner}) AS agrupado{$outerWhere}";
        $sqlCount = "SELECT COUNT(*) FROM ({$sqlBase}) AS conteo";
        $count    = (int) Yii::$app->dbSiesa->createCommand($sqlCount, $params)->queryScalar();

        return new SqlDataProvider([
            'sql'        => $sqlBase,
            'params'     => $params,
            'totalCount' => $count,
            'db'         => Yii::$app->dbSiesa,
            'pagination' => ['pageSize' => 50],
            'sort'       => [
                'attributes'   => [
                    'ID_TERCERO', 'RAZON_SOCIAL', 'SUCURSAL_CLIENTE',
                    'CENTRO_OPERACION', 'UNIDAD_DE_NEGOCIO',
                    'TIPO_DOCUMENTO_CRUCE', 'NUMERO_DOCUMENTO_CRUCE',
                    'CONDICION_PAGO', 'FECHA', 'ULTIMA_FECHA_VCTO',
                    'VALOR_TOTAL', 'SALDO_PENDIENTE', 'TOTAL_CUOTAS', 'ESTADO',
                ],
                'defaultOrder' => [
                    'FECHA' => SORT_DESC,
                ],
            ],
        ]);
    }

    /**
     * Devuelve todas las cuotas de una factura específica para la vista detalle.
     */
    public static function getCuotasFactura(string $nit, string $tipoDoc, int $numDoc): array
    {
        $sql = <<<SQL
SELECT
    SA.F353_NRO_CUOTA_CRUCE         AS NUMERO_CUOTA,
    SA.f353_fecha                   AS FECHA,
    SA.f353_fecha_vcto              AS FECHA_VCTO,
    SA.f353_total_db                AS VALOR,
    SA.f353_id_cond_pago            AS CONDICION_PAGO,
    T.f200_nit                      AS ID_TERCERO,
    T.f200_razon_social             AS RAZON_SOCIAL,
    SA.f353_id_tipo_docto_cruce     AS TIPO_DOCUMENTO_CRUCE,
    SA.F353_CONSEC_DOCTO_CRUCE      AS NUMERO_DOCUMENTO_CRUCE,
    SA.f353_id_sucursal             AS SUCURSAL_CLIENTE,
    CASE WHEN SA.f353_total_cr = 0 THEN 'Pendiente' ELSE 'Pagado' END AS ESTADO
FROM t353_co_saldo_abierto AS SA
INNER JOIN t200_mm_terceros AS T
    ON SA.f353_rowid_tercero = T.f200_rowid
WHERE LTRIM(RTRIM(T.f200_nit)) = LTRIM(RTRIM(:nit))
    AND SA.f353_id_tipo_docto_cruce = :tipo
    AND SA.F353_CONSEC_DOCTO_CRUCE  = :num
    AND SA.f353_rowid_auxiliar = 20805
    AND SA.f353_ind_anticipo   = 0
ORDER BY SA.F353_NRO_CUOTA_CRUCE ASC
SQL;

        return Yii::$app->dbSiesa->createCommand($sql, [
            ':nit'  => $nit,
            ':tipo' => $tipoDoc,
            ':num'  => $numDoc,
        ])->queryAll();
    }

    public function getTotalValor()
    {
        $params = [
            ':fini' => $this->fecha_inicio,
            ':ffin' => $this->fecha_fin,
        ];

        $filtersSql = $this->buildFiltersSql($params);

        $sql = <<<SQL
SELECT SUM(SA.F353_TOTAL_DB)
FROM t353_co_saldo_abierto AS SA
INNER JOIN t200_mm_terceros AS T
    ON SA.f353_rowid_tercero = T.f200_rowid
WHERE
    SA.F353_FECHA >= :fini
    AND SA.F353_FECHA < DATEADD(day, 1, :ffin)
    AND SA.f353_rowid_auxiliar = 20805
    AND SA.f353_ind_anticipo = 0
    AND SA.f353_id_tipo_docto_cruce <> 'NC'
{$filtersSql}
SQL;

        return Yii::$app->dbSiesa->createCommand($sql, $params)->queryScalar() ?: 0;
    }

    /**
     * Retorna cupo asignado y saldo pendiente total del empleado (sin filtro de fecha).
     * Solo aplica cuando se filtra por un ID_TERCERO específico.
     *
     * @return array|null  ['cupo_credito' => float, 'saldo_pendiente' => float] o null si no hay tercero
     */
    public function getCupoInfo()
    {
        if (!$this->ID_TERCERO) {
            return null;
        }

        // Cupo asignado en t201_mm_clientes
        // Join igual que el CRM: f201_id_cia = f200_id_cia (no hardcodeado a 1)
        // LTRIM/RTRIM para tolerar espacios en el NIT almacenado
        $sqlCupo = <<<SQL
SELECT MAX(C.f201_cupo_credito)
FROM t201_mm_clientes AS C
INNER JOIN t200_mm_terceros AS T
    ON C.f201_rowid_tercero = T.f200_rowid
WHERE LTRIM(RTRIM(T.f200_nit)) = LTRIM(RTRIM(:nit))
    AND C.f201_id_cia = 7
SQL;

        $cupo = (float) (Yii::$app->dbSiesa->createCommand($sqlCupo, [':nit' => $this->ID_TERCERO])->queryScalar() ?: 0);

        // Saldo pendiente: suma de f353_total_db donde f353_total_cr = 0 (cuotas no cruzadas)
        $sqlPendiente = <<<SQL
SELECT SUM(SA.f353_total_db)
FROM t353_co_saldo_abierto AS SA
INNER JOIN t200_mm_terceros AS T
    ON SA.f353_rowid_tercero = T.f200_rowid
WHERE LTRIM(RTRIM(T.f200_nit)) = LTRIM(RTRIM(:nit))
    AND SA.f353_rowid_auxiliar = 20805
    AND SA.f353_ind_anticipo = 0
    AND SA.f353_total_cr = 0
    AND SA.f353_id_tipo_docto_cruce <> 'NC'
SQL;

        $pendiente = (float) (Yii::$app->dbSiesa->createCommand($sqlPendiente, [':nit' => $this->ID_TERCERO])->queryScalar() ?: 0);

        return [
            'cupo_credito'    => $cupo,
            'saldo_pendiente' => $pendiente,
            'cupo_disponible' => $cupo - $pendiente,
        ];
    }
}
