<?php

namespace frontend\modules\programacion\controllers;

use Yii;
use frontend\models\Conteoentregamercancia;
use frontend\models\search\ConteoentregamercanciaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\Programacionentregamercancia;
use frontend\models\Agendaentregamercancia;
use frontend\models\search\OrdendecompradetalleSearch;
use frontend\models\search\AgendaentregamercanciaSearch;
use frontend\models\Ordendecompradetalle;
use frontend\models\Userconteo;
use frontend\models\Estadoprogramacion;
use frontend\models\Estadoconteo;
use frontend\models\Estadolegalizacion;
use frontend\models\LegalizaConteoForm;

use common\models\ProcedimientosGenerales;

/**
 * ConteoentregamercanciaController implements the CRUD actions for Conteoentregamercancia model.
 */
class ConteoentregamercanciaController extends Controller
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
     * Lists all Conteoentregamercancia models.
     *
     * @return string
     */
    public function actionIndex($idprogramacion, $item = null)
    {
        $modelprogramacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);

        $idagenda = $modelprogramacion->agendaEntregaMercancia->id;
        $modelagendaentrega = Agendaentregamercancia::findOne(['id' => $idagenda]);

        $searchModel = new ConteoentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idprogramacion, $item);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelagendaentrega' => $modelagendaentrega,
            'modelprogramacion' => $modelprogramacion
        ]);
    }

    public function actionIndexitem($idprogramacion, $item = null)
    {
        $modelprogramacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);
        $idordencompra = $modelprogramacion->agendaEntregaMercancia->idOrdenCompra;
        $idcategoria = $modelprogramacion->agendaEntregaMercancia->idCategoria;

        $dataProvider = Conteoentregamercancia::generarDataConteoCurvas ($idordencompra, $idcategoria);

        return $this->render('index_detalleoc', [
            'dataProvider' => $dataProvider,
            'modelprogramacion' => $modelprogramacion
        ]);
    }

    public function actionIndexagenda($idagenda)
    {

        /*$modelprogramacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);*/
        $iduserconteo = null;
        $item = null;
        $idprogramacion = null;

        $modelagenda = Agendaentregamercancia::findOne(['id' => $idagenda]);

        $searchModel = new ConteoentregamercanciaSearch();
        $dataProviderBD = $searchModel->search($this->request->queryParams, $idprogramacion, $item, $idagenda, $iduserconteo);

        $idordencompra = null;
        $idcategoria = null;
        $dataProvider = Conteoentregamercancia::generarDataConteoCurvas ($idagenda, $idordencompra, $idcategoria, $iduserconteo, $idprogramacion);

        // Agrupar los datos por bodega
        $dataByItem = [];
        foreach ($dataProvider as $model) {
            $item = $model['item'];
            if (!isset($dataByItem[$item])) {
                $dataByItem[$item] = [];
            }
            $dataByItem[$item][] = $model;
        }

        return $this->render('index_detalleoc_agenda', [
            'dataProvider' => $dataProvider,
            'dataByItem' => $dataByItem,
            'dataProviderBD' => $dataProviderBD,
            'modelagenda' => $modelagenda,
        ]);
    }

    public function actionIndexprogramacion($idprogramacion)
    {

        /*
        $modelprogramacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);
        $idordencompra = $modelprogramacion->agendaEntregaMercancia->idOrdenCompra;
        $idcategoria = $modelprogramacion->agendaEntregaMercancia->idCategoria;
        */

        $modelprogramacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);
        $iduserconteo = null;
        $idagenda = null;

        $modeluser = Userconteo::findOne(['id' => $modelprogramacion->idUserConteo]);
        $modelagenda = Agendaentregamercancia::findOne(['id' => $modelprogramacion->idAgendaEntregaMercancia]);

        $item = $modelprogramacion->item;

        $searchModel = new ConteoentregamercanciaSearch();
        $dataProviderBD = $searchModel->search($this->request->queryParams, $idprogramacion, $item, $idagenda, $iduserconteo);

        $idordencompra = null;
        $idcategoria = null;
        $dataProvider = Conteoentregamercancia::generarDataConteoCurvas ($idagenda, $idordencompra, $idcategoria, $iduserconteo, $idprogramacion);

        // Agrupar los datos por bodega
        $dataByItem = [];
        foreach ($dataProvider as $model) {
            $item = $model['item'];
            if (!isset($dataByItem[$item])) {
                $dataByItem[$item] = [];
            }
            $dataByItem[$item][] = $model;
        }

        return $this->render('index_detalleoc_programacion', [
            'dataProvider' => $dataProvider,
            'dataByItem' => $dataByItem,
            'dataProviderBD' => $dataProviderBD,
            'modelprogramacion' => $modelprogramacion,
        ]);
    }

    public function actionIndexall()
    {

        $searchModel = new ConteoentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index_all', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Conteoentregamercancia model.
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
     * Creates a new Conteoentregamercancia model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($idprogramacion)
    {

        $modelprogramacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);

        //var_dump($modelprogramacion);die("hola: ".$idprogramacion);

        $idcategoria = $modelprogramacion->agendaEntregaMercancia->idCategoria;
        $categoria = $modelprogramacion->agendaEntregaMercancia->categoria->nombre;
        $idordencompra = $modelprogramacion->agendaEntregaMercancia->idOrdenCompra;

        $searchModelDetalleOC = new OrdendecompradetalleSearch();
        $dataProviderDetalleOC = $searchModelDetalleOC->searchDetalleItem1($idordencompra, $idcategoria);

        $model = new Conteoentregamercancia();
        $model->idProgramacionEntregaMercancia = $idprogramacion;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('index_detalleoc', [
                'idprogramacion' => $idprogramacion,
                'categoria' => $categoria,
                'dataProviderDetalleOC' => $dataProviderDetalleOC,
            ]);
        }
    }

    public function actionSelect ($idprogramacion, $iditem){

        $model = new Conteoentregamercancia();
        $model->idProgramacionEntregaMercancia = $idprogramacion;
        $model->idItem = $iditem;
        $model->unidadesConteo = 0;

        $idordencompra = $model->programacionEntregaMercancia->agendaEntregaMercancia->idOrdenCompra;

        $totalPorOrdenItem = Ordendecompradetalle::find()
                                ->select(['SUM(cantidadPendiente) AS totalCantidadPendiente'])
                                ->where(['idOrdenCompra' => $idordencompra, 'idItem' => $iditem])
                                ->scalar();

        $model->unidadesAsignadas = $totalPorOrdenItem;

        if (!$model->save()){
            $mensaje = ProcedimientosGenerales::erroresModelo ($model->getErrors());
            Yii::$app->session->setFlash( 'error', $mensaje);
        }

        $model = Programacionentregamercancia::findOne(['id' => $idprogramacion]);
        $model->idEstado = 5;
        $model->save();

        return $this->redirect(['index', 'idprogramacion' => $idprogramacion]);
    }

    /**
     * Updates an existing Conteoentregamercancia model.
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
     * Deletes an existing Conteoentregamercancia model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $idprogramacion = $model->idProgramacionEntregaMercancia;

        $model->delete();

        return $this->redirect(['index', 'idprogramacion' => $idprogramacion]);
    }

    public function actionFinalizarconteo($id, $origen)
    {
        // Codigo = 3 -> Finalizado  (idEstado = 4)
        $modelestado = Estadoprogramacion::findOne(['codigo' => 3]);
        $idestadoprogramacion = $modelestado->id;

        $modelestado = Estadoconteo::findOne(['codigo' => 2]);
        $idestadoconteo = $modelestado->id;

        if ($origen == 'programacion'){

            $model = Programacionentregamercancia::findOne(['id' => $id]);
            $idagenda = $model->idAgendaEntregaMercancia;

            // Realizar el update y obtener el número de filas afectadas
            $filasActualizadas = Programacionentregamercancia::updateAll(
                                                                [   'idEstado' => $idestadoprogramacion], 
                                                                [   'id' => $id]);
        }else{
            $model = Agendaentregamercancia::findOne(['id' => $id]);
            $model->idEstadoConteo = $idestadoconteo;
            $model->idEstadoLegalizacion = 3;
            $model->save();

            $idagenda = $model->id;
            $filasActualizadas = 0;

            $filasActualizadas = Programacionentregamercancia::updateAll(
                [   'idEstado' => $idestadoprogramacion], 
                [   'idAgendaEntregaMercancia' => $id]);
        }

        // Validar si se actualizó al menos una fila
        if (($filasActualizadas > 0) && ($origen == 'programacion')) {
            $mensaje = "Se actualizaron " . $filasActualizadas . " registros";

            $numRegistros = ProgramacionEntregaMercancia::countProgramacionByAgendaAndEstado($idagenda, null);
            $numRegistrosFin = ProgramacionEntregaMercancia::countProgramacionByAgendaAndEstado($idagenda, $idestadoprogramacion);

            if ($numRegistros == $numRegistrosFin){
                $modelagenda = Agendaentregamercancia::findOne(['id' => $idagenda]);
                $modelagenda->idEstadoConteo = $idestadoconteo;
                $modelagenda->idEstadoLegalizacion = 3;

                $modelagenda->save();
            }

            Yii::$app->session->setFlash( 'success', $mensaje);
        } else {
            Yii::$app->session->setFlash( 'success', 'No se realizó ninguna actualización.');
        }

        if ($origen == 'ordencompra'){
            return $this->redirect(['/programacion/programacionentregamercancia/indexconteoagenda']);    
        }

        return $this->redirect(['/programacion/programacionentregamercancia/indexconteoprogramacion', 'idagenda' => $idagenda]);
    }

        /**
     * Lists all Agendaentregamercancia models.
     *
     * @return string
     */
    public function actionIndexlegalizacion()
    {
        $idsEncontrados = [];

        $menu = 'legalizacion';
        $lista_codigo = [1,2];

        foreach ($lista_codigo as $codigo) {
            // Buscar el modelo Estado por el código

            $estado = Estadolegalizacion::findOne(['codigo' => $codigo]);
            
            // Si se encuentra el estado, se agrega su ID al array
            if ($estado !== null) {
                $idsEncontrados[] = $estado->id;
            }
        }

        $searchModel = new AgendaentregamercanciaSearch();
        $dataProvider = $searchModel->searchxEstado($this->request->queryParams, $idsEncontrados, $menu);

        return $this->render('index_legalizacion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            //'modelagenda' => $modelagenda,
        ]);
    }

    public function actionViewlegalizaconteo($idagenda)
    {
        $modelagenda = Agendaentregamercancia::findOne(['id' => $idagenda]);

        $item = null;
        $idprogramacion = null;
        $iduserconteo = null;

        $searchModel = new ConteoentregamercanciaSearch();
        $dataProviderBD = $searchModel->search($this->request->queryParams, $idprogramacion, $item, $idagenda, $iduserconteo);

        $idordencompra = null;
        $idcategoria = null;
        $dataProvider = Conteoentregamercancia::generarDataConteoCurvas ($idagenda, $idordencompra, $idcategoria, $iduserconteo);

        // Agrupar los datos por bodega
        $dataByItem = [];
        foreach ($dataProvider as $model) {
            $item = $model['item'];
            if (!isset($dataByItem[$item])) {
                $dataByItem[$item] = [];
            }
            $dataByItem[$item][] = $model;
        }

        return $this->render('view_legalizacion_conteo', [
            'dataProvider' => $dataProvider,
            'dataByItem' => $dataByItem,
            'dataProviderBD' => $dataProviderBD,
            'modelagenda' => $modelagenda,
        ]);
    }

    public function actionGenerarexcelconteocurvas ($idagenda){
        
        $filename = Conteoentregamercancia::generarExcelConteoCurvas ($idagenda);
    }

    public function actionLegalizarconteo ($idagenda){

        $model = new LegalizaConteoForm ();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if ($model->validate()){

                    $modelestado = Estadolegalizacion::findOne(['codigo' => 2]);

                    $modelagenda = Agendaentregamercancia::findOne(['id' => $idagenda]);
                    $modelagenda->idEstadoConteo = $modelestado->id;
                    $modelagenda->idEstadoLegalizacion = 2;
                    $modelagenda->numeroFactura = $model->numeroFactura;
                    $modelagenda->observacionLegalizacion = $model->observacion;
                    $modelagenda->idUserLegalizacion = Yii::$app->user->identity->id;

                    $respuesta = $modelagenda->save();
                    
                    if ($respuesta){
                        Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
                    }else{
                        Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
                    }
                }

                return $this->redirect(['indexlegalizacion']);
            }
        } 

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('create_legaliza_conteo', [
                'model' => $model,
            ]);
        }  
    }

    public function actionHabilitarconteo ($idagenda){
        $codigo = 1;    
        $modelestado = Estadoconteo::findOne(['codigo' => $codigo]);

        $modelagenda = Agendaentregamercancia::findOne(['id' => $idagenda]);
        $modelagenda->idEstadoConteo = $modelestado->id;
        $modelagenda->idEstadoLegalizacion = 3;
        $modelagenda->save();

        Yii::$app->session->setFlash( 'success', 'Registro Actualizado');

        return $this->redirect(['indexlegalizacion']);
    }

    /**
     * Finds the Conteoentregamercancia model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Conteoentregamercancia the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Conteoentregamercancia::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
