<?php

namespace frontend\modules\crossdocking\controllers;

use Yii;
use frontend\models\Conteocdscdestino;
use frontend\models\Conteocdscdestinofactura;
use frontend\models\search\ConteocdscdestinoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\TraspasoFacturaCDSCForm;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use frontend\models\Impresora;
use frontend\models\Parametroscontrol;

/**
 * ConteocdscdestinoController implements the CRUD actions for Conteocdscdestino model.
 */
class ConteocdscdestinoController extends Controller
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
     * Lists all Conteocdscdestino models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ConteocdscdestinoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexalmacen($idconteofactura, $origen = null)
    {
        $modelfactura = Conteocdscdestinofactura::findOne(['id' => $idconteofactura]);

        $programa = 'index';
        if ($origen == 'traspaso'){
            $programa = 'index_almacen_imprimir';
        }

        $searchModel = new ConteocdscdestinoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idconteofactura);

        return $this->render($programa, [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'origen' => $origen,
            'modelfactura' => $modelfactura
        ]);
    }

    public function actionPrintbox ($idconteofactura, $idcentrooperacion = null){

        $model = new TraspasoFacturaCDSCForm();

        $impresora = Impresora::find()->where(['nombre' => 'Packing'])->one();
        $model->idImpresora = $impresora->id;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if ($model->validate()) {

                    $impresora = Impresora::find()->where(['id' => $model->idImpresora])->one();
                    $printerURL = $impresora->ip;
                    $nombrePrinter = $impresora->nombre;

                    $result = $this->generarEtiquetasCaja($idconteofactura, $idcentrooperacion, $printerURL);

                    if ($result) {
                        Yii::$app->session->setFlash('success', 'Las etiquetas han sido generadas correctamente. '. $nombrePrinter . ' (' . $printerURL . ')' );
                    } else {
                        Yii::$app->session->setFlash('error', 'Error Generando las etiquetas. '. $printerURL);
                    }

                }

                return $this->redirect(['indexalmacen', 'idconteofactura' => $idconteofactura, 'origen' => 'traspaso']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('select_print_traspaso', [
                'model' => $model,
            ]);
        }

    }

    function generarEtiquetasCaja($idconteofactura, $idcentrooperacion, $printerURL)
    {
        $modelfactura = Conteocdscdestinofactura::findOne(['id' => $idconteofactura]);

        $dataProviderDestino = $modelfactura->getConteocdscdestinos()
                        ->andFilterWhere([
                            'idConteocdscdestinofactura' => $idconteofactura,
                            'idCentroOperacion' => $idcentrooperacion,
                        ])->all();

        if ($dataProviderDestino === null) {
            throw new \Exception("No se encontró datos de impresión pra la factura con ID: $modelfactura->id");
        }

        $puerto = '9100';
    
        try{
            $connector = new NetworkPrintConnector($printerURL, $puerto);
            $printer = new Printer($connector);

            Conteocdscdestino::imprimirEtiquetas($idconteofactura, $printer, $dataProviderDestino);
            // Cerrar conexión con la impresora
            $printer->close();

            return true;

        } catch (\Exception $e) {
            echo "Error al imprimir: " . $e->getMessage();
        }

        return false;

        //$mpdf->Output("etiquetas_factura_{$modelfactura->numeroFactura}.pdf", \Mpdf\Output\Destination::INLINE);
    }

    function imprimirEtiquetas($idconteofactura, $printer, $dataProviderDestino){

        foreach ($dataProviderDestino as $destino) {
            for ($i = 1; $i <= $destino->numeroCajas; $i++) {
                // Renderizar la vista con los datos necesarios

                $dataProviderDetalle = $destino->getConteocdscdestinodetalles()
                        ->andFilterWhere([
                            'idConteocdscdestino' => $destino->id,
                        ])->all();

                $totalunidadempaque = 0;

                foreach($dataProviderDetalle as $detalle){
                    if (!$detalle->item->unidadempaque){
                        $equivalencia = 1;
                    }else {
                        $equivalencia = $detalle->item->unidadempaque->equivalencia;
                    }
                    $unidadempaque = $detalle->totalUnidades / $equivalencia;
                    $totalunidadempaque = $totalunidadempaque + $unidadempaque;
                }

                $printer->setTextSize(2, 2); // Tamaño grande para el título
                $printer->setEmphasis(true); // Negrita
                $printer->text("CONTEO CDSC No:");

                $printer->setEmphasis(false);
                $printer->text($idconteofactura . "\n");

                $printer->setEmphasis(true);
                $printer->text("CAJA ");
                $printer->setEmphasis(false);
                $printer->text("$i DE {$destino->numeroCajas}\n");

                $printer->setEmphasis(true);
                $printer->text("PROVEEDOR:\n");
                $printer->setEmphasis(false);

                $printer->setTextSize(2, 2); // Tamaño Normal
                $printer->text("{$destino->factura->proveedor->nit} - {$destino->factura->proveedor->razonSocial}\n");

                $printer->setTextSize(2, 2); // Tamaño grande
                $printer->setEmphasis(true);
                $printer->text("FACTURA:");
                $printer->setEmphasis(false);
                $printer->text("{$destino->factura->numeroFactura}\n");

                $printer->setEmphasis(true);
                $printer->text("ALM. DESTINO:\n");
                $printer->setEmphasis(false);

                $printer->setTextSize(2, 2); // Tamaño normal
                $printer->text("{$destino->centrooperacion->codigo} - {$destino->centrooperacion->nombre}\n");

                $printer->setTextSize(2, 2); // Tamaño grande

                $printer->setEmphasis(true);
                $printer->text("UND. EMPAQUE:");
                $printer->setEmphasis(false);
                $printer->text(round($totalunidadempaque, 0) . "\n");

                $printer->setEmphasis(true);
                $printer->text("TOTAL UNDS:");
                $printer->setEmphasis(false);
                $printer->text("{$destino->total}\n");

                $printer->setEmphasis(true);
                $printer->text("USUARIO:\n");
                $printer->setEmphasis(false);

                $printer->setTextSize(2, 2); // Tamaño normal

                $printer->text("{$destino->usuarioconteo->user->empleado->nombreEmpleado}\n");

                // Separador
                // $printer->text("-------------------------\n");

                // Cortar papel después de cada etiqueta
                $printer->cut();
            }
        }
    }

    public function actionPrintitems ($idconteofactura, $idcentrooperacion = null){

        $model = new TraspasoFacturaCDSCForm();

        $impresora = Impresora::find()->where(['nombre' => 'Packing'])->one();
        $model->idImpresora = $impresora->id;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if ($model->validate()) {

                    $impresora = Impresora::find()->where(['id' => $model->idImpresora])->one();
                    $printerURL = $impresora->ip;
                    $nombrePrinter = $impresora->nombre;

                    $result = $this->generarTirilla($idconteofactura, $idcentrooperacion, $printerURL);

                    if ($result) {
                        Yii::$app->session->setFlash('success', 'Las etiquetas han sido generadas correctamente. '. $nombrePrinter . ' (' . $printerURL . ')' );
                    } else {
                        Yii::$app->session->setFlash('error', 'Error Generando las etiquetas. '. $printerURL);
                    }

                }

                return $this->redirect(['indexalmacen', 'idconteofactura' => $idconteofactura, 'origen' => 'traspaso']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('select_print_traspaso', [
                'model' => $model,
            ]);
        }

    }

    function generarTirilla($idconteofactura, $idcentrooperacion, $printerURL){

        $parametrosMap = [
            '001' => 'nombreEmpresa',
            '002' => 'nitEmpresa',
            '003' => 'direccionEmpresa',
            '004' => 'telefonoEmpresa',
        ];
        
        $modelparametros = ParametrosControl::find()
            ->where(['codigo' => array_keys($parametrosMap)])
            ->indexBy('codigo')
            ->all();
        
        $parametros = [];
        foreach ($parametrosMap as $codigo => $nombreVariable) {
            if (isset($modelparametros[$codigo])) {
                $parametros[$nombreVariable] = $modelparametros[$codigo]->valor;
            }
        }

        $modelfactura = Conteocdscdestinofactura::findOne(['id' => $idconteofactura]);

        $dataProviderDestino = $modelfactura->getConteocdscdestinos()
            ->andFilterWhere([
                'idConteocdscdestinofactura' => $idconteofactura,
            ])
            ->andFilterWhere([
                'idCentroOperacion' => $idcentrooperacion ?: null, // Si está vacío, no filtrar por centro de operación
            ])
            ->all(); // Obtener los datos como un arreglo

        $puerto = '9100';
    
        try{
            $connector = new NetworkPrintConnector($printerURL, $puerto);
            $printer = new Printer($connector);
    
            $this->imprimirTirilla($idconteofactura, 
                                    $printer, 
                                    $dataProviderDestino, 
                                    $parametros);
            
            // Cerrar conexión con la impresora
            $printer->close();
    
            return true;
    
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die("hola");
        }

        return false;
    }

    function imprimirTirilla ($idconteofactura, $printer, $dataProviderDestino, $parametros){

        foreach ($dataProviderDestino as $destino) {

            Conteocdscdestino::generarTraspasoEncabezado($parametros, $destino, $printer);

            $dataProviderDetalle = $destino->getConteocdscdestinodetalles()
                ->where(['>', 'totalUnidades', 0])
                ->all();

            $totales = Conteocdscdestino::generarTraspasoDetalle($dataProviderDetalle, $printer);

            Conteocdscdestino::generarTraspasoPiePagina ($destino, $totales, $printer);

            // Cortar papel
            $printer->cut();
        }

    }

    /**
     * Displays a single Conteocdscdestino model.
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
     * Creates a new Conteocdscdestino model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Conteocdscdestino();

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
     * Updates an existing Conteocdscdestino model.
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
     * Deletes an existing Conteocdscdestino model.
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
     * Finds the Conteocdscdestino model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Conteocdscdestino the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Conteocdscdestino::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
