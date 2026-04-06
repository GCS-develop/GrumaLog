<?php

namespace frontend\modules\catalogos\controllers;

use frontend\models\Inventario;
use frontend\models\search\InventarioSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use Yii;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use common\components\SiesaSyncService;

/**
 * InventarioController implements the CRUD actions for Inventario model.
 */
class InventarioController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                        'sincronizar-inventario' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Inventario models grouped by SKU.
     */
    public function actionIndex()
    {
        $searchModel = new InventarioSearch();
        $searchModel->load($this->request->queryParams);

        $bodegas   = array_values(array_filter(array_map('trim', (array)($searchModel->codigoBodega ?? []))));
        $hasFilter = !empty($bodegas);
        $skuRows   = [];
        $totals    = null;
        $limited   = false;

        if ($hasFilter) {
            $skuRows = $this->buildSkuRows($searchModel, $bodegas);
            if (count($skuRows) > 1000) {
                $skuRows = array_slice($skuRows, 0, 1000);
                $limited = true;
            }
            $totals = [
                'grumaBruto' => (int) Inventario::getotalExistenciasGruma($bodegas),
                'siesaBruto' => (int) Inventario::getTotalExistenciasSiesa($bodegas),
                'grumaReal'  => (int) Inventario::getotalExistenciasGrumaReal($bodegas),
                'siesaReal'  => (int) Inventario::getTotalExistenciasSiesaReal($bodegas),
            ];
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'skuRows'     => $skuRows,
            'totals'      => $totals,
            'hasFilter'   => $hasFilter,
            'bodegas'     => $bodegas,
            'limited'     => $limited,
        ]);
    }

    /**
     * Construye la lista de SKUs con sus EANs anidados, aplicando todos los filtros del search.
     */
    private function buildSkuRows(InventarioSearch $search, array $bodegas): array
    {
        $params  = [];
        $wheres  = ['1=1'];

        $phs = [];
        foreach ($bodegas as $i => $cod) {
            $phs[] = ":bod{$i}";
            $params[":bod{$i}"] = $cod;
        }
        $wheres[] = 'inv.codigoBodega IN (' . implode(',', $phs) . ')';

        if (!empty($search->item)) {
            $wheres[] = 'i.item = :item';
            $params[':item'] = (int)$search->item;
        }
        if (!empty($search->codigoBarras)) {
            $wheres[] = 'inv.codigoBarras LIKE :barras';
            $params[':barras'] = '%' . trim($search->codigoBarras) . '%';
        }
        if (!empty($search->color)) {
            $wheres[] = 'c.nombre LIKE :color';
            $params[':color'] = '%' . trim($search->color) . '%';
        }
        if (!empty($search->talla)) {
            $wheres[] = 't.codigo LIKE :talla';
            $params[':talla'] = '%' . trim($search->talla) . '%';
        }

        $where = implode(' AND ', $wheres);

        $sqlSku = "
            SELECT
                inv.codigoBodega,
                i.item,
                i.idColor,
                i.idTalla,
                c.nombre AS color,
                t.codigo AS talla,
                MAX(COALESCE(inv.existencia, 0)) AS existencia,
                COUNT(DISTINCT inv.codigoBarras)  AS totalEan
            FROM inventario inv
            JOIN item i ON i.id = inv.idItem
            LEFT JOIN color c ON c.id = i.idColor
            LEFT JOIN talla t ON t.id = i.idTalla
            WHERE {$where}
            GROUP BY inv.codigoBodega, i.item, i.idColor, i.idTalla, c.nombre, t.codigo
            ORDER BY inv.codigoBodega, i.item, t.codigo, c.nombre
        ";

        $sqlEan = "
            SELECT
                inv.codigoBodega,
                i.item,
                i.idColor,
                i.idTalla,
                inv.codigoBarras,
                inv.existencia,
                inv.fechaUltimaActualizacion
            FROM inventario inv
            JOIN item i ON i.id = inv.idItem
            LEFT JOIN color c ON c.id = i.idColor
            LEFT JOIN talla t ON t.id = i.idTalla
            WHERE {$where}
            ORDER BY i.item, inv.codigoBarras
        ";

        $skus = Yii::$app->db->createCommand($sqlSku, $params)->queryAll();
        $eans = Yii::$app->db->createCommand($sqlEan, $params)->queryAll();

        $eanMap = [];
        foreach ($eans as $ean) {
            $key = $ean['codigoBodega'] . '|' . $ean['item'] . '|' . $ean['idColor'] . '|' . $ean['idTalla'];
            $eanMap[$key][] = [
                'codigoBarras'             => $ean['codigoBarras'],
                'existencia'               => $ean['existencia'],
                'fechaUltimaActualizacion' => $ean['fechaUltimaActualizacion'],
            ];
        }

        // ── Existencia SIESA por (bodega, barcode) ────────────────────────────
        // Igual que actionDiferencias: query todo SIESA para las bodegas, sin filtrar por barcode
        $siesaExMap   = null; // null = fallo de conexión; array = datos ok
        $siesaErrMsg  = null;
        if (!empty($bodegas)) {
            $sBodPhs = [];
            $sParams  = [];
            foreach ($bodegas as $i => $cod) {
                $sBodPhs[] = ":sbod{$i}";
                $sParams[":sbod{$i}"] = $cod;
            }
            // No indexamos por bodega para evitar discrepancias de formato (ej: "81" vs "081")
            // Ya filtramos por bodega en el WHERE, así que todos los registros son de esas bodegas
            $sqlSiesa = "
                SELECT t131.f131_id                AS barras,
                       t400.f400_cant_existencia_1 AS existencia
                FROM t400_cm_existencia t400
                INNER JOIN t150_mc_bodegas t150
                  ON t400.f400_rowid_bodega = t150.f150_rowid
                INNER JOIN t131_mc_items_barras t131
                  ON t400.f400_rowid_item_ext = t131.f131_rowid_item_ext
                WHERE t150.f150_id IN (" . implode(',', $sBodPhs) . ")
            ";
            try {
                $siesaRows = Yii::$app->dbSiesa->createCommand($sqlSiesa, $sParams)->queryAll();
                $siesaExMap = [];
                foreach ($siesaRows as $sr) {
                    $siesaExMap[$sr['barras']] = (float)$sr['existencia'];
                }
            } catch (\Exception $e) {
                $siesaErrMsg = $e->getMessage();
                Yii::error('[Inventario/buildSkuRows] SIESA error: ' . $e->getMessage(), __METHOD__);
            }
        }

        foreach ($skus as &$sku) {
            $key = $sku['codigoBodega'] . '|' . $sku['item'] . '|' . $sku['idColor'] . '|' . $sku['idTalla'];
            $sku['eans'] = $eanMap[$key] ?? [];

            if ($siesaExMap === null) {
                // error de conexión SIESA
                $sku['existenciaSiesa'] = null;
            } else {
                // En SIESA t400 guarda existencia por item_ext, no por barcode.
                // Todos los barcodes del mismo item_ext tienen el mismo valor,
                // así que tomamos el primero que exista en el mapa (no sumamos).
                $siesaVal = null;
                foreach ($sku['eans'] as $ean) {
                    if (isset($siesaExMap[$ean['codigoBarras']])) {
                        $siesaVal = $siesaExMap[$ean['codigoBarras']];
                        break;
                    }
                }
                $sku['existenciaSiesa'] = $siesaVal ?? 0;
            }

            unset($sku['idColor'], $sku['idTalla']);
        }

        return $skus;
    }

    /**
     * Displays a single Inventario model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Inventario model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Inventario();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Inventario model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Inventario model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Inventario model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Inventario the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Inventario::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionSincronizarInventario()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $codigoBodega = Yii::$app->request->post('InventarioSearch')['codigoBodega'] ?? null;
        if (empty($codigoBodega)) {
            return ['success' => false, 'error' => 'Debe seleccionar una bodega para sincronizar.'];
        }

        try {
            $syncItemsActivo = (int)\frontend\models\Parametroscontrol::getValorparametro('SYNC_ITEMS_SIESA') === 1;
            $svc = new SiesaSyncService('E:\laragon\bin\php\php-8.1.10-win32-vs16-x64\php.exe', 'C:\Apache24\htdocs\conektasiesav2');
            $r   = $svc->syncInventarioBodega((int)$codigoBodega, $syncItemsActivo);
            return [
                'success' => empty($r['error']),
                'log'     => $r['log']   ?? '',
                'error'   => $r['error'] ?? '',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function actionSkus()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $bodegas = Yii::$app->request->get('bodegas', []);
        if (is_string($bodegas)) $bodegas = [$bodegas];
        $bodegas = array_values(array_filter(array_map('trim', $bodegas)));

        $params = [];
        $where  = '1=1';

        if (!empty($bodegas)) {
            $phs = [];
            foreach ($bodegas as $i => $cod) {
                $phs[] = ":bod{$i}";
                $params[":bod{$i}"] = $cod;
            }
            $where .= ' AND inv.codigoBodega IN (' . implode(',', $phs) . ')';
        }

        // SKUs agrupados
        $sql = "
            SELECT
                inv.codigoBodega,
                i.item,
                i.idColor,
                i.idTalla,
                c.nombre AS color,
                t.codigo AS talla,
                MAX(COALESCE(inv.existencia, 0)) AS existencia,
                COUNT(DISTINCT inv.codigoBarras)  AS totalEan
            FROM inventario inv
            JOIN item i   ON i.id = inv.idItem
            LEFT JOIN color c ON c.id = i.idColor
            LEFT JOIN talla t ON t.id = i.idTalla
            WHERE {$where}
            GROUP BY inv.codigoBodega, i.item, i.idColor, i.idTalla, c.nombre, t.codigo
            ORDER BY inv.codigoBodega, i.item
        ";

        $skus = Yii::$app->db->createCommand($sql, $params)->queryAll();

        // EANs individuales (para expansión)
        $eanSql = "
            SELECT
                inv.codigoBodega,
                i.item,
                i.idColor,
                i.idTalla,
                inv.codigoBarras,
                inv.existencia,
                inv.fechaUltimaActualizacion
            FROM inventario inv
            JOIN item i ON i.id = inv.idItem
            WHERE {$where}
            ORDER BY inv.codigoBodega, i.item, inv.codigoBarras
        ";

        $eans = Yii::$app->db->createCommand($eanSql, $params)->queryAll();

        // Agrupar EANs por clave de SKU
        $eanMap = [];
        foreach ($eans as $ean) {
            $key = $ean['codigoBodega'] . '|' . $ean['item'] . '|' . $ean['idColor'] . '|' . $ean['idTalla'];
            $eanMap[$key][] = [
                'codigoBarras'            => $ean['codigoBarras'],
                'existencia'              => $ean['existencia'],
                'fechaUltimaActualizacion' => $ean['fechaUltimaActualizacion'],
            ];
        }

        foreach ($skus as &$sku) {
            $key = $sku['codigoBodega'] . '|' . $sku['item'] . '|' . $sku['idColor'] . '|' . $sku['idTalla'];
            $sku['eans'] = $eanMap[$key] ?? [];
            unset($sku['idColor'], $sku['idTalla']); // limpiar campos internos
        }

        return [
            'total' => count($skus),
            'rows'  => $skus,
        ];
    }

    /**
     * Diagnóstico temporal: lista tipos de documento con movimientos en t470,
     * con descripción desde t303 y conteo de registros + unidades.
     * Visitar: /catalogos/inventario/tipos-movimiento
     */
    public function actionTiposMovimiento()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // t303_co_tipo_docto es el catálogo de tipos de documento en Siesa
        $sql = "
            SELECT
                t350.f350_id_tipo_docto                        AS codigo,
                MAX(COALESCE(t303.f303_descripcion, '—'))      AS descripcion,
                COUNT(DISTINCT t350.f350_rowid)                AS num_documentos,
                SUM(ABS(t470.f470_cant_1))                    AS unidades_totales,
                MIN(CAST(t350.f350_fecha AS DATE))             AS fecha_primera,
                MAX(CAST(t350.f350_fecha AS DATE))             AS fecha_ultima
            FROM t470_cm_movto_invent t470
            JOIN t350_co_docto_contable t350
              ON t470.f470_rowid_docto = t350.f350_rowid
            LEFT JOIN t303_co_tipo_docto t303
              ON t303.f303_id = t350.f350_id_tipo_docto
            WHERE CAST(t350.f350_fecha AS DATE) >= DATEADD(MONTH, -6, GETDATE())
            GROUP BY t350.f350_id_tipo_docto
            ORDER BY unidades_totales DESC
        ";

        try {
            $rows = Yii::$app->dbSiesa->createCommand($sql)->queryAll();
            return ['ok' => true, 'tipos' => $rows];
        } catch (\Exception $e) {
            // Si t303 no existe, intentar sin join de descripción
            $sql2 = "
                SELECT
                    t350.f350_id_tipo_docto       AS codigo,
                    COUNT(DISTINCT t350.f350_rowid) AS num_documentos,
                    SUM(ABS(t470.f470_cant_1))      AS unidades_totales,
                    MIN(CAST(t350.f350_fecha AS DATE)) AS fecha_primera,
                    MAX(CAST(t350.f350_fecha AS DATE)) AS fecha_ultima
                FROM t470_cm_movto_invent t470
                JOIN t350_co_docto_contable t350
                  ON t470.f470_rowid_docto = t350.f350_rowid
                WHERE CAST(t350.f350_fecha AS DATE) >= DATEADD(MONTH, -6, GETDATE())
                GROUP BY t350.f350_id_tipo_docto
                ORDER BY unidades_totales DESC
            ";
            try {
                $rows = Yii::$app->dbSiesa->createCommand($sql2)->queryAll();
                return ['ok' => true, 'nota' => 'sin tabla t303, sin descripcion', 'tipos' => $rows];
            } catch (\Exception $e2) {
                return ['ok' => false, 'error' => $e2->getMessage()];
            }
        }
    }

    public function actionDiferencias()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $bodegas = Yii::$app->request->get('bodegas', []);
        if (is_string($bodegas)) $bodegas = [$bodegas];
        $bodegas = array_values(array_filter(array_map('trim', $bodegas)));

        if (empty($bodegas)) {
            $bodegas = \frontend\models\Inventario::getCodigosBodegaUnicos();
        }

        // ── Inventario Gruma (codigoBarras → existencia) ──────────────────────
        $query = \frontend\models\Inventario::find()
            ->select(['codigoBarras', 'existencia', 'codigoBodega'])
            ->asArray();
        if (!empty($bodegas)) {
            $query->andWhere(['in', 'codigoBodega', $bodegas]);
        }
        $grumaRows = $query->all();
        $gruma = [];
        foreach ($grumaRows as $r) {
            $gruma[$r['codigoBarras']] = (float)$r['existencia'];
        }

        // ── Inventario Siesa (codigoBarras → existencia) ──────────────────────
        $ph  = implode(',', array_map(fn($i) => ":b{$i}", array_keys($bodegas)));
        $sql = "SELECT DISTINCT t131.f131_id AS barras,
                       t400.f400_cant_existencia_1 AS existencia
                FROM t400_cm_existencia t400
                INNER JOIN t150_mc_bodegas t150
                    ON t400.f400_rowid_bodega = t150.f150_rowid
                INNER JOIN t131_mc_items_barras t131
                    ON t400.f400_rowid_item_ext = t131.f131_rowid_item_ext
                WHERE t150.f150_id IN ({$ph})";
        $cmd = Yii::$app->dbSiesa->createCommand($sql);
        foreach ($bodegas as $i => $cod) {
            $cmd->bindValue(":b{$i}", $cod, \PDO::PARAM_STR);
        }
        $siesa = array_column($cmd->queryAll(), 'existencia', 'barras');

        // ── Comparación ───────────────────────────────────────────────────────
        $soloGruma = [];
        $diferente  = [];
        foreach ($gruma as $barras => $ex) {
            if (!array_key_exists($barras, $siesa)) {
                $soloGruma[] = ['barras' => $barras, 'existenciaGruma' => $ex];
            } elseif ((float)$siesa[$barras] !== $ex) {
                $diferente[] = ['barras' => $barras, 'gruma' => $ex, 'siesa' => (float)$siesa[$barras]];
            }
        }
        $soloSiesa = [];
        foreach ($siesa as $barras => $ex) {
            if (!array_key_exists($barras, $gruma)) {
                $soloSiesa[] = ['barras' => $barras, 'existenciaSiesa' => (float)$ex];
            }
        }

        $phG = implode(',', array_map(fn($i) => ":gd{$i}", array_keys($bodegas)));

        // ── Barcodes fantasma: mismo código de barras → múltiples idItem distintos ──
        // Cuando el mismo barcode está en inventario para 2 idItem diferentes,
        // el cálculo REAL los agrupa en SKUs separados y suma doble.
        // La comparación por barcode no lo detecta porque las existencias "coinciden".
        $sqlDupBarcode = "
            SELECT
                sub.codigoBarras,
                sub.codigoBodega,
                sub.totalItems,
                sub.existencia
            FROM (
                SELECT
                    inv.codigoBarras,
                    inv.codigoBodega,
                    COUNT(DISTINCT inv.idItem) AS totalItems,
                    MAX(inv.existencia)        AS existencia
                FROM inventario inv
                WHERE inv.codigoBodega IN ({$phG})
                GROUP BY inv.codigoBarras, inv.codigoBodega
            ) sub
            WHERE sub.totalItems > 1
            ORDER BY sub.totalItems DESC, sub.codigoBarras
        ";
        $cmdDupBarcode = Yii::$app->db->createCommand($sqlDupBarcode);
        foreach ($bodegas as $i => $cod) {
            $cmdDupBarcode->bindValue(":gd{$i}", $cod, \PDO::PARAM_STR);
        }
        $barcodesFantasma = $cmdDupBarcode->queryAll();

        // Para cada barcode fantasma, listar los SKUs a los que apunta
        $detalleBarcodes = [];
        if (!empty($barcodesFantasma)) {
            $barcodeList = array_unique(array_column($barcodesFantasma, 'codigoBarras'));
            $bPhs = implode(',', array_map(fn($i) => ":bc{$i}", array_keys($barcodeList)));
            $sqlDetalle = "
                SELECT
                    inv.codigoBarras,
                    inv.codigoBodega,
                    i.item,
                    i.idColor,
                    c.nombre AS color,
                    i.idTalla,
                    t.codigo AS talla,
                    inv.existencia
                FROM inventario inv
                JOIN item i ON i.id = inv.idItem
                LEFT JOIN color c ON c.id = i.idColor
                LEFT JOIN talla t ON t.id = i.idTalla
                WHERE inv.codigoBodega IN ({$phG})
                  AND inv.codigoBarras IN ({$bPhs})
                ORDER BY inv.codigoBarras, i.item
            ";
            $cmdDetalle = Yii::$app->db->createCommand($sqlDetalle);
            foreach ($bodegas as $i => $cod) {
                $cmdDetalle->bindValue(":gd{$i}", $cod, \PDO::PARAM_STR);
            }
            foreach ($barcodeList as $i => $bc) {
                $cmdDetalle->bindValue(":bc{$i}", $bc, \PDO::PARAM_STR);
            }
            // agrupar por barcode
            foreach ($cmdDetalle->queryAll() as $row) {
                $detalleBarcodes[$row['codigoBarras']][] = $row;
            }
        }

        // ── SKU Split: item_exts de Siesa que Gruma divide en ≥2 grupos ──────────
        // Paso 1: Siesa → barcode→{item_ext_id, existencia}
        $sqlSiesaMap = "
            SELECT t131.f131_id                    AS barras,
                   t400.f400_rowid_item_ext         AS item_ext_id,
                   t400.f400_cant_existencia_1       AS siesaEx
            FROM t400_cm_existencia t400
            JOIN t150_mc_bodegas t150
              ON t400.f400_rowid_bodega = t150.f150_rowid
            JOIN t131_mc_items_barras t131
              ON t400.f400_rowid_item_ext = t131.f131_rowid_item_ext
            WHERE t150.f150_id IN ({$ph})
        ";
        $cmdSM = Yii::$app->dbSiesa->createCommand($sqlSiesaMap);
        foreach ($bodegas as $i => $cod) { $cmdSM->bindValue(":b{$i}", $cod, \PDO::PARAM_STR); }
        $siesaMapRows = $cmdSM->queryAll();

        $siesaBarToExt = []; // barcode → {item_ext_id, siesaEx}
        foreach ($siesaMapRows as $row) {
            $siesaBarToExt[$row['barras']] = [
                'item_ext_id' => $row['item_ext_id'],
                'siesaEx'     => (float)$row['siesaEx'],
            ];
        }

        // Paso 2: Gruma → barcode→{skuKey, item, color, talla, existencia}
        $sqlGrumaMap = "
            SELECT inv.codigoBarras,
                   i.item,
                   i.idColor,
                   i.idTalla,
                   c.nombre  AS color,
                   t.codigo  AS talla,
                   inv.existencia
            FROM inventario inv
            JOIN item i ON i.id = inv.idItem
            LEFT JOIN color c ON c.id = i.idColor
            LEFT JOIN talla t ON t.id = i.idTalla
            WHERE inv.codigoBodega IN ({$phG})
        ";
        $cmdGM = Yii::$app->db->createCommand($sqlGrumaMap);
        foreach ($bodegas as $i => $cod) { $cmdGM->bindValue(":gd{$i}", $cod, \PDO::PARAM_STR); }

        $grumaBarToSku = [];
        foreach ($cmdGM->queryAll() as $row) {
            $grumaBarToSku[$row['codigoBarras']] = [
                'skuKey'    => $row['item'] . '|' . $row['idColor'] . '|' . $row['idTalla'],
                'item'      => $row['item'],
                'color'     => $row['color'],
                'talla'     => $row['talla'],
                'existencia'=> (float)$row['existencia'],
            ];
        }

        // Paso 3: agrupar por item_ext_id y detectar splits
        $extData = [];
        foreach ($siesaBarToExt as $barras => $sInfo) {
            $extId = $sInfo['item_ext_id'];
            if (!isset($extData[$extId])) {
                $extData[$extId] = ['siesaEx' => $sInfo['siesaEx'], 'skuGroups' => [], 'barcodes' => []];
            }
            $extData[$extId]['barcodes'][] = $barras;
            if (isset($grumaBarToSku[$barras])) {
                $g = $grumaBarToSku[$barras];
                $extData[$extId]['skuGroups'][$g['skuKey']] = [
                    'item' => $g['item'], 'color' => $g['color'],
                    'talla' => $g['talla'], 'existencia' => $g['existencia'],
                ];
            }
        }

        // Paso 4: encontrar item_exts con >1 grupo en Gruma y existencias distintas
        $skuSplits = [];
        foreach ($extData as $extId => $data) {
            if (count($data['skuGroups']) <= 1) continue;
            $grumaContrib = array_sum(array_column($data['skuGroups'], 'existencia'));
            $diff = $grumaContrib - $data['siesaEx'];
            if (abs($diff) > 0.001) {
                $skuSplits[] = [
                    'item_ext_id' => $extId,
                    'siesaEx'     => $data['siesaEx'],
                    'grumaReal'   => $grumaContrib,
                    'diff'        => $diff,
                    'grupos'      => array_values($data['skuGroups']),
                    'barcodes'    => $data['barcodes'],
                ];
            }
        }

        // ── Items incompletos (item/color/talla NULL o 0) ─────────────────────
        $sqlNulos = "
            SELECT i.item AS itemCodigo,
                   i.idColor,
                   i.idTalla,
                   COUNT(inv.id) AS registros,
                   MAX(inv.existencia) AS existencia
            FROM inventario inv
            JOIN item i ON i.id = inv.idItem
            WHERE inv.codigoBodega IN ({$phG})
              AND (i.idColor IS NULL OR i.idTalla IS NULL OR i.item IS NULL OR i.item = 0)
            GROUP BY i.item, i.idColor, i.idTalla
            ORDER BY i.item
        ";
        $cmdNulos = Yii::$app->db->createCommand($sqlNulos);
        foreach ($bodegas as $i => $cod) {
            $cmdNulos->bindValue(":gd{$i}", $cod, \PDO::PARAM_STR);
        }
        $itemsNulos = $cmdNulos->queryAll();

        return [
            'soloGruma'        => array_slice($soloGruma, 0, 200),
            'soloSiesa'        => array_slice($soloSiesa, 0, 200),
            'diferente'        => array_slice($diferente,  0, 200),
            'itemsNulos'       => $itemsNulos,
            'barcodesFantasma' => $barcodesFantasma,
            'detalleBarcodes'  => $detalleBarcodes,
            'skuSplits'        => array_slice($skuSplits, 0, 100),
            'totales'          => [
                'soloGruma'        => count($soloGruma),
                'soloSiesa'        => count($soloSiesa),
                'diferente'        => count($diferente),
                'itemsNulos'       => count($itemsNulos),
                'barcodesFantasma' => count($barcodesFantasma),
                'skuSplits'        => count($skuSplits),
            ],
        ];
    }
}
