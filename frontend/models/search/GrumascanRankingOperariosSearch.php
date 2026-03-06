<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;

class GrumascanRankingOperariosSearch extends Model
{
    public $desde;   // YYYY-MM-DD
    public $hasta;   // YYYY-MM-DD
    public $bodega;  // '075' / '090' / 75 / 90

    public function rules()
    {
        return [
            [['desde', 'hasta', 'bodega'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'desde' => 'Desde',
            'hasta' => 'Hasta',
            'bodega' => 'Bodega',
        ];
    }

    public function search($params)
    {
        $this->load($params);

        // Defaults
        $hoy = (new \DateTime('now', new \DateTimeZone('America/Bogota')))->format('Y-m-d');
        if (empty($this->hasta)) $this->hasta = $hoy;
        if (empty($this->desde)) {
            $this->desde = (new \DateTime($this->hasta, new \DateTimeZone('America/Bogota')))
                ->modify('-7 days')->format('Y-m-d');
        }

        $desdeDT = $this->desde . ' 00:00:00';
        $hastaDT = $this->hasta . ' 23:59:59';

        // Filtro bodega (tabla real: bodegas)
        $whereBodega = '';
        $paramsSql = [
            ':desde' => $desdeDT,
            ':hasta' => $hastaDT,
        ];

        if ($this->bodega !== null && $this->bodega !== '') {
            $bodegaRaw = trim((string)$this->bodega);             // '075'
            $bodegaId  = ctype_digit($bodegaRaw) ? (int)$bodegaRaw : null; // 75

            // OJO: aquí asumimos que bodegas tiene columna "codigo"
            // Si tu columna se llama distinto, cambiamos b.codigo y listo.
            if ($bodegaId !== null) {
                $whereBodega = " AND (gm.idbodega = :bodegaId OR b.codigo = :bodegaCod) ";
                $paramsSql[':bodegaId'] = $bodegaId;
                $paramsSql[':bodegaCod'] = $bodegaRaw;
            } else {
                $whereBodega = " AND b.codigo = :bodegaCod ";
                $paramsSql[':bodegaCod'] = $bodegaRaw;
            }
        }

        /**
         * FIX DATOS VACÍOS:
         * - created_at es NVARCHAR y NO siempre está en formato 120.
         * - Si la conversión falla queda NULL y el filtro por fecha lo mata.
         *
         * Solución:
         * - Calculamos conteo_created_dt con varios TRY_CONVERT (multi-formato)
         * - Y filtramos así: (dt IS NULL OR dt BETWEEN :desde AND :hasta)
         *   => nunca te quedas sin datos por culpa del formato sucio.
         */

        $sql = "
            ;WITH base AS (
                SELECT
                    u.username AS username,
                    gsc.id AS conteo_id,

                    -- Parse robusto para datetime desde NVARCHAR (varios formatos)
                    COALESCE(
                        TRY_CONVERT(datetime2(0), gsc.created_at, 120),
                        TRY_CONVERT(datetime2(0), gsc.created_at, 121),
                        TRY_CONVERT(datetime2(0), gsc.created_at, 126),
                        TRY_CONVERT(datetime2(0), gsc.created_at),
                        TRY_CONVERT(datetime2(0), REPLACE(gsc.created_at, ',', ''), 120)
                    ) AS conteo_created_dt,

                    COALESCE(
                        TRY_CONVERT(datetime2(0), gscd.created_at, 120),
                        TRY_CONVERT(datetime2(0), gscd.created_at, 121),
                        TRY_CONVERT(datetime2(0), gscd.created_at, 126),
                        TRY_CONVERT(datetime2(0), gscd.created_at),
                        TRY_CONVERT(datetime2(0), REPLACE(gscd.created_at, ',', ''), 120)
                    ) AS det_created_dt,

                    COALESCE(TRY_CONVERT(DECIMAL(18,2), gscd.cantidad), 0) AS cant,
                    TRY_CONVERT(DECIMAL(18,2), ue.equivalencia) AS eq

                FROM grumascanconteo gsc
                JOIN grumascanmarcacion gm
                    ON gm.id = gsc.idmarcacion
                JOIN bodegas b
                    ON b.id = gm.idbodega
                JOIN grumascanconteodetalle gscd
                    ON gscd.idgrumascanconteo = gsc.id
                JOIN item it
                    ON it.id = gscd.idItem
                LEFT JOIN unidadEmpaque ue
                    ON ue.codigo = it.unidadEmpaque
                LEFT JOIN [user] u
                    ON u.id = gsc.created_by

                WHERE gsc.idestado = 1
                  AND (
                        COALESCE(
                            TRY_CONVERT(datetime2(0), gsc.created_at, 120),
                            TRY_CONVERT(datetime2(0), gsc.created_at, 121),
                            TRY_CONVERT(datetime2(0), gsc.created_at, 126),
                            TRY_CONVERT(datetime2(0), gsc.created_at),
                            TRY_CONVERT(datetime2(0), REPLACE(gsc.created_at, ',', ''), 120)
                        ) IS NULL
                        OR
                        COALESCE(
                            TRY_CONVERT(datetime2(0), gsc.created_at, 120),
                            TRY_CONVERT(datetime2(0), gsc.created_at, 121),
                            TRY_CONVERT(datetime2(0), gsc.created_at, 126),
                            TRY_CONVERT(datetime2(0), gsc.created_at),
                            TRY_CONVERT(datetime2(0), REPLACE(gsc.created_at, ',', ''), 120)
                        ) BETWEEN :desde AND :hasta
                  )
                  $whereBodega
            )
            SELECT
                username,
                CAST(SUM(CASE WHEN eq IS NOT NULL THEN cant * eq ELSE cant END) AS DECIMAL(18,2)) AS unidades_reales,
                CAST(SUM(cant) AS DECIMAL(18,2)) AS paquetes,
                COUNT(DISTINCT conteo_id) AS conteos,
                ISNULL(DATEDIFF(SECOND, MIN(det_created_dt), MAX(det_created_dt)), 0) AS tiempo_captura_seg,
                CAST(
                    CASE
                        WHEN NULLIF(DATEDIFF(SECOND, MIN(det_created_dt), MAX(det_created_dt)), 0) IS NULL THEN 0
                        ELSE
                            SUM(CASE WHEN eq IS NOT NULL THEN cant * eq ELSE cant END)
                            / (NULLIF(DATEDIFF(SECOND, MIN(det_created_dt), MAX(det_created_dt)), 0) / 3600.0)
                    END
                AS DECIMAL(18,2)) AS velocidad_u_h
            FROM base
            GROUP BY username
        ";

        $countSql = "
            ;WITH base AS (
                SELECT u.username
                FROM grumascanconteo gsc
                JOIN grumascanmarcacion gm ON gm.id = gsc.idmarcacion
                JOIN bodegas b ON b.id = gm.idbodega
                JOIN grumascanconteodetalle gscd ON gscd.idgrumascanconteo = gsc.id
                JOIN item it ON it.id = gscd.idItem
                LEFT JOIN [user] u ON u.id = gsc.created_by
                WHERE gsc.idestado = 1
                  AND (
                        COALESCE(
                            TRY_CONVERT(datetime2(0), gsc.created_at, 120),
                            TRY_CONVERT(datetime2(0), gsc.created_at, 121),
                            TRY_CONVERT(datetime2(0), gsc.created_at, 126),
                            TRY_CONVERT(datetime2(0), gsc.created_at),
                            TRY_CONVERT(datetime2(0), REPLACE(gsc.created_at, ',', ''), 120)
                        ) IS NULL
                        OR
                        COALESCE(
                            TRY_CONVERT(datetime2(0), gsc.created_at, 120),
                            TRY_CONVERT(datetime2(0), gsc.created_at, 121),
                            TRY_CONVERT(datetime2(0), gsc.created_at, 126),
                            TRY_CONVERT(datetime2(0), gsc.created_at),
                            TRY_CONVERT(datetime2(0), REPLACE(gsc.created_at, ',', ''), 120)
                        ) BETWEEN :desde AND :hasta
                  )
                  $whereBodega
                GROUP BY u.username
            )
            SELECT COUNT(1) FROM base;
        ";

        $totalCount = (int) Yii::$app->db->createCommand($countSql, $paramsSql)->queryScalar();

        return new SqlDataProvider([
            'sql' => $sql,
            'params' => $paramsSql,
            'totalCount' => $totalCount,
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'attributes' => [
                    'username',
                    'unidades_reales',
                    'paquetes',
                    'conteos',
                    'tiempo_captura_seg',
                    'velocidad_u_h',
                ],
                'defaultOrder' => ['unidades_reales' => SORT_DESC],
            ],
        ]);
    }
}
