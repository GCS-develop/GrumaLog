<?php

namespace frontend\modules\hunter\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use common\components\SiesaSyncService;

use app\models\HunterConteo;
use app\models\HunterConteoScan;
use app\models\HunterSeccion;
use app\models\HunterUbicacion;
use frontend\models\Bodegas;
use common\models\User; // <-- importante para el login
use yii\filters\Cors;

class HunterApiController extends Controller
{
    public $enableCsrfValidation = false; // llamadas desde APK / Postman


    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::class,
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => false,
                    'Access-Control-Max-Age' => 86400,
                ],
            ],

            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'sync'      => ['POST', 'OPTIONS'],
                    'catalogos' => ['GET', 'OPTIONS'],
                    'login'     => ['POST', 'OPTIONS'],
                    'ping'      => ['GET', 'POST', 'OPTIONS'],
                    'bodegas'          => ['GET', 'POST', 'OPTIONS'],
                    'catalogo-bodega'  => ['GET', 'POST', 'OPTIONS'],
                    'inventario-bodega' => ['GET', 'POST', 'OPTIONS'],
                    'colores' => ['GET', 'OPTIONS'],
                    'tallas' => ['GET', 'OPTIONS'],
                    'sync-bodega' => ['POST', 'OPTIONS'],
                ],
            ],

            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'text/json'        => Response::FORMAT_JSON,
                    '*/json'           => Response::FORMAT_JSON,
                ],
            ],

        ];
    }


    // --- prueba rápida de que responde JSON ---
    public function actionPing()
    {
        return [
            'ok' => true,
            'msg' => 'pong',
            'time' => date('Y-m-d H:i:s'),
        ];
    }
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * GET /index.php?r=hunter/hunter-api/catalogos
     */
    public function actionCatalogos()
    {
        $bodegas = Bodegas::find()
            ->select(['id', 'codigo', 'nombre'])
            ->orderBy(['codigo' => SORT_ASC])
            ->asArray()
            ->all();

        $secciones = HunterSeccion::find()
            ->select(['id', 'id_bodega', 'codigo', 'nombre'])
            ->where(['activo' => 1])
            ->orderBy(['id_bodega' => SORT_ASC, 'codigo' => SORT_ASC])
            ->asArray()
            ->all();

        $ubicaciones = HunterUbicacion::find()
            ->select(['id', 'id_bodega', 'id_seccion', 'codigo', 'descripcion'])
            ->where(['activo' => 1])
            ->orderBy(['id_bodega' => SORT_ASC, 'id_seccion' => SORT_ASC, 'codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return [
            'ok'          => true,
            'bodegas'     => $bodegas,
            'secciones'   => $secciones,
            'ubicaciones' => $ubicaciones,
        ];
    }

    /**
     * POST /index.php?r=hunter/hunter-api/sync
     */
    public function actionSync()
    {
        $bodyRaw = Yii::$app->request->getRawBody();
        if (empty($bodyRaw)) {
            throw new BadRequestHttpException('Cuerpo vacío');
        }

        $data = json_decode($bodyRaw, true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('JSON inválido');
        }

        $idBodega    = $data['id_bodega']    ?? null;
        $idSeccion   = $data['id_seccion']   ?? null;
        $idUbicacion = $data['id_ubicacion'] ?? null;
        $origen      = $data['origen']       ?? 'Urovo';
        $dispositivo = $data['dispositivo']  ?? null;
        $lecturas    = $data['lecturas']     ?? [];

        if (!$idBodega || !is_array($lecturas) || empty($lecturas)) {
            throw new BadRequestHttpException('Faltan datos obligatorios (id_bodega / lecturas).');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $conteo = new HunterConteo();
            $conteo->id_bodega    = (int)$idBodega;
            $conteo->id_seccion   = $idSeccion ? (int)$idSeccion : null;
            $conteo->id_ubicacion = $idUbicacion ? (int)$idUbicacion : null;
            $conteo->origen       = $origen;
            $conteo->estado       = 0; // pendiente cruce

            if (!$conteo->save()) {
                throw new \RuntimeException('No se pudo guardar hunter_conteo: ' . json_encode($conteo->errors));
            }

            $totalLecturas = 0;
            $eanSet = [];

            foreach ($lecturas as $row) {
                $ean      = trim($row['ean'] ?? '');
                $cantidad = (int)($row['cantidad'] ?? 1);
                if ($ean === '' || $cantidad <= 0) {
                    continue;
                }

                $scan = new HunterConteoScan();
                $scan->id_conteo   = $conteo->id;
                $scan->ean         = $ean;
                $scan->cantidad    = $cantidad;
                $scan->dispositivo = $dispositivo;

                if (!$scan->save()) {
                    throw new \RuntimeException('Error guardando scan: ' . json_encode($scan->errors));
                }

                $totalLecturas += $cantidad;
                $eanSet[$ean] = true;
            }

            $conteo->total_lecturas = $totalLecturas;
            $conteo->total_skus     = count($eanSet);

            if (!$conteo->save(false, ['total_lecturas', 'total_skus', 'updated_at', 'updated_by'])) {
                throw new \RuntimeException('Error actualizando totales del conteo.');
            }

            $transaction->commit();

            return [
                'ok'             => true,
                'conteo_id'      => $conteo->id,
                'codigo'         => $conteo->codigo,
                'total_lecturas' => $conteo->total_lecturas,
                'total_skus'     => $conteo->total_skus,
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error([
                'hunter-sync-error' => $e->getMessage(),
                'trace'             => $e->getTraceAsString(),
            ], __METHOD__);

            return [
                'ok'    => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * POST /index.php?r=hunter/hunter-api/login
     * Body (x-www-form-urlencoded):
     *   username, password, deviceCode
     */
    public function actionLogin()
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            throw new BadRequestHttpException('Solo se permite POST');
        }

        // ✅ Soporta x-www-form-urlencoded / multipart (post)
        // ✅ y también JSON raw (Ionic/HttpClient)
        $data = $request->post();
        if (empty($data)) {
            $raw = $request->getRawBody();
            $data = json_decode($raw, true) ?: [];
        }

        $username   = trim((string)($data['username'] ?? ''));
        $password   = (string)($data['password'] ?? '');
        $deviceCode = trim((string)($data['deviceCode'] ?? ''));

        if ($username === '' || $password === '') {
            return ['ok' => false, 'error' => 'Debes enviar usuario y contraseña.'];
        }

        /** @var User|null $user */
        $user = User::find()->where(['username' => $username])->one();

        if (!$user || !$user->validatePassword($password)) {
            return ['ok' => false, 'error' => 'Usuario o contraseña inválidos.'];
        }

        if ((int)$user->status !== 10) {
            return ['ok' => false, 'error' => 'Usuario inactivo o bloqueado.'];
        }

        // (Opcional) log deviceCode
        // Yii::info(['deviceCode' => $deviceCode, 'userId' => $user->id], 'hunter-login');

        return [
            'ok' => true,
            'user' => [
                'id' => (int)$user->id,
                'username' => (string)$user->username,
                'email' => $user->email,
                'permisos' => [
                    'conteo'          => (bool)$user->conteo,
                    'conteocdsc'      => (bool)$user->conteocdsc,
                    'traspaso'        => (bool)$user->traspaso,
                    'despacho'        => (bool)$user->despacho,
                    'recepcion'       => (bool)$user->recepcion,
                    'devolucion'      => (bool)$user->devolucion,
                    'auditoriamanual' => (bool)$user->auditoriamanual,
                    'auditoria'       => (bool)$user->auditoria,
                ],
                'idEmpleado'        => $user->idEmpleado        !== null ? (int)$user->idEmpleado : null,
                'idBodegaRecibir'   => $user->idBodegaRecibir   !== null ? (int)$user->idBodegaRecibir : null,
                'idBodegaDespachar' => $user->idBodegaDespachar !== null ? (int)$user->idBodegaDespachar : null,
            ],
        ];
    }

    /**
     * GET /index.php?r=hunter/hunter-api/bodegas
     */
    public function actionBodegas()
    {
        $bodegas = Bodegas::find()
            ->select(['id', 'codigo', 'nombre'])
            ->orderBy(['codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return [
            'ok' => true,
            'bodegas' => $bodegas,
            'server_time' => time(),
        ];
    }

    /**
     * GET /index.php?r=hunter/hunter-api/catalogo-bodega&id_bodega=61&since=1700000000
     *
     * Devuelve el catálogo (EAN -> item/talla/color/descr) filtrado por bodega.
     * El "since" es opcional (unix timestamp) para sync incremental.
     */

    public function actionCatalogoBodega($id_bodega = null, $since = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $codigoBodega = (int)$id_bodega;
        if ($codigoBodega <= 0) {
            throw new BadRequestHttpException('id_bodega es obligatorio.');
        }

        $sinceTs = $since !== null ? (int)$since : null;

        $sql = "
        SELECT
            i.codigoBarras AS ean,
            it.item        AS item,
            it.id          AS idItem,
            it.descripcion AS descripcion,
            it.idTalla     AS idTalla,
            it.idColor     AS idColor,
            i.existencia   AS existencia,
            it.unidadEmpaque AS unidadEmpaque,
            i.updated_at   AS updated_at
        FROM inventario i
        INNER JOIN item it ON it.id = i.idItem
        WHERE i.codigoBodega = :codigoBodega
    ";

        $params = [':codigoBodega' => $codigoBodega];

        // incremental (si tienes updated_at)
        if ($sinceTs) {
            $sql .= " AND i.updated_at >= :since ";
            $params[':since'] = $sinceTs;
        }

        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        // Normaliza / limpia
        $items = array_map(function ($r) {
            $ue = trim((string)($r['unidadEmpaque'] ?? '')); // <-- CLAVE

            return [
                'ean'          => (string)($r['ean'] ?? ''),
                'item'         => isset($r['item']) ? (int)$r['item'] : null,
                'idItem'       => isset($r['idItem']) ? (int)$r['idItem'] : null,
                'descripcion'  => (string)($r['descripcion'] ?? ''),
                'idTalla'      => isset($r['idTalla']) ? (int)$r['idTalla'] : null,
                'idColor'      => isset($r['idColor']) ? (int)$r['idColor'] : null,
                'unidadEmpaque' => $ue !== '' ? $ue : null,
                'factorEmpaque' => (preg_match('/^px(\d+)$/i', $ue, $m) ? (int)$m[1] : 1),
                'existencia'   => isset($r['existencia']) ? (int)$r['existencia'] : null,
                'updated_at'   => isset($r['updated_at']) ? (int)$r['updated_at'] : null,
            ];
        }, $rows);


        return [
            'ok' => true,
            'id_bodega' => $codigoBodega,   // mantiene tu contrato
            'count' => count($items),
            'items' => $items,
            'server_time' => time(),
        ];
    }

    /**
     * GET /index.php?r=hunter/hunter-api/inventario-bodega&id_bodega=61&since=1700000000
     *
     * Devuelve existencias por EAN de una bodega.
     * "since" opcional si tienes updated_at.
     */
    public function actionInventarioBodega($id_bodega = null, $since = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $codigoBodega = (int)$id_bodega;
        if ($codigoBodega <= 0) {
            throw new BadRequestHttpException('id_bodega es obligatorio.');
        }

        $sinceTs = $since !== null ? (int)$since : null;

        $sql = "
        SELECT
            i.codigoBarras AS ean,
            i.existencia   AS existencia,
            i.updated_at   AS updated_at
        FROM inventario i
        WHERE i.codigoBodega = :codigoBodega
    ";

        $params = [':codigoBodega' => $codigoBodega];

        if ($sinceTs) {
            $sql .= " AND i.updated_at >= :since ";
            $params[':since'] = $sinceTs;
        }

        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        $inventario = array_map(function ($r) {
            return [
                'ean' => (string)($r['ean'] ?? ''),
                'existencia' => (int)($r['existencia'] ?? 0),
                'updated_at' => isset($r['updated_at']) ? (int)$r['updated_at'] : null,
            ];
        }, $rows);

        return [
            'ok' => true,
            'id_bodega' => $codigoBodega,
            'count' => count($inventario),
            'inventario' => $inventario,
            'server_time' => time(),
        ];
    }

    public function actionTallas($ids = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $sql = "
        SELECT
            id,
            codigo,
            LTRIM(RTRIM(nombre)) AS nombre,
            updated_at
        FROM talla
    ";

        if ($ids) {
            $arr = array_values(array_filter(array_map('intval', explode(',', $ids))));
            if (!empty($arr)) {
                $in = implode(',', $arr);
                $sql .= " WHERE id IN ($in) ";
            }
        }

        $rows = Yii::$app->db->createCommand($sql)->queryAll();

        return [
            'ok' => true,
            'count' => count($rows),
            'tallas' => $rows,
            'server_time' => time(),
        ];
    }

    public function actionColores($ids = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $sql = "
        SELECT
            id,
            codigo,
            LTRIM(RTRIM(nombre)) AS nombre,
            updated_at
        FROM color
    ";

        $params = [];

        if ($ids) {
            $arr = array_values(array_filter(array_map('intval', explode(',', $ids))));
            if (!empty($arr)) {
                $in = implode(',', $arr);
                $sql .= " WHERE id IN ($in) ";
            }
        }

        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        return [
            'ok' => true,
            'count' => count($rows),
            'colores' => $rows,
            'server_time' => time(),
        ];
    }
    /**
     * POST /index.php?r=hunter/hunter-api/sync-bodega
     * JSON:
     * {
     *   "codigoBodega": "031",
     *   "syncItems": true
     * }
     */
    public function actionSyncBodega()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        if (!$request->isPost) {
            throw new BadRequestHttpException('Solo POST');
        }

        // Ya lo parsea Yii por el JsonParser del config
        $data = $request->getBodyParams();

        $codigoBodegaRaw = $data['codigoBodega'] ?? null;
        $codigoBodega = (int) trim((string)$codigoBodegaRaw);

        // lo dejamos, aunque NO lo usemos por ahora
        $syncItems = filter_var($data['syncItems'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($codigoBodega <= 0) {
            throw new BadRequestHttpException('codigoBodega es obligatorio');
        }

        $svc = new \common\components\SiesaSyncService(
            'E:\laragon\bin\php\php-8.1.10-win32-vs16-x64\php.exe',
            'C:\Apache24\htdocs\conektasiesav2'
        );

        $user = Yii::$app->user->identity;

        Yii::info([
            'accion' => 'sync-bodega',
            'codigoBodega' => $codigoBodega,
            'syncItems' => $syncItems,
            'usuario' => $user?->username,
            'ip' => $request->userIP,
        ], 'inventario');

        // 🔥 SOLO inventario, que es lo que existe en conektasiesav2
        $r = $syncItems
            ? $svc->syncInventarioBodega($codigoBodega)
            : ['ok' => true, 'codigoBodega' => $codigoBodega, 'log' => '(sin sincronización; usando inventario local)'];

        Yii::info([
            'accion' => 'sync-bodega',
            'resultado_ok' => $r['ok'] ?? null,
            'codigoBodega' => $codigoBodega,
            'usuario' => $user?->username,
        ], 'inventario');

        return ['ok' => true, 'resultado' => $r];
    }
}
