<?php

namespace frontend\modules\siesa\controllers;

use common\models\search\InventariosWsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;
use common\models\InventariosWs;
use common\models\User;
use frontend\models\Bodegas;
use yii;
use common\components\ImpresionPreciosService;
use common\models\search\InventariosWsPrintSearch;
use yii\web\Response;

/**
 * InventariosWsController implements the CRUD actions for InventariosWs model.
 */
class InventariosWsController extends Controller
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
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all InventariosWs models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new InventariosWsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexPrint()
    {
        $searchModel = new InventariosWsPrintSearch();
        $searchModel->load(Yii::$app->request->get());

        $ean   = $searchModel->EAN ? trim($searchModel->EAN) : null;
        $item  = $searchModel->Item ? trim($searchModel->Item) : null;
        $ext1  = $searchModel->Extension1 ? trim($searchModel->Extension1) : null;
        $ext2  = $searchModel->Extension2 ? trim($searchModel->Extension2) : null;

        $byEan  = !empty($ean);
        $byItem = !empty($item);

        $inv  = new \common\models\InventariosWs();
        $rows = [];

        // 1) INVENTARIO DESDE WS "Inventarios"
        if ($byEan) {
            // Buscar directamente por EAN en Inventarios
            $rows = $inv->getAllInventariosSiesa($ean, null, 'EAN', 'Inventarios');
        } elseif ($byItem) {
            // Buscar primero en Productos por Item para obtener los EAN
            $productos = $inv->getAllInventariosSiesa(null, $item, 'Item', 'Productos');
            $eans = array_values(array_unique(array_filter(array_column($productos, 'CodigoBarras'))));

            $rows = [];
            if (!empty($eans)) {
                foreach ($eans as $eanFromItem) {
                    $chunk = $inv->getAllInventariosSiesa($eanFromItem, null, 'EAN', 'Inventarios');
                    if (!empty($chunk)) {
                        $rows = array_merge($rows, $chunk);
                    }
                }
            }
        } else {
            $rows = [];
        }

        // Si no hay nada, devolver grilla vacía
        if (empty($rows)) {
            $dataProvider = new \yii\data\ArrayDataProvider([
                'allModels'  => [],
                'pagination' => ['pageSize' => 50],
            ]);
            return $this->render('index_print', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }

        // 2) NORMALIZADOR
        $norm = function ($v) {
            $v = is_scalar($v) ? (string)$v : '';
            $v = strtoupper(trim($v));
            $v = ltrim($v, '0');
            return $v;
        };

        // 2.a) SI BUSCAS POR EAN → filtrar por bodega del usuario
        if ($byEan) {
            $codigoBodega = \common\models\User::getCodigoCOUsuario(Yii::$app->user->id);
            if (!$codigoBodega) {
                $dataProvider = new \yii\data\ArrayDataProvider([
                    'allModels'  => [],
                    'pagination' => ['pageSize' => 50],
                ]);
                return $this->render('index_print', [
                    'searchModel'  => $searchModel,
                    'dataProvider' => $dataProvider,
                ]);
            }
            $codigoRef = $norm($codigoBodega);

            $rows = array_values(array_filter($rows, function ($r) use ($norm, $codigoRef, $ext1, $ext2) {
                // Filtramos por bodega SOLO cuando es EAN
                $b1 = array_key_exists('Bodega', $r) ? $norm($r['Bodega']) : null;
                if ($b1 === null || $b1 !== $codigoRef) return false;

                if ($ext1) {
                    $rExt1 = $norm($r['Extension1'] ?? '');
                    if (strpos($rExt1, $norm($ext1)) === false) return false;
                }
                if ($ext2) {
                    $rExt2 = $norm($r['Extension2'] ?? '');
                    if (strpos($rExt2, $norm($ext2)) === false) return false;
                }
                return true;
            }));
        }

        // 2.b) SI BUSCAS POR ITEM → NO filtrar por bodega, solo opcionalmente por color/talla
        if ($byItem) {
            if ($ext1) {
                $needle = $norm($ext1);
                $rows = array_values(array_filter($rows, function ($r) use ($norm, $needle) {
                    $rExt1 = $norm($r['Extension1'] ?? '');
                    return strpos($rExt1, $needle) !== false;
                }));
            }

            if ($ext2) {
                $needle = $norm($ext2);
                $rows = array_values(array_filter($rows, function ($r) use ($norm, $needle) {
                    $rExt2 = $norm($r['Extension2'] ?? '');
                    return strpos($rExt2, $needle) !== false;
                }));
            }
        }

        // 3) PRECIOS DESDE SQL (TU QUERY)
        $preciosRows = [];
        if ($byEan) {
            $preciosRows = $inv->getUltimoPrecioSiesa($ean, null);
        } elseif ($byItem) {
            $preciosRows = $inv->getUltimoPrecioSiesa(null, $item);
        }

        $mapPrecios = InventariosWs::mapPreciosPorEANDesdeUltimoPrecio($preciosRows);

        foreach ($rows as &$r) {
            $eanRow = $r['EAN'] ?? $r['CodigoBarras'] ?? null;
            if ($eanRow && isset($mapPrecios[$eanRow])) {
                $info = $mapPrecios[$eanRow];

                $r['Price']       = $info['precio_venta'];
                $r['PrecioCampo'] = $info['campo_precio'];

                if (!empty($info['costo'])) {
                    $r['CostoPromedioUnitario'] = $info['costo'];
                }
            }
        }
        unset($r);

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels'  => $rows,
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'attributes' => [
                    'Item',
                    'Referencia',
                    'Descripcion',
                    'EAN',
                    'Extension1',
                    'Extension2',
                    'Bodega',
                    'NombreBodega',
                    'CantidadExistente',
                    'CantidadDisponible',
                    'CantidadComprometida',
                    'CantidadDisponible_POS',
                    'CostoPromedioUnitario',
                ],
            ],
        ]);

        return $this->render('index_print', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }




    public function actionPrint($ean)
    {
        $inv  = new \common\models\InventariosWs();
        $rows = $inv->getAllInventariosSiesa($ean);
        if (empty($rows)) {
            Yii::$app->session->setFlash('error', "No se encontró inventario para EAN {$ean}");
            return $this->redirect(['index-print']);
        }

        $codigoBodega = \common\models\User::getCodigoCOUsuario(Yii::$app->user->id);
        $row = null;
        foreach ($rows as $r) {
            if ((string)ltrim($r['Bodega'] ?? '', '0') === (string)ltrim($codigoBodega ?? '', '0')) {
                $row = $r;
                break;
            }
        }
        if (!$row) {
            Yii::$app->session->setFlash('error', "No hay inventario en tu bodega para EAN {$ean}");
            return $this->redirect(['index-print']);
        }

        // --- NUEVO: buscar precio con el WS de UltimoPrecio ---
        $preciosRows = $inv->getUltimoPrecioSiesa($ean, null);
        $mapPrecios  = InventariosWs::mapPreciosPorEANDesdeUltimoPrecio($preciosRows);

        $precioFinal = 0.0;
        if (isset($mapPrecios[$ean])) {
            $precioFinal = $mapPrecios[$ean]['precio_venta'];
        } else {
            // fallback al costo
            $precioFinal = (float)($row['CostoPromedioUnitario'] ?? 0);
        }

        // Construimos el "modelo" que espera el servicio
        $m = new \stdClass();
        $m->existencia  = (int)($row['CantidadExistente'] ?? 0);
        $m->precio      = $precioFinal;
        $m->codigoBarra = $row['EAN'] ?? $ean;

        $svc = new ImpresionPreciosService();
        $r = $svc->imprimirPorExistencia($m);

        Yii::$app->session->setFlash($r['status'] === 'success' ? 'success' : 'error', $r['message']);
        return $this->redirect(['index-print']);
    }


    public function actionPrintajax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ean      = Yii::$app->request->post('ean');
        $precio   = (float)Yii::$app->request->post('precio', 0);
        $cantidad = (int)Yii::$app->request->post('input', 0);

        if (!$ean || $cantidad <= 0) {
            return ['status' => 'error', 'message' => 'Datos inválidos.'];
        }

        $svc = new ImpresionPreciosService();
        // Aquí no necesitamos existencia, solo imprimir la cantidad ingresada
        return $svc->imprimirCantidad($cantidad, $precio, $ean);
    }


    public function actionTestItemProductos($item)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $inv  = new \common\models\InventariosWs();
        // 👇 consultamos el WS de Productos por Item
        $rows = $inv->getAllInventariosSiesa(null, $item, 'Item', 'Productos');

        return [
            'ok'     => true,
            'item'   => $item,
            'count'  => is_array($rows) ? count($rows) : 0,
            'sample' => array_slice($rows ?? [], 0, 5),
            // útil para ver si viene EAN/Referencia/etc.
            'keys'   => isset($rows[0]) ? array_keys($rows[0]) : [],
        ];
    }
}
