<?php

namespace frontend\modules\programacion\controllers;

use Yii;
use frontend\models\Programacionentregamercancia;
use frontend\models\search\ProgramacionentregamercanciaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\db\Expression;

use frontend\models\search\AgendaentregamercanciaSearch;
use frontend\models\Estadoagenda;
use frontend\models\Estadoconteo;
use frontend\models\Estadoprogramacion;
use frontend\models\Agendaentregamercancia;
use frontend\models\Conteoentregamercancia;
use frontend\models\Ordendecompradetalle;
use frontend\models\Facturaentregamercancia;
use common\models\OrdenesCompraWs;

/**
 * ProgramacionentregamercanciaController implements the CRUD actions for Programacionentregamercancia model.
 */
class ProgramacionentregamercanciaController extends Controller
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

    public function actionIndexrecepcion($menu){

        // Colocamos los códigos de estado que NO queremos visualizar
        // No Puede ver lo que no se haya hecho nada. Codigo = 0
        $idsEncontrados = []; 
        $lista_codigo = [0];    

        foreach ($lista_codigo as $codigo) {
            // Buscar el modelo Estado por el código
            $estado = Estadoagenda::findOne(['codigo' => $codigo]);
            
            // Si se encuentra el estado, se agrega su ID al array
            if ($estado !== null) {
                $idsEncontrados[] = $estado->id;
            }
        }

        $searchModel = new AgendaentregamercanciaSearch();
        $dataProvider = $searchModel->searchxEstado($this->request->queryParams, $idsEncontrados, $menu);

        return $this->render('index_recepcion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'menu' => $menu
        ]);

    }

    public function actionIndexconteoagenda (){

        $codigo = 1;
        $estado = Estadoconteo::findOne(['codigo' => $codigo]);

        $idagenda = null;
        $idestado = null;
        
        $searchModel = new AgendaentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idagenda, $idestado, $estado->id);

        return $this->render('index_conteo_agenda', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }

    /**
     * Lists all Agendaentregamercancia models.
     *
     * @return string
     */
    public function actionIndexagenda($menu)
    {
        $idsEncontrados = [];

        switch ($menu) {
            case 'recepcion': 
                $programa = 'index_recepcion';
                $lista_codigo = [];
                break;
            case 'programacion':
                $programa = 'index_agenda';
                $lista_codigo = [0,1]; // Contar todo lo que este sin programar o en conteo
                break;
        }

        foreach ($lista_codigo as $codigo) {
            // Buscar el modelo Estado por el código

            if ($menu == 'recepcion'){
                $estado = Estadoagenda::findOne(['codigo' => $codigo]);
            }else{
                $estado = Estadoconteo::findOne(['codigo' => $codigo]);                
            }
            
            // Si se encuentra el estado, se agrega su ID al array
            if ($estado !== null) {
                $idsEncontrados[] = $estado->id;
            }
        }

        //var_dump($idsEncontrados);die("hola");

        $searchModel = new AgendaentregamercanciaSearch();
        $dataProvider = $searchModel->searchxEstado($this->request->queryParams, $idsEncontrados, $menu);

        return $this->render($programa, [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'menu' => $menu
        ]);
    }

    public function actionIndexprogramacion($menu)
    {
        // Colocamos los códigos de estado que este Cumplidas
        // Solo se Puede ver lo que se haya marcado como cumplido. Codigo = 2
        $idsEncontrados = []; 
        $lista_codigo = [2];    

        foreach ($lista_codigo as $codigo) {
            // Buscar el modelo Estado por el código
            $estado = Estadoagenda::findOne(['codigo' => $codigo]);
            
            // Si se encuentra el estado, se agrega su ID al array
            if ($estado !== null) {
                $idsEncontrados[] = $estado->id;
            }
        }

        //var_dump($idsEncontrados);die("hola");

        $searchModel = new AgendaentregamercanciaSearch();
        $dataProvider = $searchModel->searchxEstado($this->request->queryParams, $idsEncontrados, $menu);

        return $this->render('index_programacion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'menu' => $menu
        ]);
    }

    /**
     * Lists all Programacionentregamercancia models.
     *
     * @return string
     */
    public function actionIndex($id)
    {
        $modelagendaentrega = Agendaentregamercancia::findOne(['id' => $id]);

        $searchModel = new ProgramacionentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelagendaentrega' => $modelagendaentrega
        ]);
    }

    /**
     * Lists all Programacionentregamercancia models.
     *
     * @return string
     */
    public function actionIndexconteoprogramacion($idagenda)
    {
        $id = null;
        $idestadoconteo = 2;
        $idestadoprogramacion = 1;

        $searchModel = new ProgramacionentregamercanciaSearch();
        $dataProvider = $searchModel->searchxagenda($this->request->queryParams, $id, $idestadoconteo, $idestadoprogramacion, $idagenda);

        return $this->render('index_conteo_programacion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'idagenda' => $idagenda
        ]);
    }

    public function actionIndexfactura($idfactura)
    {
        $modelfactura = Facturaentregamercancia::findOne(['id' => $idfactura]);
        $idagenda = $modelfactura->idAgendaEntregaMercancia;

        $modelagendaentrega = Agendaentregamercancia::findOne(['id' => $idagenda]);

        $idordencompra = $modelagendaentrega->idOrdenCompra;
        $idcategoria = $modelagendaentrega->idCategoria;

        Programacionentregamercancia::registrarItemsOC ($idagenda, $idordencompra, $idcategoria, $idfactura);

        $searchModel = new ProgramacionentregamercanciaSearch();
        $dataProvider = $searchModel->searchxFactura($this->request->queryParams, $idfactura);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelagendaentrega' => $modelagendaentrega,
            'modelfactura' => $modelfactura
        ]);
    }

    public function actionAssignuser($idagenda)
    {
        $modelagendaentrega = Agendaentregamercancia::findOne(['id' => $idagenda]);
        $idordencompra = $modelagendaentrega->idOrdenCompra;
        $idcategoria = $modelagendaentrega->idCategoria;

        Programacionentregamercancia::registrarItemsOC ($idagenda, $idordencompra, $idcategoria);

        $searchModel = new ProgramacionentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idagenda);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelagendaentrega' => $modelagendaentrega
        ]);
    }

    /**
     * Displays a single Programacionentregamercancia model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView()
    {
        $searchModel = new ProgramacionentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index_programacion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }

    public function actionAssignoneuser($idfactura){

        $modelfactura = Facturaentregamercancia::findOne(['id' => $idfactura]);
        $idagenda = $modelfactura->idAgendaEntregaMercancia;

        $model = new Programacionentregamercancia();

        $model->idAgendaEntregaMercancia = $idagenda;
        $model->idFacturaEntregaMercancia = $idfactura;
        $model->unidadesAsignadas = 0;
        $model->item = 9999;
        $model->puedeModificarEntrada = 0;
        $model->unidadxPaquete = 1;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if ($model->validate()){
                    $iduser = $model->idUserConteo;
                    $idempleadologistica = $model->userConteo->idEmpleadoLogistica;

                    $numeroRegistros = Programacionentregamercancia::asignarUserConteo ($idagenda, 
                                                                                        $iduser,
                                                                                        $idempleadologistica,
                                                                                        $idfactura
                                                                                    );

                    Yii::$app->session->setFlash( 'success', ' Usuario Fue Asignado a Contar ' . $numeroRegistros );
                }else{
                    Yii::$app->session->setFlash( 'error', 'Error Asignando Un Solo Usuario');
                    var_dump($model->getErrors()); die("hola");
                }

                //return $this->redirect(['index', 'id' => $model->idAgendaEntregaMercancia]);
                return $this->redirect(['indexfactura', 'idfactura' => $model->idFacturaEntregaMercancia]);
            }
        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('create', [
                'model' => $model,
                'oneuser' => 1
            ]);
        }
    }

    /**
     * Creates a new Programacionentregamercancia model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($id)
    {
        $modelprogramacion = $this->findModel($id);

        $model = new Programacionentregamercancia();

        $model->idAgendaEntregaMercancia = $modelprogramacion->idAgendaEntregaMercancia;
        $model->idFacturaEntregaMercancia = $modelprogramacion->idFacturaEntregaMercancia;
        $model->item = $modelprogramacion->item;
        $model->idEstado = $modelprogramacion->idEstado;
        $model->referencia = $modelprogramacion->referencia;
        $model->descripcion = $modelprogramacion->descripcion;

        $idordencompra = $modelprogramacion->agendaEntregaMercancia->idOrdenCompra;

        $total = Programacionentregamercancia::totalUnidadesAsignadas ($model->idAgendaEntregaMercancia, $model->item);

        $totalunidadesoc = Ordendecompradetalle::totalCantidadPendientexItem ($idordencompra, $model->item);

        $model->unidadesAsignadas = $totalunidadesoc - $total;

        if ($model->unidadesAsignadas <= 0){
            Yii::$app->session->setFlash( 'error', 'Error Total Número de Unidades YA Estan Asignadas => OC: ' . $totalunidadesoc . ' - Programada: ' . $total);
            return $this->redirect(['index', 'id' => $model->idAgendaEntregaMercancia]);
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if ($model->validate()){

                    $model->idEmpleadoLogistica = $model->userConteo->idEmpleadoLogistica;
                    
                    if ($model->save()){
                        
                        $modelagendaentrega = Agendaentregamercancia::findOne(['id' => $modelprogramacion->idAgendaEntregaMercancia]);

                        $codigo = 1;
                        $modelestado = Estadoconteo::findOne(['codigo' => $codigo]);

                        $modelagendaentrega->idEstadoConteo = $modelestado->id;
                        $modelagendaentrega->save();

                        $modelestado = Estadoprogramacion::findOne(['codigo' => 0]);

                        $total = Programacionentregamercancia::totalUnidadesAsignadas ($model->idAgendaEntregaMercancia, $model->item);
                        $totalunidadesoc = Ordendecompradetalle::totalCantidadPendientexItem ($idordencompra, $model->item);
                        $unidades = $totalunidadesoc - $total;
                        if ($unidades > 0){
                            $modelnew  = new Programacionentregamercancia(); 
                            $modelnew->idAgendaEntregaMercancia = $model->idAgendaEntregaMercancia;
                            $modelnew->idFacturaEntregaMercancia = $model->idFacturaEntregaMercancia;
                            $modelnew->item = $model->item;
                            $modelnew->referencia = $model->referencia;
                            $modelnew->descripcion = $model->descripcion;
                            $modelnew->unidadesAsignadas = $unidades;
                            $modelnew->idEstado = $modelestado->id;
                            $modelnew->save();
                        }
                
                    }else{
                        $mensajerror = ProcedimientosGenerales::erroresModelo($model->getErrors());
                        Yii::$app->session->setFlash( 'error', $mensajerror);
                    }
                }else{
                    $mensajerror = ProcedimientosGenerales::erroresModelo($model->getErrors());
                    Yii::$app->session->setFlash( 'error', $mensajerror);
                }

                return $this->redirect(['index', 'id' => $model->idAgendaEntregaMercancia]);
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('create', [
                'model' => $model,
                'oneuser' => 0
            ]);
        }  
    }

    public function actionUpdate($id){

        $model = $this->findModel($id);
        $unidadesitem = $model->unidadesAsignadas;

        if ($model->idEstado == 4){
            Yii::$app->session->setFlash( 'error', 'Error Registro No puede Ser Modificado.');
            //return $this->redirect(['index', 'id' => $model->idAgendaEntregaMercancia]);
            return $this->redirect(['indexfactura', 'idfactura' => $model->idFacturaEntregaMercancia]);
        }

        $idordencompra = $model->agendaEntregaMercancia->idOrdenCompra;
        $total = Programacionentregamercancia::totalUnidadesAsignadas ($model->idAgendaEntregaMercancia, $model->item);
        $totalunidadesoc = Ordendecompradetalle::totalCantidadPendientexItem ($idordencompra, $model->item);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                $totalasignadas = $total - $unidadesitem + $model->unidadesAsignadas;

                $unidades = $totalunidadesoc - $totalasignadas;
                if ($unidades < 0){
                    //die($totalunidadesoc . ' - ' . $totalasignadas . ' - ' . $unidades);
                    Yii::$app->session->setFlash( 'error', 'Error Total Número de Unidades YA Estan Asignadas => OC: ' . $totalunidadesoc . ' - Programada: ' . $totalasignadas);
                    //return $this->redirect(['index', 'id' => $model->idAgendaEntregaMercancia]);
                    return $this->redirect(['indexfactura', 'idfactura' => $model->idFacturaEntregaMercancia]);
                }

                $modelestado = Estadoprogramacion::findOne(['codigo' => 1]);

                $model->idEmpleadoLogistica = $model->userConteo->idEmpleadoLogistica;
                $model->idEstado = $modelestado->id;

                if ($model->save()){
                    $modelagendaentrega = Agendaentregamercancia::findOne(['id' => $model->idAgendaEntregaMercancia]);

                    $codigo = 1;
                    $modelestado = Estadoconteo::findOne(['codigo' => $codigo]);

                    $modelagendaentrega->idEstadoConteo = $modelestado->id;
                    $modelagendaentrega->save();

                    $modelestado = Estadoprogramacion::findOne(['codigo' => 0]);

                    $total = Programacionentregamercancia::totalUnidadesAsignadas ($model->idAgendaEntregaMercancia, $model->item);
                    $totalunidadesoc = Ordendecompradetalle::totalCantidadPendientexItem ($idordencompra, $model->item);
                    $unidades = $totalunidadesoc - $total;
                    if ($unidades > 0){
                        $modelnew  = new Programacionentregamercancia(); 
                        $modelnew->idAgendaEntregaMercancia = $model->idAgendaEntregaMercancia;
                        $modelnew->idFacturaEntregaMercancia = $model->idFacturaEntregaMercancia;
                        $modelnew->item = $model->item;
                        $modelnew->referencia = $model->referencia;
                        $modelnew->descripcion = $model->descripcion;
                        $modelnew->unidadesAsignadas = $unidades;
                        $modelnew->idEstado = $modelestado->id;
                        $modelnew->save();
                    }
                }
                
                //return $this->redirect(['index', 'id' => $model->idAgendaEntregaMercancia]);
                return $this->redirect(['indexfactura', 'idfactura' => $model->idFacturaEntregaMercancia]);
            }
        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        } 

    }

    /**
     * Updates an existing Programacionentregamercancia model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionReceive($idagendaentrega)
    {
        $model = Agendaentregamercancia::findOne(['id' => $idagendaentrega]);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                
                $modelestado = Estadoagenda::findOne(['id' => $model->idEstado]);
                switch ($modelestado->codigo){
                    case 2:
                    case 3;
                        $model->motivoCumplimiento = $model->motivo;
                        $model->idUserCumplimiento = Yii::$app->user->identity->id;
                        $model->fechaCumplimiento = new Expression('GETDATE()');
                        break;
                    case 4:
                        $model->motivoDevolucion = $model->motivo;
                        $model->idUserDevolucion = Yii::$app->user->identity->id;
                        $model->fechaDevolucion = new Expression('GETDATE()');
                        break;
                }

                $model->save();

                return $this->redirect(['indexagenda', 'menu' => 'recepcion']);
            }
        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('receive', [
                'model' => $model,
            ]);
        }  
    }

    /**
     * Deletes an existing Programacionentregamercancia model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $idAgendaEntregaMercancia = $model->idAgendaEntregaMercancia;

        $model->delete();

        $modelagendaentrega = Agendaentregamercancia::findOne(['id' => $idAgendaEntregaMercancia ]);

        $numero = $modelagendaentrega->numeroPersonasProgramadas;
        if ($numero){
            $codigo = 1;
            if ($numero > 0){
                $codigo = 4;
            }
            $modelestado = Estadoagenda::findOne(['codigo' => $codigo]);

            $modelagendaentrega->idEstado = $modelestado->id;
            $modelagendaentrega->save();
        }


        return $this->redirect(['index', 'id' => $idAgendaEntregaMercancia]);
    }

    public function actionHabilitarconteo ($id){
        $model = $this->findModel($id);
        $iduserconteo = $model->idUserConteo;

        $cantidad = Programacionentregamercancia::find()
                                ->where(['idUserConteo' => $iduserconteo, 'idEstado' => 1])
                                ->count();

        if ($cantidad != 0){
            Yii::$app->session->setFlash( 'error', 'Error Usuario YA Esta Habilitado Para Conteo');
            return $this->redirect(['view']);
        }

        $codigo = 1;    
        $modelestado = Estadoconteo::findOne(['codigo' => $codigo]);

        $modelagenda = Agendaentregamercancia::findOne(['id' => $model->idAgendaEntregaMercancia]);
        $modelagenda->idEstadoConteo = $modelestado->id;
        $modelagenda->save();

        $model->idEstado = 1;
        $model->save();

        return $this->redirect(['view']);

    }

    public function actionHabilitarconteoprogramacion ($idprogramacion){

        $model = $this->findModel($idprogramacion);
        $model->idEstado = 1;
        $model->save();

        $codigo = 1;    
        $modelestado = Estadoconteo::findOne(['codigo' => $codigo]);

        /*$modelagenda = Agendaentregamercancia::findOne(['id' => $model->idAgendaEntregaMercancia]);
        $modelagenda->idEstadoConteo = $modelestado->id;
        $modelagenda->save();*/

        Yii::$app->session->setFlash( 'success', 'Conteo de Orden de Compra Actualizado Con Éxito');
        
        //return $this->redirect(['indexconteoprogramacion', 'idagenda' => $model->idAgendaEntregaMercancia]);
        return $this->redirect(['/programacion/facturaentregamercancia/indexconteoprogramacion', 'idfactura' => $model->idFacturaEntregaMercancia]);

    }

    public function actionConteoreferencia($id)
    {
        $model = Programacionentregamercancia::findOne(['id' => $id]);
        $iduserconteo = $model->idUserConteo;

        $modelestado = Estadoprogramacion::findOne(['codigo' => 2]);

        $cantidad = Programacionentregamercancia::find()
                                ->where(['idUserConteo' => $iduserconteo, 'idEstado' => $modelestado->id])
                                ->count();

        if ($cantidad != 0){
            Yii::$app->session->setFlash( 'error', 'Error Usuario YA Esta Habilitado Para Conteo');
            return $this->redirect(['view']);
        }

        return $this->redirect(['/programacion/conteoentregamercancia/index', 'idprogramacion' => $id]);
    }

    public function actionActualizadataordencompraws ($idagenda){

        $model = Agendaentregamercancia::findOne(['id' => $idagenda]);

        $modelordencompra = OrdenesCompraWs::sincronizarERPAgenda (
                                        $model->ordenCompra->cO->codigo, 
                                        $model->ordenCompra->tipoDocumento->codigo, 
                                        $model->ordenCompra->consecutivo);

        Yii::$app->session->setFlash( 'success', 'Orden de Compra Actualizada Con Éxito');
        
        return $this->redirect(['indexprogramacion', 'menu' => 'programacion']);

    }

    /**
     * Finds the Programacionentregamercancia model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Programacionentregamercancia the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Programacionentregamercancia::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
