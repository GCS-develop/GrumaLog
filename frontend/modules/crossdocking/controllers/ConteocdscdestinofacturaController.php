<?php

namespace frontend\modules\crossdocking\controllers;

use frontend\models\Bodegatipodocumento;
use frontend\models\search\ConteocdscdestinodetalleSearch;
use Yii;
use frontend\models\Conteocdscdestinofactura;
use frontend\models\search\ConteocdscdestinofacturaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use Mpdf\Mpdf;
use kartik\mpdf\Pdf;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

use frontend\models\search\ConteocdscdestinoSearch;
use frontend\models\LegalizaConteoCDSCForm;
use frontend\models\EntradaFacturaCDSCForm;
use frontend\models\TraspasoFacturaCDSCForm;
use frontend\models\DataOrdenCompra;
use frontend\models\Centrooperacion;
use frontend\models\Conteocdscusuario;
use frontend\models\Conteocdscusuariodestino;
use frontend\models\Conteobylecturacodigo;
use frontend\models\Conteocdscdestinodetalle;
use frontend\models\search\TransferenciaordencompraexcelSearch;
use frontend\models\search\TransferenciatransitoexcelSearch;

use frontend\models\Ordendecompra;
use frontend\models\Traspaso;
use frontend\models\Parametroscontrol;

/**
 * ConteocdscdestinofacturaController implements the CRUD actions for Conteocdscdestinofactura model.
 */
class ConteocdscdestinofacturaController extends Controller
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
     * Lists all Conteocdscdestinofactura models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $idestadofactura = null;
        $idestadolegaliza = null;
        $idestadoentrada = null;
        $idestadotraspaso = null;

        $searchModel = new ConteocdscdestinofacturaSearch();
        $dataProvider = $searchModel->search(
            $this->request->queryParams,
            $idestadofactura,
            $idestadolegaliza,
            $idestadoentrada,
            $idestadotraspaso
        );

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexlegaliza()
    {
        $idestadofactura = 2;
        $idestadolegaliza = 0;
        $idestadoentrada = null;
        $idestadotraspaso = null;

        $searchModel = new ConteocdscdestinofacturaSearch();
        $dataProvider = $searchModel->search(
            $this->request->queryParams,
            $idestadofactura,
            $idestadolegaliza,
            $idestadoentrada,
            $idestadotraspaso
        );

        return $this->render('index_legaliza', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexentrada()
    {
        $idestadofactura = null;
        $idestadolegaliza = null;
        $idestadoentrada = 1;
        $idestadotraspaso = null;

        $searchModel = new ConteocdscdestinofacturaSearch();
        $dataProvider = $searchModel->search(
            $this->request->queryParams,
            $idestadofactura,
            $idestadolegaliza,
            $idestadoentrada,
            $idestadotraspaso
        );

        return $this->render('index_entrada', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndextraspaso()
    {
        $idestadofactura = null;
        $idestadolegaliza = null;
        $idestadoentrada = 2;
        $idestadotraspaso = 2;

        $searchModel = new ConteocdscdestinofacturaSearch();
        $dataProvider = $searchModel->search(
            $this->request->queryParams,
            $idestadofactura,
            $idestadolegaliza,
            $idestadoentrada,
            $idestadotraspaso
        );

        return $this->render('index_traspaso', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Conteocdscdestinofactura models.
     *
     * @return string
     */
    public function actionIndexalmacen($idconteofactura, $origen = null)
    {
        $modelfactura = $this->findModel($idconteofactura);

        /*if ($modelfactura->idEstado == 2){
            Yii::$app->session->setFlash( 'error', 'Factura Ya se Encuentra Finalizada');
            return $this->redirect(['/crossdocking/conteocdscdestinofactura/index']);
        }*/

        $programa = 'index_almacen';
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

    /**
     * Displays a single Conteocdscdestinofactura model.
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
     * Creates a new Conteocdscdestinofactura model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new DataOrdenCompra();

        $modelco = Centrooperacion::findOne(['codigo' => '002']);
        $model->idCentroOperacion = $modelco->id;
        $idCia = 7;

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                $mensajeError = 'Error Actualizando Registro';

                if ($model->validate()) {

                    $OK = Ordendecompra::insertarDatosOC ($idCia, 
                                                            $model->idCentroOperacion, 
                                                            $model->idTipoDocumento, 
                                                            $model->numeroOrdenCompra);


                    if ($OK > 0){

                        $modeloc = Ordendecompra::find()->where(['idCO' => $model->idCentroOperacion,
                                                            'idTipoDocumento' => $model->idTipoDocumento,
                                                            'consecutivo' => $model->numeroOrdenCompra])->one();

                        $model->idOrdenCompra = $modeloc->id;
                        $model->fechaOrden = $modeloc->fecha;
                        $respuesta = Conteocdscdestinofactura::grabarOrdenCompra($model);

                        if ($respuesta) {
                            Yii::$app->session->setFlash('success', 'Registro Actualizado');
                        } else {
                            Yii::$app->session->setFlash('error', $mensajeError);
                        }
                    }else {
                        switch($OK){
                            case - 1:
                                Yii::$app->session->setFlash( 'error', 'OC. Tiene Inconsistencias. No Unidades de Item No Son Equivalentes a la Unidad de Medida');
                                break;
                            case 0: 
                                Yii::$app->session->setFlash( 'error', 'Número Orden de Compra NO Existe');
                                break;
                        }
                    }
                }

                return $this->redirect(['index', 'id' => $model->idAgenda]);
            }
        }

        return $this->render('create_orden_compra', [
            'model' => $model,
        ]);

    }

    /**
     * Updates an existing Conteocdscdestinofactura model.
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
     * Deletes an existing Conteocdscdestinofactura model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $idconteofactura = $id;
        $model = $this->findModel($idconteofactura);

        if ($model->idEstado == 2){
            Yii::$app->session->setFlash( 'error', 'Factura Ya se Encuentra Finalizada');
            return $this->redirect(['/crossdocking/conteocdscdestinofactura/index']);
        }

        $modeldestinos = $model->conteocdscdestinos;
        foreach ($modeldestinos as $destinos) {
            $id = $destinos->id;

            // Define la condición
            $condition = ['=', 'idConteocdscdestino', $id];

            // Elimina todos los registros que cumplan la condición
            $count = Conteocdscdestinodetalle::deleteAll($condition);

            $count = Conteocdscusuariodestino::deleteAll($condition);

            $destinos->delete();
        }

        // Elimina todos los registros de Conteos de Usuario que cumplan la condición
        $condition = ['=', 'idConteocdscdestinofactura', $idconteofactura];
        $count = Conteocdscusuario::deleteAll($condition);

        // Elimina todos los registros de Conteos de Usuario que cumplan la condición
        $condition = ['=', 'idConteoFactura', $idconteofactura];
        $count = Conteobylecturacodigo::deleteAll($condition);

        $model->delete();

        Yii::$app->session->setFlash('success', $count . ' Registros Eliminados Con Éxito');

        return $this->redirect(['index']);
    }

    public function actionViewlegalizaconteo($idconteofactura)
    {
        $modelfactura = $this->findModel($idconteofactura);

        $searchModel = new ConteocdscdestinoSearch();
        $dataProviderBD = $searchModel->search($this->request->queryParams, $idconteofactura);

        $idproveedor = $modelfactura->idProveedor;
        $numerofactura = $modelfactura->numeroFactura;

        $dataProvider = Conteocdscdestinofactura::generarDataConteoCurvas($idconteofactura, $idproveedor, $numerofactura);

        return $this->render('view_legalizacion_conteo', [
            'dataProvider' => $dataProvider,
            'dataProviderBD' => $dataProviderBD,
            'modelfactura' => $modelfactura,
        ]);
    }

    public function actionLegalizarconteo($idconteofactura)
    {

        $model = new LegalizaConteoCDSCForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if ($model->validate()) {

                    $modelfactura = Conteocdscdestinofactura::findOne(['id' => $idconteofactura]);
                    $modelfactura->idLegalizado = 1;
                    $modelfactura->idCentroOperacionLegaliza = $model->idCentroOperacion;
                    $modelfactura->observacionLegalizacion = $model->observacion;

                    $modelfactura->idEstadoEntrada = 1; // Autorizada
                    $modelfactura->idEstadoTraspaso = 1; // Pendiente

                    $modelfactura->fechaLegaliza = new Expression('GETDATE()');
                    $modelfactura->idUserLegaliza = Yii::$app->user->identity->id;

                    $respuesta = $modelfactura->save();

                    if ($respuesta) {
                        Yii::$app->session->setFlash('success', 'Registro Actualizado');
                    } else {
                        Yii::$app->session->setFlash('error', 'Error Actualizando Registro');
                    }
                }

                return $this->redirect(['indexlegaliza']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create_legaliza_conteo', [
                'model' => $model,
            ]);
        }
    }

    public function actionViewentradafactura($idconteofactura)
    {
        $modelfactura = $this->findModel($idconteofactura);

        $searchModel = new ConteocdscdestinoSearch();
        $dataProviderBD = $searchModel->search($this->request->queryParams, $idconteofactura);

        $idproveedor = $modelfactura->idProveedor;
        $numerofactura = $modelfactura->numeroFactura;

        $dataProvider = Conteocdscdestinofactura::generarDataConteoCurvas($idconteofactura, $idproveedor, $numerofactura);

        return $this->render('view_entrada_factura', [
            'dataProvider' => $dataProvider,
            'dataProviderBD' => $dataProviderBD,
            'modelfactura' => $modelfactura,
        ]);
    }

    public function actionEntradafactura($idconteofactura)
    {
        $modelfactura = Conteocdscdestinofactura::findOne(['id' => $idconteofactura]);
        $model = new EntradaFacturaCDSCForm();

        $model->idTipoDocumento = $modelfactura->idSerieEntrada;
        $model->numeroEntrada = $modelfactura->numeroEntrada;
        $model->fechaEntrada = $modelfactura->fechaEntrada;
        $model->numeroFacturaEntrada = $modelfactura->numeroFacturaEntrada;
        $model->consignacion = $modelfactura->consignacion;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if ($model->validate()) {

                    $modelfactura = Conteocdscdestinofactura::findOne(['id' => $idconteofactura]);
                    $modelfactura->idSerieEntrada = $model->idTipoDocumento;
                    $modelfactura->numeroEntrada = $model->numeroEntrada;
                    $modelfactura->consignacion = $model->consignacion;

                    // $modelfactura->fechaEntrada = new Expression('GETDATE()');
                    $modelfactura->fechaEntrada = $model->fechaEntrada;
                    $modelfactura->idUserEntrada = Yii::$app->user->identity->id;

                    $modelfactura->numeroFacturaEntrada = $model->numeroFacturaEntrada;

                    $respuesta = $modelfactura->save();

                    if ($respuesta) {
                        Yii::$app->session->setFlash('success', 'Registro Actualizado');
                    } else {
                        Yii::$app->session->setFlash('error', 'Error Actualizando Registro');
                    }
                }

                return $this->redirect(['indexentrada']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create_entrada_factura', [
                'model' => $model,
            ]);
        }
    }

    public function actionTraspasofactura ($idconteofactura){

        $model = new TraspasoFacturaCDSCForm();

        $modelco = Centrooperacion::findOne(['codigo' => '002']);
        $model->idCentroOperacionMovimiento = $modelco->id;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if ($model->validate()) {

                    $modelfactura = Conteocdscdestinofactura::findOne(['id' => $idconteofactura]);
                    
                    $bodega =Bodegatipodocumento::findOne(['id' => $model->idBodegaMovimiento]);

                    $modelfactura->idCentroOperacionMovimiento = $model->idCentroOperacionMovimiento;
                    $modelfactura->idBodegaMovimiento = $bodega->idBodega;
                    $modelfactura->idTipoDocumentoMovimiento = $bodega->idTipoDocumento;

                    $respuesta = $modelfactura->save();

                    if ($respuesta) {
                        Yii::$app->session->setFlash('success', 'Registro Actualizado');
                    } else {
                        Yii::$app->session->setFlash('error', 'Error Actualizando Registro');
                    }
                }

                return $this->redirect(['indextraspaso']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create_traspaso_factura', [
                'model' => $model,
            ]);
        }
    }

    public function actionTransferenciatraspaso($idconteofactura)
    {
        $model = $this->findModel($idconteofactura);

        if (!$model->idTipoDocumentoMovimiento){
            Yii::$app->session->setFlash('error', 'Falta Especificar Tipo Documento de Movimiento');
            return $this->redirect(['indextraspaso']);
        }

        if (!$model->idBodegaMovimiento){
            Yii::$app->session->setFlash('error', 'Falta Especificar Bodega de Movimiento');
            return $this->redirect(['indextraspaso']);
        }

        if (!$model->idErpEntrada){
            Yii::$app->session->setFlash('error', 'Falta Ejecutar Transferencia de Entrada');
            return $this->redirect(['indextraspaso']);
        }

        $data = Conteocdscdestinodetalle::generarTransferenciaTraspasoERP($model);

        if (is_array($data) && !empty($data['id'])) {

            $idtransferenciatraspasoerp = $data['id'];
            $tipomovimiento = 3;

            $model->idTransferenciatraspasoerp = $idtransferenciatraspasoerp;
            $model->save();

            $searchModel = new TransferenciatransitoexcelSearch();
            $dataProvider = $searchModel->search($this->request->queryParams, $idtransferenciatraspasoerp);

            return $this->render('index_transferencia_traspasoerp', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'idtransferenciatraspasoerp' => $idtransferenciatraspasoerp,
                'idconteofactura' => $idconteofactura
            ]);

        }
        
        return $this->redirect(['indextraspaso']);
    }

    public function actionImprimirtraspasos($idconteofactura, $idcentrooperacion = null)
    {

        $modelparametros = ParametrosControl::findOne(['codigo' => '001']);
        $nombreEmpresa = $modelparametros->valor;

        $modelparametros = ParametrosControl::findOne(['codigo' => '002']);
        $nitEmpresa = $modelparametros->valor;

        $modelparametros = ParametrosControl::findOne(['codigo' => '003']);
        $direccionEmpresa = $modelparametros->valor;

        $modelparametros = ParametrosControl::findOne(['codigo' => '004']);
        $telefonoEmpresa = $modelparametros->valor;


        $modelfactura = Conteocdscdestinofactura::findOne(['id' => $idconteofactura]);

        $dataProviderDestino = $modelfactura->getConteocdscdestinos()
            ->andFilterWhere([
                'idConteocdscdestinofactura' => $idconteofactura,
                'idCentroOperacion' => $idcentrooperacion,
            ])->all();
        ;

        // Recorrer los recibos y generar e imprimir cada uno
        foreach ($dataProviderDestino as $destino) {

            $encabezado = Conteocdscdestinofactura::generarTraspasoEncabezado(
                $nombreEmpresa,
                $nitEmpresa,
                $direccionEmpresa,
                $telefonoEmpresa,
                $destino
            );

            $dataProviderDetalle = $destino->getConteocdscdestinodetalles()
                ->where(['>', 'totalUnidades', 0])
                ->all();

            $detalle = Conteocdscdestinofactura::generarTraspasoDetalle($dataProviderDetalle);

            $piepagina = Conteocdscdestinofactura::generarTraspasoPiePagina($destino);

            // Renderizar la vista del recibo con los datos
            $content = $this->render('view_print_traspaso_POS', [
                'encabezado' => $encabezado,
                'detalle' => $detalle,
                'piepagina' => $piepagina
            ]);

            ob_start();
            header('Content-Type: text/html');

            // Imprimir el contenido en la impresora POS
            echo "<script>window.print();</script>";
            echo $content;

            // Enviar el comando de corte de papel
            echo "<script>document.execCommand('print', false, null);</script>";
        }
    }

    public function actionPrinttraspaso($idconteofactura, $idcentrooperacion = null)
    {

        $content = Conteocdscdestinofactura::printTraspaso($idconteofactura, $idcentrooperacion);

        return $this->render('view_print_traspaso', [
            'content' => $content,
        ]);

        /*
        
        $content .= $contentAll;

        $pdf = new Pdf();
        
        $pdf->filename = "Recibo_".".pdf";
        $pdf->defaultFontSize = 9;
        $pdf->marginLeft = 7.0;
        $pdf->defaultFont = 'Arial';
        $pdf->destination = Pdf::DEST_DOWNLOAD;
        $pdf->mode = Pdf::MODE_UTF8;
        
        $mpdf = $pdf->api; // fetches mpdf api
        //
        
        //$mpdf->SetHeader('TPM'); // call methods or set any properties
        $mpdf->WriteHtml($content); // call mpdf write html
        $mpdf->Output($pdf->filename, 'D'); // call the mpdf api output as needed
        */
    }

    public function actionEnd($id)
    {
        $model = $this->findModel($id);

        if ($model->idEstado == 2){
            Yii::$app->session->setFlash( 'error', 'Factura Ya se Encuentra Finalizada');
            return $this->redirect(['/crossdocking/conteocdscdestinofactura/index']);
        }

        $model->idEstado = 2;
        $model->save();

        $result = Conteocdscusuario::updateAll(['idEstado' => 0], ['idConteocdscdestinofactura' => $id]);

        return $this->redirect(['index']);
    }

    public function actionTransferencia($idconteofactura)
    {
        $model = $this->findModel($idconteofactura);
        $idtransferenciaerp = $model->idTransferenciaerp;

        if (!$model->idSerieEntrada){
            Yii::$app->session->setFlash('error', 'Falta Especificar Tipo Documento de Entrada');
            return $this->redirect(['indexentrada']);
        }

        if (!$model->numeroEntrada){
            Yii::$app->session->setFlash('error', 'Falta Especificar Número de Entrada');
            return $this->redirect(['indexentrada']);
        }

        if (!$model->numeroFacturaEntrada){
            Yii::$app->session->setFlash('error', 'Falta Especificar Número de Factura de Entrada');
            return $this->redirect(['indexentrada']);
        }

        $searchModel = new ConteocdscdestinodetalleSearch();
        $dataProviderBD = $searchModel->searchSIESA($idconteofactura);

        $idtransferenciaerp = Conteocdscdestinofactura::crearRegistroTransferencia($model,  $dataProviderBD);

        $model->idTransferenciaerp = $idtransferenciaerp;
        $model->save();

        return $this->redirect(['viewtransferenciaocerp', 'id' => $idtransferenciaerp, 'idconteofactura' => $idconteofactura]);
    }

    public function actionViewtransferenciaocerp($id, $idconteofactura)
    {
        $searchModel = new TransferenciaordencompraexcelSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $id);

        return $this->render('index_transferenciaocerp', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'idtransferenciaerp' => $id,
            'idconteofactura' => $idconteofactura
        ]);
    }


    /**
     * Finds the Conteocdscdestinofactura model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Conteocdscdestinofactura the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Conteocdscdestinofactura::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
