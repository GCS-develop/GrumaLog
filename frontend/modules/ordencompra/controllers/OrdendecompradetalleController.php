<?php

namespace frontend\modules\ordencompra\controllers;

use common\components\PrinterService;
use common\components\StickerGenerator;
use frontend\models\Ordendecompra;
use Yii;
use frontend\models\Ordendecompradetalle;
use frontend\models\search\OrdendecompradetalleSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\Impresoraspaxarbodega;
use frontend\models\Selectimpresora;
use frontend\models\Item;

/**
 * OrdendecompradetalleController implements the CRUD actions for Ordendecompradetalle model.
 */
class OrdendecompradetalleController extends Controller
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
     * Lists all Ordendecompradetalle models.
     *
     * @return string
     */
    public function actionIndex($idordencompra)
    {
        $modeloc = Ordendecompra::findOne(['id' => $idordencompra]);

        $searchModel = new OrdendecompradetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idordencompra);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modeloc' => $modeloc
        ]);
    }

    /**
     * Displays a single Ordendecompradetalle model.
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
     * Creates a new Ordendecompradetalle model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Ordendecompradetalle();

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
     * Updates an existing Ordendecompradetalle model.
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
     * Deletes an existing Ordendecompradetalle model.
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

    public function actionPrintitems($idordencompra, $fecha_activacion, $iditem = null, $origen = null)
    {
        $model = new Selectimpresora();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->validate()) {
                $impresora = Impresoraspaxarbodega::findOne($model->idImpresora);

                if (!$impresora) {
                    Yii::$app->session->setFlash('error', 'No se encontró la impresora seleccionada.');
                    return $this->redirect(['index', 'idordencompra' => $idordencompra]);
                }

                if (!in_array($impresora->tipo, ['ip', 'recurso'])) {
                    Yii::$app->session->setFlash('error', 'Tipo de impresora no reconocido.');
                    return $this->redirect(['index', 'idordencompra' => $idordencompra]);
                }

                $orden = Ordendecompra::findOne($idordencompra);
                if (!$orden) {
                    throw new NotFoundHttpException('Orden de compra no encontrada.');
                }

                $query = $orden->getPurchaseOrderItems();
                if ($iditem !== null) {
                    $query->andWhere(['idItem' => $iditem]);
                }

                $detalles = $query->all();

                // Generar etiquetas
                $labelContent = StickerGenerator::generar($detalles, $fecha_activacion, $origen);

                /*
                //Mostrar en pantalla (modo prueba)
                if (YII_ENV_DEV || isset($_GET['preview'])) {
                    return $this->renderContent('<pre>' . htmlspecialchars($labelContent) . '</pre>');
                }
                */

                // Enviar a la impresora
                $resultado = PrinterService::send($impresora, $labelContent);

                if ($resultado['codigo'] === 0) {
                    Yii::$app->session->setFlash('success', 'Etiquetas generadas correctamente.');
                } else {
                    Yii::$app->session->setFlash('error', 'Error al imprimir: ' . $resultado['mensaje']);
                }
            }

            return $this->redirect(['index', 'idordencompra' => $idordencompra]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('select_impresora', ['model' => $model]);
        }
    }

    function generarSticker($idordencompra, $iditem, $fecha_activacion, $impresora, $origen)
    {

        $model = Ordendecompra::findOne(['id' => $idordencompra]);

        $dataProviderDetalle = $model->getPurchaseOrderItems()
            ->andFilterWhere([
                'idItem' => $iditem,
            ])
            ->all(); // Obtener los datos como un arreglo
        try {

            $labelContent = $this->imprimirSticker(
                $fecha_activacion,
                $dataProviderDetalle,
                $origen
            );

            $resultado = $this->sendToPrinter($impresora, $labelContent);

            // Cerrar conexión con la impresora

            return $resultado;

        } catch (\Exception $e) {
            return [
                'mensaje' => $e->getMessage(),
                'codigo' => -2
            ];
        }

    }

    function imprimirSticker($fecha_activacion, $dataProviderDetalle, $origen)
    {

        $labelsPerRow = 3; // Número de etiquetas por fila
        $labelWidth = 200; // Ancho de cada etiqueta
        $labelHeight = 300; // Altura de cada fila

        $count = 0;
        $content = '';

        foreach ($dataProviderDetalle as $detalle) {

            // Calcular posición X e Y para la etiqueta actual
            $row = floor($count / $labelsPerRow); // Número de fila
            $column = $count % $labelsPerRow; // Columna en la fila

            $x = $column * $labelWidth; // Desplazamiento horizontal
            $y = $row * $labelHeight;  // Desplazamiento vertical

            $modelitem = Item::findOne(['id' => $detalle->idItem]);

            $precio = Item::obtenerPrecioVenta($modelitem->codigoBarras, $fecha_activacion);

            $stickerContent = Item::generarContenidoSticker($modelitem, $precio, $x, $y);

            $content .= $stickerContent;

            $count++;

        }

        return $content;
    }

    private function sendToPrinter($impresora, $data)
    {
        $error = 0;

        $respuesta['error_message'] = '';
        $respuesta['error_code'] = 0;
        $respuesta['ok'] = false;

        switch ($impresora->tipo) {
            case 'ip':
                $respuesta = Impresoraspaxarbodega::imprimirxip($impresora, $data);
                break;
            case 'recurso':
                $respuesta = Impresoraspaxarbodega::imprimirxrecurso($impresora, $data);
                break;
        }

        if ($respuesta['ok'] == false) {
            $error = 1;
        }

        switch ($error) {
            case 0:
                return [
                    'mensaje' => "Sticker enviado con Éxito a la impresora",
                    'codigo' => $error
                ];
            case 1:
                $error_message = $respuesta['error_message'];
                $error_code = $respuesta['error_code'];

                return [
                    'mensaje' => "No se pudo conectar a la impresora, revisar que este conectada por favor!, Ip: $impresora->ip:$impresora->puerto Error: $error_message ($error_code)",
                    'codigo' => $error
                ];
            default:
                return [
                    'mensaje' => "No se ha Encontrado Item",
                    'codigo' => -1
                ];
        }

    }

    /**
     * Finds the Ordendecompradetalle model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Ordendecompradetalle the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ordendecompradetalle::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
