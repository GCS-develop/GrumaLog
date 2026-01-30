<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Dashboard de Auditoría:
 * - Atribuye errores al USUARIO CREADOR del TRASPASO (padre)
 *   usando delta por ítem: (auditado - detalle).
 * - Mide productividad de AUDITORES (desde traspasodetalleauditado).
 */
class TraspasoAuditDashboardSearch extends Model
{
    public $fecha;              // YYYY-MM-DD
    public $desde;              // YYYY-MM-DD
    public $hasta;              // YYYY-MM-DD

    public $user_id;            // Filtro: creador del traspaso (t.created_by)
    public $bodega_origen_id;   // t.idBodegaOrigen
    public $bodega_destino_id;  // t.idBodegaDestino
    public $tipo_documento;     // t.idTipoDocumento

    // Para panel fuente (detalle|auditado|ambos) — se mantiene
    public $fuente = 'ambos';

    public function rules()
    {
        return [
            [['fecha', 'desde', 'hasta', 'fuente'], 'safe'],
            [['user_id', 'bodega_origen_id', 'bodega_destino_id', 'tipo_documento'], 'integer'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    // ========= Helpers de rango de fechas =========

    private function getRange(): array
    {
        $tz = new \DateTimeZone('America/Bogota');
        if (!$this->desde && !$this->hasta) {
            $f = $this->fecha ?: (new \DateTime('now', $tz))->format('Y-m-d');
            $inicio = (new \DateTime($f . ' 00:00:00', $tz))->format('Y-m-d H:i:s');
            $fin    = (new \DateTime($f . ' 00:00:00', $tz))->modify('+1 day')->format('Y-m-d H:i:s');
        } else {
            $d = $this->desde ?: (new \DateTime('now', $tz))->format('Y-m-d');
            $h = $this->hasta ?: $d;
            $inicio = (new \DateTime($d . ' 00:00:00', $tz))->format('Y-m-d H:i:s');
            $fin    = (new \DateTime($h . ' 00:00:00', $tz))->modify('+1 day')->format('Y-m-d H:i:s');
        }
        return [$inicio, $fin];
    }

    private function baseWhereSql(): array
    {
        [$inicio, $fin] = $this->getRange();

        $where = [];
        $params = [
            ':inicio' => $inicio,
            ':fin'    => $fin,
        ];

        // 1) Rango por fecha del TRASPASO (padre)
        $where[] = 'TRY_CAST(t.[created_at] AS datetime2) BETWEEN :inicio AND :fin';

        // 2) **Solo traspasos que tienen auditado**
        $where[] = 'EXISTS (SELECT 1 FROM [traspasodetalleauditado] tdaX WHERE tdaX.[idTraspaso] = t.[id])';

        // Filtros opcionales
        if ($this->user_id) {
            $where[] = 't.[created_by] = :uid';
            $params[':uid'] = (int)$this->user_id;
        }
        if ($this->bodega_origen_id) {
            $where[] = 't.[idBodegaOrigen] = :bo';
            $params[':bo'] = (int)$this->bodega_origen_id;
        }
        if ($this->bodega_destino_id) {
            $where[] = 't.[idBodegaDestino] = :bd';
            $params[':bd'] = (int)$this->bodega_destino_id;
        }
        if ($this->tipo_documento) {
            $where[] = 't.[idTipoDocumento] = :td';
            $params[':td'] = (int)$this->tipo_documento;
        }

        $sql = 'WHERE ' . implode(' AND ', $where);
        return [$sql, $params];
    }


    // ========= Panel "fuente" (comparativo general por usuario) =========
    // Conservamos tu concepto original (detalle|auditado|ambos) para productividad.

    public function searchPerUser(): ArrayDataProvider
    {
        // Para “fuente” usamos la persona que opera LAS FILAS de cada hijo.
        // detalle -> td.created_by ; auditado -> tda.created_by ; ambos -> merge por username.

        [$sqlWhere, $params] = $this->baseWhereSql();
        $db = Yii::$app->db;

        // fuente: detalle
        $sqlDet = "
            SELECT 
                td.[created_by]      AS user_id,
                uc.[username]        AS username,
                SUM(td.[cantidad])   AS total_unidades,
                DATEDIFF(SECOND, MIN(TRY_CAST(td.[created_at] AS datetime2)), MAX(TRY_CAST(td.[created_at] AS datetime2))) AS dur_seconds
            FROM [traspasodetalle] td
            INNER JOIN [traspaso] t ON td.[idTraspaso] = t.[id]
            LEFT JOIN [user] uc ON td.[created_by] = uc.[id]
            $sqlWhere
            GROUP BY td.[created_by], uc.[username]
        ";

        // fuente: auditado
        $sqlAud = "
            SELECT 
                tda.[created_by]     AS user_id,
                ua.[username]        AS username,
                SUM(tda.[cantidad])  AS total_unidades,
                DATEDIFF(SECOND, MIN(TRY_CAST(tda.[created_at] AS datetime2)), MAX(TRY_CAST(tda.[created_at] AS datetime2))) AS dur_seconds
            FROM [traspasodetalleauditado] tda
            INNER JOIN [traspaso] t ON tda.[idTraspaso] = t.[id]
            LEFT JOIN [user] ua ON tda.[created_by] = ua.[id]
            $sqlWhere
            GROUP BY tda.[created_by], ua.[username]
        ";

        $rows = [];
        if ($this->fuente === 'detalle') {
            $rows = $db->createCommand($sqlDet, $params)->queryAll();
        } elseif ($this->fuente === 'auditado') {
            $rows = $db->createCommand($sqlAud, $params)->queryAll();
        } else {
            $det = $db->createCommand($sqlDet, $params)->queryAll();
            $aud = $db->createCommand($sqlAud, $params)->queryAll();

            // Merge por user_id (suma unidades y duraciones; username de det/aud preferentemente aud si existe)
            $byId = [];
            foreach ($det as $r) {
                $id = (int)$r['user_id'];
                $byId[$id] = [
                    'user_id'        => $id,
                    'username'       => $r['username'],
                    'total_unidades' => (int)$r['total_unidades'],
                    'dur_seconds'    => (int)$r['dur_seconds'],
                ];
            }
            foreach ($aud as $r) {
                $id = (int)$r['user_id'];
                if (!isset($byId[$id])) {
                    $byId[$id] = [
                        'user_id'        => $id,
                        'username'       => $r['username'],
                        'total_unidades' => 0,
                        'dur_seconds'    => 0,
                    ];
                }
                $byId[$id]['username']       = $byId[$id]['username'] ?: $r['username'];
                $byId[$id]['total_unidades'] += (int)$r['total_unidades'];
                $byId[$id]['dur_seconds']    += (int)$r['dur_seconds'];
            }
            $rows = array_values($byId);
        }

        // UPH
        foreach ($rows as &$r) {
            $dur = (int)($r['dur_seconds'] ?? 0);
            $r['velocidad_uph'] = $dur > 0 ? round(((int)$r['total_unidades']) * 3600 / $dur, 2) : 0;
        }
        unset($r);

        return new ArrayDataProvider([
            'allModels'  => $rows,
            'pagination' => ['pageSize' => 100],
            'sort' => [
                'attributes' => ['username', 'total_unidades', 'dur_seconds', 'velocidad_uph'],
                'defaultOrder' => ['total_unidades' => SORT_DESC],
            ],
        ]);
    }

    // ========= Comparativo por CREADOR (atribuir errores al padre) =========

    public function searchComparativoCreadores(): ArrayDataProvider
    {
        [$sqlWhere, $params] = $this->baseWhereSql();
        $db = Yii::$app->db;

        // Agregado de detalle (productividad del creador: unidades y duración usando TD del traspaso)
        $sqlProd = "
            SELECT 
                t.[created_by]                 AS creator_id,
                uc.[username]                  AS creator,
                SUM(td.[cantidad])             AS unidades_detalle,
                DATEDIFF(SECOND, MIN(TRY_CAST(td.[created_at] AS datetime2)), MAX(TRY_CAST(td.[created_at] AS datetime2))) AS dur_detalle
            FROM [traspaso] t
            LEFT JOIN [traspasodetalle] td ON td.[idTraspaso] = t.[id]
            LEFT JOIN [user] uc ON t.[created_by] = uc.[id]
            $sqlWhere
            GROUP BY t.[created_by], uc.[username]
        ";

        // Delta por ítem (auditado - detalle) agrupado por traspaso y luego por creador
        // Usamos FULL OUTER JOIN para contemplar ítems que aparecen solo en un lado
        $sqlDelta = "
            WITH det AS (
                SELECT idTraspaso, idItem, SUM(cantidad) AS cant_det
                FROM [traspasodetalle]
                GROUP BY idTraspaso, idItem
            ),
            aud AS (
                SELECT idTraspaso, idItem, SUM(cantidad) AS cant_aud
                FROM [traspasodetalleauditado]
                GROUP BY idTraspaso, idItem
            ),
            delta AS (
                SELECT 
                    COALESCE(aud.idTraspaso, det.idTraspaso)  AS idTraspaso,
                    COALESCE(aud.idItem, det.idItem)          AS idItem,
                    COALESCE(aud.cant_aud,0) - COALESCE(det.cant_det,0) AS d
                FROM aud
                FULL OUTER JOIN det
                    ON aud.idTraspaso = det.idTraspaso AND aud.idItem = det.idItem
            )
            SELECT 
                t.[created_by] AS creator_id,
                SUM(CASE WHEN d > 0 THEN d ELSE 0 END)                      AS sobrantes,
                SUM(CASE WHEN d < 0 THEN ABS(d) ELSE 0 END)                 AS faltantes,
                SUM(ABS(d))                                                 AS total_abs,
                SUM(d)                                                      AS neto
            FROM delta
            INNER JOIN [traspaso] t ON t.[id] = delta.[idTraspaso]
            $sqlWhere
            GROUP BY t.[created_by]
        ";

        $prod   = $db->createCommand($sqlProd, $params)->queryAll();
        $deltas = $db->createCommand($sqlDelta, $params)->queryAll();
        $byCreator = ArrayHelper::index($deltas, 'creator_id');

        $rows = [];
        foreach ($prod as $p) {
            $cid = (int)$p['creator_id'];
            $sob = (int)($byCreator[$cid]['sobrantes'] ?? 0);
            $fal = (int)($byCreator[$cid]['faltantes'] ?? 0);
            $abs = (int)($byCreator[$cid]['total_abs'] ?? 0);
            $net = (int)($byCreator[$cid]['neto'] ?? 0);

            $dur = (int)($p['dur_detalle'] ?? 0);
            $uni = (int)($p['unidades_detalle'] ?? 0);
            $uph = $dur > 0 ? round($uni * 3600 / $dur, 2) : 0;

            $rows[] = [
                'creator_id'        => $cid,
                'creator'           => $p['creator'],
                'unidades_detalle'  => $uni,
                'dur_detalle'       => $dur,
                'uph_detalle'       => $uph,
                'sobrantes'         => $sob,
                'faltantes'         => $fal,
                'total_abs'         => $abs,
                'neto'              => $net,
            ];
        }

        return new ArrayDataProvider([
            'allModels'  => $rows,
            'pagination' => ['pageSize' => 100],
            'sort' => [
                'attributes' => [
                    'creator',
                    'unidades_detalle',
                    'dur_detalle',
                    'uph_detalle',
                    'sobrantes',
                    'faltantes',
                    'total_abs',
                    'neto'
                ],
                'defaultOrder' => ['total_abs' => SORT_DESC],
            ],
        ]);
    }

    // ========= Productividad de AUDITORES =========

    public function searchAuditoresProductividad(): ArrayDataProvider
    {
        [$sqlWhere, $params] = $this->baseWhereSql();
        $db = Yii::$app->db;

        $sql = "
            SELECT 
                tda.[created_by]                 AS auditor_id,
                ua.[username]                    AS auditor,
                SUM(tda.[cantidad])              AS unidades_auditadas,
                DATEDIFF(SECOND, MIN(TRY_CAST(tda.[created_at] AS datetime2)), MAX(TRY_CAST(tda.[created_at] AS datetime2))) AS dur_auditado
            FROM [traspasodetalleauditado] tda
            INNER JOIN [traspaso] t ON t.[id] = tda.[idTraspaso]
            LEFT JOIN [user] ua ON ua.[id] = tda.[created_by]
            $sqlWhere
            GROUP BY tda.[created_by], ua.[username]
        ";

        $rows = $db->createCommand($sql, $params)->queryAll();

        foreach ($rows as &$r) {
            $dur = (int)($r['dur_auditado'] ?? 0);
            $r['uph_auditado'] = $dur > 0 ? round(((int)$r['unidades_auditadas']) * 3600 / $dur, 2) : 0;
        }
        unset($r);

        return new ArrayDataProvider([
            'allModels'  => $rows,
            'pagination' => ['pageSize' => 100],
            'sort' => [
                'attributes' => ['auditor', 'unidades_auditadas', 'dur_auditado', 'uph_auditado'],
                'defaultOrder' => ['unidades_auditadas' => SORT_DESC],
            ],
        ]);
    }

    // ========= Novedades por traspaso =========

    public function searchNovedadesPorTraspaso(): ArrayDataProvider
    {
        [$sqlWhere, $params] = $this->baseWhereSql();
        $db = Yii::$app->db;

        $sql = "
            WITH det AS (
                SELECT idTraspaso, idItem, SUM(cantidad) AS cant_det
                FROM [traspasodetalle]
                GROUP BY idTraspaso, idItem
            ),
            aud AS (
                SELECT idTraspaso, idItem, SUM(cantidad) AS cant_aud
                FROM [traspasodetalleauditado]
                GROUP BY idTraspaso, idItem
            ),
            delta AS (
                SELECT 
                    COALESCE(aud.idTraspaso, det.idTraspaso)  AS idTraspaso,
                    COALESCE(aud.idItem, det.idItem)          AS idItem,
                    COALESCE(aud.cant_aud,0) - COALESCE(det.cant_det,0) AS d
                FROM aud
                FULL OUTER JOIN det
                    ON aud.idTraspaso = det.idTraspaso AND aud.idItem = det.idItem
            )
            SELECT 
                t.[id] AS idTraspaso,
                COUNT(DISTINCT tda.[created_by]) AS usuarios_involucrados,
                SUM(CASE WHEN d > 0 THEN d ELSE 0 END)      AS sobrantes,
                SUM(CASE WHEN d < 0 THEN ABS(d) ELSE 0 END) AS faltantes,
                SUM(ABS(d))                                 AS total_abs,
                SUM(d)                                      AS neto
            FROM [traspaso] t
            LEFT JOIN [traspasodetalleauditado] tda ON tda.[idTraspaso] = t.[id]
            INNER JOIN delta ON delta.[idTraspaso] = t.[id]
            $sqlWhere
            GROUP BY t.[id]
            ORDER BY total_abs DESC
        ";

        $rows = $db->createCommand($sql, $params)->queryAll();

        return new ArrayDataProvider([
            'allModels'  => $rows,
            'pagination' => ['pageSize' => 100],
            'sort' => [
                'attributes' => ['idTraspaso', 'usuarios_involucrados', 'sobrantes', 'faltantes', 'total_abs', 'neto'],
                'defaultOrder' => ['total_abs' => SORT_DESC],
            ],
        ]);
    }

    // ========= Novedades por usuario (creador) =========

    public function searchNovedadesPerUser(): ArrayDataProvider
    {
        [$sqlWhere, $params] = $this->baseWhereSql();
        $db = Yii::$app->db;

        $sql = "
            WITH det AS (
                SELECT idTraspaso, idItem, SUM(cantidad) AS cant_det
                FROM [traspasodetalle]
                GROUP BY idTraspaso, idItem
            ),
            aud AS (
                SELECT idTraspaso, idItem, SUM(cantidad) AS cant_aud
                FROM [traspasodetalleauditado]
                GROUP BY idTraspaso, idItem
            ),
            delta AS (
                SELECT 
                    COALESCE(aud.idTraspaso, det.idTraspaso)  AS idTraspaso,
                    COALESCE(aud.idItem, det.idItem)          AS idItem,
                    COALESCE(aud.cant_aud,0) - COALESCE(det.cant_det,0) AS d
                FROM aud
                FULL OUTER JOIN det
                    ON aud.idTraspaso = det.idTraspaso AND aud.idItem = det.idItem
            )
            SELECT 
                t.[created_by]           AS created_by,
                uc.[username]            AS username,
                SUM(CASE WHEN d > 0 THEN d ELSE 0 END)      AS sobrantes,
                SUM(CASE WHEN d < 0 THEN ABS(d) ELSE 0 END) AS faltantes,
                SUM(ABS(d))                                 AS total_abs,
                SUM(d)                                      AS neto,
                COUNT(DISTINCT t.[id])                       AS traspasos_con_novedad,
                COUNT(*)                                     AS items_con_novedad
            FROM [traspaso] t
            LEFT JOIN [user] uc ON uc.[id] = t.[created_by]
            INNER JOIN delta ON delta.[idTraspaso] = t.[id]
            $sqlWhere
            GROUP BY t.[created_by], uc.[username]
            ORDER BY total_abs DESC
        ";

        $rows = $db->createCommand($sql, $params)->queryAll();

        return new ArrayDataProvider([
            'allModels'  => $rows,
            'pagination' => ['pageSize' => 100],
            'sort' => [
                'attributes' => ['username', 'sobrantes', 'faltantes', 'total_abs', 'neto', 'traspasos_con_novedad', 'items_con_novedad'],
                'defaultOrder' => ['total_abs' => SORT_DESC],
            ],
        ]);
    }

    // ========= Detalle de novedades (usuario creador – traspaso – ítem) =========

    public function searchNovedadesDetalle(): ArrayDataProvider
    {
        [$sqlWhere, $params] = $this->baseWhereSql();
        $db = Yii::$app->db;

        $sql = "
            WITH det AS (
                SELECT idTraspaso, idItem, SUM(cantidad) AS cant_det
                FROM [traspasodetalle]
                GROUP BY idTraspaso, idItem
            ),
            aud AS (
                SELECT idTraspaso, idItem, SUM(cantidad) AS cant_aud
                FROM [traspasodetalleauditado]
                GROUP BY idTraspaso, idItem
            ),
            delta AS (
                SELECT 
                    COALESCE(aud.idTraspaso, det.idTraspaso)  AS idTraspaso,
                    COALESCE(aud.idItem, det.idItem)          AS idItem,
                    COALESCE(aud.cant_aud,0) AS cant_aud,
                    COALESCE(det.cant_det,0) AS cant_det,
                    COALESCE(aud.cant_aud,0) - COALESCE(det.cant_det,0) AS d
                FROM aud
                FULL OUTER JOIN det
                    ON aud.idTraspaso = det.idTraspaso AND aud.idItem = det.idItem
            )
            SELECT 
                t.[created_by],
                uc.[username],
                delta.[idTraspaso],
                delta.[idItem],
                delta.[cant_det]    AS cant_detalle,
                delta.[cant_aud]    AS cant_auditado,
                delta.[d]           AS delta
            FROM delta
            INNER JOIN [traspaso] t ON t.[id] = delta.[idTraspaso]
            LEFT JOIN [user] uc ON uc.[id] = t.[created_by]
            $sqlWhere
            ORDER BY ABS(delta.[d]) DESC
        ";

        $rows = $db->createCommand($sql, $params)->queryAll();

        return new ArrayDataProvider([
            'allModels'  => $rows,
            'pagination' => ['pageSize' => 100],
        ]);
    }
}
