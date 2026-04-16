<?php

namespace frontend\modules\programacion\controllers;

use frontend\models\Conectoresdinamicos;
use frontend\models\DataDocumentoEntrada;
use frontend\models\Facturaentregamercancia;
use frontend\models\Ordendecompra;
use frontend\models\Transferenciaerp;
use Yii;
use frontend\models\Conteoentregamercancia;
use frontend\models\search\ConteoentregamercanciaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use yii\web\UploadedFile;

use frontend\models\Programacionentregamercancia;
use frontend\models\Agendaentregamercancia;
use frontend\models\search\OrdendecompradetalleSearch;
use frontend\models\search\AgendaentregamercanciaSearch;
use frontend\models\search\TransferenciaordencompraexcelSearch;
use frontend\models\Ordendecompradetalle;
use frontend\models\Userconteo;
use frontend\models\Estadoprogramacion;
use frontend\models\Estadoconteo;
use frontend\models\Estadolegalizacion;
use frontend\models\LegalizaConteoForm;
use frontend\models\Calificacionproveedor;
use frontend\models\FileAgendaInput;
use frontend\models\Logborradoconteo;
use frontend\models\search\LogborradoconteoSearch;

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
                        'deleteconteos' => ['POST'],
                        'reducirconteositem' => ['POST'],
                        'reducirconteosfila' => ['POST'],
                        'reducirconteostalla' => ['POST'],
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

    public function actionIndexagenda($idagenda=null, $idfactura=null)
    {

        /*$modelprogramacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);*/
        $iduserconteo = null;
        $item = null;
        $idprogramacion = null;

        $modelfactura = Facturaentregamercancia::findOne(['id' => $idfactura]);

        if ($idagenda == null){
            $idagenda = $modelfactura->idAgendaEntregaMercancia;
        }

        $modelagenda = Agendaentregamercancia::findOne(['id' => $idagenda]);

        $searchModel = new ConteoentregamercanciaSearch();
        $dataProviderBD = $searchModel->search($this->request->queryParams, $idprogramacion, $item, $idagenda, $iduserconteo, $idfactura);

        $idordencompra = null;
        $idcategoria = null;
        $dataProvider = Conteoentregamercancia::generarDataConteoCurvas ($idagenda, $idordencompra, $idcategoria, $iduserconteo, $idprogramacion, $idfactura);

        // Agrupar los datos por bodega
        $dataByItem = [];
        foreach ($dataProvider as $model) {
            $item = $model['item'];
            if (!isset($dataByItem[$item])) {
                $dataByItem[$item] = [];
            }
            $dataByItem[$item][] = $model;
        }

        //var_dump($dataProvider); die("hola");

        return $this->render('index_detalleoc_agenda', [
            'dataProvider' => $dataProvider,
            'dataByItem' => $dataByItem,
            'dataProviderBD' => $dataProviderBD,
            'modelagenda' => $modelagenda,
            'modelfactura' => $modelfactura
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

    public function actionActualizardocumentoentrada ($idfactura){

        $factura = Facturaentregamercancia::findOne(['id' => $idfactura]);
        $idagenda = $factura->idAgendaEntregaMercancia;

        $agenda = Agendaentregamercancia::findOne(['id' => $idagenda]);

        $ordencompra = Ordendecompra::findOne(['id' => $agenda->idOrdenCompra]);

        $model = new DataDocumentoEntrada();

        /*$model->fechaDocumento = $ordencompra->fechaDocumentoEntrada;
        $model->idTipoDocumento = $ordencompra->idTipoDocumentoEntrada;
        $model->consignacion = $ordencompra->consignacion;
        $model->consecutivo = $ordencompra->consecutivoDocumentoEntrada;
        $model->idCO = $ordencompra->idCODocumentoEntrada;*/

        $model->fechaDocumento = $factura->fechaDocumentoEntrada;
        $model->idTipoDocumento = $factura->idTipoDocumentoEntrada;
        $model->consignacion = $factura->consignacion;
        $model->consecutivo = $factura->consecutivoDocumentoEntrada;
        $model->idCO = $factura->idCODocumentoEntrada;

        if($model->consignacion == null){
            $model->consignacion = 1;
        }

        if($model->consecutivo == null){
            $model->consecutivo = 1;
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                /*$ordencompra->fechaDocumentoEntrada = $model->fechaDocumento;
                $ordencompra->idTipoDocumentoEntrada = $model->idTipoDocumento;
                $ordencompra->consignacion = $model->consignacion;
                $ordencompra->consecutivoDocumentoEntrada = $model->consecutivo;
                $ordencompra->idCODocumentoEntrada = $model->idCO;*/

                $factura->fechaDocumentoEntrada = $model->fechaDocumento;
                $factura->idTipoDocumentoEntrada = $model->idTipoDocumento;
                $factura->consignacion = $model->consignacion;
                $factura->consecutivoDocumentoEntrada = $model->consecutivo;
                $factura->idCODocumentoEntrada = $model->idCO;

                $factura->save();

                //$ordencompra->save();

                return $this->redirect(['/programacion/facturaentregamercancia/indexlegalizaconteo']);
            }
        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('create_documentoentrada', [
                'model' => $model,
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
        $programacion   = Programacionentregamercancia::findOne(['id' => $idprogramacion]);

        if ($this->estaLegalizado($programacion)) {
            Yii::$app->session->setFlash('error', 'No se puede eliminar conteos de una agenda ya legalizada.');
            return $this->redirect(['indexprogramacion', 'idprogramacion' => $idprogramacion]);
        }

        $it        = $model->getItem()->one();
        $itemCode  = $it ? $it->item : null;
        $itemColor = $it && $it->color ? $it->color->nombre : null;
        $itemTalla = $it && $it->talla ? trim($it->talla->nombre) : null;

        Logborradoconteo::registrar(
            $programacion,
            'ELIMINAR_FILA',
            $model->unidadesConteo,
            $model->unidadesConteo,
            $itemCode,
            $itemColor,
            $itemTalla
        );

        $model->delete();

        return $this->redirect(['index', 'idprogramacion' => $idprogramacion]);
    }

    public function actionDeleteconteos($idprogramacion)
    {
        $programacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);

        if ($this->estaLegalizado($programacion)) {
            Yii::$app->session->setFlash('error', 'No se puede eliminar conteos de una agenda ya legalizada.');
            return $this->redirect(['indexprogramacion', 'idprogramacion' => $idprogramacion]);
        }

        $totalAntes = Conteoentregamercancia::find()
            ->where(['idProgramacionEntregaMercancia' => $idprogramacion])
            ->sum('unidadesConteo') ?? 0;

        Logborradoconteo::registrar(
            $programacion,
            'BORRAR_TODOS',
            $totalAntes,
            $totalAntes
        );

        Conteoentregamercancia::updateAll(
            ['unidadesConteo' => 0],
            ['idProgramacionEntregaMercancia' => $idprogramacion]
        );

        return $this->redirect(['indexprogramacion', 'idprogramacion' => $idprogramacion]);
    }

    public function actionReducirconteosfila($idprogramacion, $item, $color, $cantidad)
    {
        $cantidad     = (int) $cantidad;
        $programacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);

        if ($cantidad <= 0) {
            return $this->redirect(['indexprogramacion', 'idprogramacion' => $idprogramacion]);
        }

        if ($this->estaLegalizado($programacion)) {
            Yii::$app->session->setFlash('error', 'No se puede modificar conteos de una agenda ya legalizada.');
            return $this->redirect(['indexprogramacion', 'idprogramacion' => $idprogramacion]);
        }

        $models = Conteoentregamercancia::find()
            ->alias('cem')
            ->join('INNER JOIN', 'item it', 'cem.idItem = it.id')
            ->join('INNER JOIN', 'color col', 'it.idColor = col.id')
            ->where(['cem.idProgramacionEntregaMercancia' => $idprogramacion])
            ->andWhere(['cem.item' => $item])
            ->andWhere(['col.codigo' => $color])
            ->orderBy(['cem.id' => SORT_DESC])
            ->all();

        $totalAntes = array_sum(array_column(array_map(fn($m) => ['u' => $m->unidadesConteo], $models), 'u'));

        $restante = $cantidad;
        foreach ($models as $model) {
            if ($restante <= 0) break;
            if ($model->unidadesConteo <= $restante) {
                $restante -= $model->unidadesConteo;
                $model->unidadesConteo = 0;
                $model->save(false);
            } else {
                $model->unidadesConteo -= $restante;
                $model->save(false);
                $restante = 0;
            }
        }

        Logborradoconteo::registrar($programacion, 'REDUCIR_FILA', $totalAntes, $cantidad, $item, $color);

        return $this->redirect(['indexprogramacion', 'idprogramacion' => $idprogramacion]);
    }

    public function actionReducirconteostalla($idprogramacion, $item, $color, $talla, $cantidad)
    {
        $cantidad     = (int) $cantidad;
        $programacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);

        if ($cantidad <= 0) {
            return $this->redirect(['indexprogramacion', 'idprogramacion' => $idprogramacion]);
        }

        if ($this->estaLegalizado($programacion)) {
            Yii::$app->session->setFlash('error', 'No se puede modificar conteos de una agenda ya legalizada.');
            return $this->redirect(['indexprogramacion', 'idprogramacion' => $idprogramacion]);
        }

        $models = Conteoentregamercancia::find()
            ->alias('cem')
            ->join('INNER JOIN', 'item it', 'cem.idItem = it.id')
            ->join('INNER JOIN', 'color col', 'it.idColor = col.id')
            ->join('INNER JOIN', 'talla tal', 'it.idTalla = tal.id')
            ->where(['cem.idProgramacionEntregaMercancia' => $idprogramacion])
            ->andWhere(['cem.item' => $item])
            ->andWhere(['col.codigo' => $color])
            ->andWhere(['tal.codigo' => $talla])
            ->orderBy(['cem.id' => SORT_DESC])
            ->all();

        $totalAntes = array_sum(array_column(array_map(fn($m) => ['u' => $m->unidadesConteo], $models), 'u'));

        $restante = $cantidad;
        foreach ($models as $model) {
            if ($restante <= 0) break;
            if ($model->unidadesConteo <= $restante) {
                $restante -= $model->unidadesConteo;
                $model->unidadesConteo = 0;
                $model->save(false);
            } else {
                $model->unidadesConteo -= $restante;
                $model->save(false);
                $restante = 0;
            }
        }

        Logborradoconteo::registrar($programacion, 'REDUCIR_TALLA', $totalAntes, $cantidad, $item, $color, $talla);

        return $this->redirect(['indexprogramacion', 'idprogramacion' => $idprogramacion]);
    }

    public function actionReducirconteositem($idprogramacion, $item, $cantidad)
    {
        $cantidad     = (int) $cantidad;
        $programacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);

        if ($cantidad <= 0) {
            return $this->redirect(['indexprogramacion', 'idprogramacion' => $idprogramacion]);
        }

        if ($this->estaLegalizado($programacion)) {
            Yii::$app->session->setFlash('error', 'No se puede modificar conteos de una agenda ya legalizada.');
            return $this->redirect(['indexprogramacion', 'idprogramacion' => $idprogramacion]);
        }

        $models = Conteoentregamercancia::find()
            ->where(['idProgramacionEntregaMercancia' => $idprogramacion, 'item' => $item])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        $totalAntes = array_sum(array_column(array_map(fn($m) => ['u' => $m->unidadesConteo], $models), 'u'));

        $restante = $cantidad;
        foreach ($models as $model) {
            if ($restante <= 0) break;
            if ($model->unidadesConteo <= $restante) {
                $restante -= $model->unidadesConteo;
                $model->unidadesConteo = 0;
                $model->save(false);
            } else {
                $model->unidadesConteo -= $restante;
                $model->save(false);
                $restante = 0;
            }
        }

        Logborradoconteo::registrar($programacion, 'REDUCIR_ITEM', $totalAntes, $cantidad, $item);

        return $this->redirect(['indexprogramacion', 'idprogramacion' => $idprogramacion]);
    }

    public function actionFinalizarconteo($id, $origen, $idfactura)
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
                [   'idFacturaEntregaMercancia' => $idfactura]);
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

        //return $this->redirect(['/programacion/programacionentregamercancia/indexconteoprogramacion', 'idagenda' => $idagenda]);
        return $this->redirect(['/programacion/facturaentregamercancia/indexconteoprogramacion', 'idfactura' => $idfactura]);
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

    public function actionViewlegalizaconteo($idagenda, $idfactura = null)
    {
        $modelfactura = Facturaentregamercancia::findOne(['id' => $idfactura]);
        if ($modelfactura){
            $idagenda = $modelfactura->idAgendaEntregaMercancia;
            $modelagenda = Agendaentregamercancia::findOne(['id' => $modelfactura->idAgendaEntregaMercancia]);
        }else{
            $modelagenda = Agendaentregamercancia::findOne(['id' => $idagenda]);
        }

        $item = null;
        $idprogramacion = null;
        $iduserconteo = null;

        $searchModel = new ConteoentregamercanciaSearch();
        $dataProviderBD = $searchModel->searchSIESA($idagenda, $idfactura);

        $idordencompra = null;
        $idcategoria = null;

        if ($idfactura){
            $idagenda = null;
        }
        
        $dataProvider = Conteoentregamercancia::generarDataConteoCurvas ($idagenda, $idordencompra, $idcategoria, $iduserconteo, $idprogramacion, $idfactura);

        // Agrupar los datos por bodega
        $dataByItem = [];
        foreach ($dataProvider as $model) {
            $item = $model['item'];
            if (!isset($dataByItem[$item])) {
                $dataByItem[$item] = [];
            }
            $dataByItem[$item][] = $model;
        }

        //var_dump($modelfactura);die("hola");

        return $this->render('view_legalizacion_conteo', [
            'dataProvider' => $dataProvider,
            'dataByItem' => $dataByItem,
            'dataProviderBD' => $dataProviderBD,
            'modelagenda' => $modelagenda,
            'modelfactura' => $modelfactura
        ]);
    }

    public function actionGenerarexcelconteocurvas ($idfactura){
        
        $filename = Conteoentregamercancia::generarExcelConteoCurvas ($idfactura);

        //$rutaGuardado = Transferencia::generarArchivotransferencia($factura);

        Yii::$app->response->sendFile($filename)->send();
        
        return $this->redirect(['indexlegalizacion']);

        //$filename = Conteoentregamercancia::generarExcelConteoCurvas ($idagenda);
    }

    public function actionLegalizarconteo ($idfactura){

        $model = new LegalizaConteoForm ();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if ($model->validate()){

                    $modelfactura = Facturaentregamercancia::findOne(['id' => $idfactura]);
                    $idagenda = $modelfactura->idAgendaEntregaMercancia;

                    $modelfactura->numeroFacturaLegaliza = $model->numeroFactura;
                    if ($model->observacion){
                        $modelfactura->observaciones = $modelfactura->observaciones . ' - ' . $model->observacion;
                    }

                    $modelfactura->save();

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

                        // Actualizar unidades entregadas en las calificaciones de esta OC
                        $idOc = (int)Yii::$app->db->createCommand("
                            SELECT idOrdenCompra FROM agendaentregamercancia WHERE id = :id
                        ", [':id' => $idagenda])->queryScalar();

                        if ($idOc) {
                            // Totales reales contados por subcategoría
                            $totales = Yii::$app->db->createCommand("
                                SELECT sub.nombre AS subcategoria,
                                       SUM(d.cantidadEntrada) AS total_entregadas
                                FROM ordendecompradetalle d
                                INNER JOIN subcategoria sub ON sub.id = d.idSubcategoria
                                WHERE d.idOrdenCompra = :id
                                GROUP BY sub.nombre
                            ", [':id' => $idOc])->queryAll();

                            foreach ($totales as $total) {
                                $calif = Calificacionproveedor::find()
                                    ->where([
                                        'id_ordendecompra' => $idOc,
                                        'subcategoria'     => $total['subcategoria'],
                                    ])
                                    ->orderBy(['id' => SORT_DESC])
                                    ->one();

                                if ($calif) {
                                    $calif->unidades_entregadas = (int)$total['total_entregadas'];
                                    $calif->calcularPuntajes();
                                    $calif->save(false); // false = sin re-validar
                                }
                            }
                        }
                    }else{
                        Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
                    }
                }

                return $this->redirect(['/programacion/facturaentregamercancia/indexlegalizaconteo']);
            }
        } 

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('create_legaliza_conteo', [
                'model' => $model,
            ]);
        }  
    }

    public function actionHabilitarconteo ($idfactura){
        $codigo = 1;    
        $modelestado = Estadoconteo::findOne(['codigo' => $codigo]);

        $modelfactura = Facturaentregamercancia::findOne(['id' => $idfactura]);
        $idagenda = $modelfactura->idAgendaEntregaMercancia;

        $modelagenda = Agendaentregamercancia::findOne(['id' => $idagenda]);
        $modelagenda->idEstadoConteo = $modelestado->id;
        $modelagenda->idEstadoLegalizacion = 3;
        $modelagenda->save();

        Yii::$app->session->setFlash( 'success', 'Registro Actualizado');

        return $this->redirect(['/programacion/facturaentregamercancia/indexlegalizaconteo']);
    }

    public function actionExtraerdataocsiesa (){
        $model = new FileAgendaInput(); 

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }         

        if ($model->load(Yii::$app->request->post())) {

            $userId = Yii::$app->user->id;
            $model->archivo = UploadedFile::getInstance($model, 'archivo');

            $respuesta = Agendaentregamercancia::uploadocsiesa($model->archivo);

            if ($respuesta) {

                Yii::$app->session->setFlash('success', 'El Archivo se ha cargado correctamente. ');
                return $this->redirect(['indexlegalizacion']);
            }else{
                $errorString = ProcedimientosGenerales::erroresModelo ($model->getErrors());
                Yii::$app->session->setFlash('error', 'Ocurrió un error al cargar los archivos: ' . $errorString);
            }

            return $this->redirect(['indexlegalizacion']);

        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('uploaddataocsiesa', [
                'model' => $model,
            ]);
        }  
    }

    public function actionTransferencia ($idfactura){

        $modelfactura = Facturaentregamercancia::findOne(['id' => $idfactura]);
        $idagenda = $modelfactura->idAgendaEntregaMercancia;

        if (!$modelfactura->consecutivoDocumentoEntrada){
            Yii::$app->session->setFlash('error', 'Falta Actualizar Documentos Entrada SIESA');
            return $this->redirect(['/programacion/facturaentregamercancia/indexlegalizaconteo']);
        }

        if (!$modelfactura->numeroFacturaLegaliza){
            Yii::$app->session->setFlash('error', 'Falta Actualizar Número Factura de Legalización');
            return $this->redirect(['/programacion/facturaentregamercancia/indexlegalizaconteo']);
        }

        $searchModel = new ConteoentregamercanciaSearch();
        $dataProviderBD = $searchModel->searchSIESA($idagenda, $idfactura);

        // var_dump($dataProviderBD); die("pare 1");
        $idtransferenciaerp = Conteoentregamercancia::crearRegistroTransferencia($idagenda, $idfactura, $dataProviderBD);

        return $this->redirect(['viewtransferenciaocerp', 'id' => $idtransferenciaerp]);
    }

    public function actionViewtransferenciaocerp($id)
    {
        $searchModel = new TransferenciaordencompraexcelSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $id);

        return $this->render('index_transferenciaocerp', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'idtransferenciaerp' => $id
        ]);
    }


    /**
     * Investigación forense OC 2CA-6427
     */
    public function actionInvestigacion6427()
    {
        $db = Yii::$app->db;

        // 1. OC y agenda
        $oc = $db->createCommand("
            SELECT oc.id AS idOC, oc.consecutivo, td.codigo AS tipoDoc, co.codigo AS co,
                   a.id AS idAgenda, ec.nombre AS estadoConteo, el.nombre AS estadoLegaliz,
                   ul.username AS userLegalizo,
                   CONVERT(varchar,a.updated_at,120) AS agendaUltimaMod,
                   uu.username AS agendaModPor
            FROM ordencompra oc
            INNER JOIN tipodocumento td ON oc.idTipoDocumento = td.id
            INNER JOIN centrooperacion co ON oc.idCO = co.id
            INNER JOIN agendaentregamercancia a ON a.idOrdenCompra = oc.id
            LEFT JOIN estadoconteo ec ON a.idEstadoConteo = ec.id
            LEFT JOIN estadolegalizacion el ON a.idEstadoLegalizacion = el.id
            LEFT JOIN [user] ul ON a.idUserLegalizacion = ul.id
            LEFT JOIN [user] uu ON a.updated_by = uu.id
            WHERE oc.consecutivo = '6427' AND td.codigo = '2CA'
        ")->queryAll();

        $idAgenda = $oc[0]['idAgenda'] ?? null;
        $programaciones = $idAgenda ? $db->createCommand("
            SELECT p.id, p.item, ep.nombre AS estadoProg,
                   p.idFacturaEntregaMercancia AS idFactura,
                   CONVERT(varchar,p.created_at,120) AS created_at, uc2.username AS creadoPor,
                   CONVERT(varchar,p.updated_at,120) AS updated_at, uu.username AS actualizadoPor
            FROM programacionentregamercancia p
            LEFT JOIN estadoprogramacion ep ON p.idEstado = ep.id
            LEFT JOIN userconteo uc ON p.idUserConteo = uc.id
            LEFT JOIN [user] uc2 ON p.created_by = uc2.id
            LEFT JOIN [user] uu ON p.updated_by = uu.id
            WHERE p.idAgendaEntregaMercancia = $idAgenda ORDER BY p.item
        ")->queryAll() : [];

        $idsProgStr  = implode(',', array_column($programaciones, 'id') ?: [0]);
        $facturasStr = implode(',', array_unique(array_filter(array_column($programaciones, 'idFactura'))) ?: [0]);

        $conteos = $db->createCommand("
            SELECT c.id, c.item, c.unidadesConteo, c.unidadesAsignadas,
                   CONVERT(varchar,c.created_at,120) AS created_at, uc.username AS creadoPor,
                   CONVERT(varchar,c.updated_at,120) AS updated_at, uu.username AS actualizadoPor
            FROM conteoentregamercancia c
            LEFT JOIN [user] uc ON c.created_by = uc.id
            LEFT JOIN [user] uu ON c.updated_by = uu.id
            WHERE c.idProgramacionEntregaMercancia IN ($idsProgStr)
            ORDER BY c.item, c.id
        ")->queryAll();

        $actividad7abril = $db->createCommand("
            SELECT c.id, c.item, c.unidadesConteo,
                   CONVERT(varchar,c.created_at,120) AS created_at, uc.username AS creadoPor,
                   CONVERT(varchar,c.updated_at,120) AS updated_at, uu.username AS actualizadoPor
            FROM conteoentregamercancia c
            LEFT JOIN [user] uc ON c.created_by = uc.id
            LEFT JOIN [user] uu ON c.updated_by = uu.id
            WHERE c.idProgramacionEntregaMercancia IN ($idsProgStr)
              AND (CAST(c.updated_at AS DATE) = '2026-04-07' OR CAST(c.created_at AS DATE) = '2026-04-07')
            ORDER BY c.updated_at
        ")->queryAll();

        $escaneos = $db->createCommand("
            SELECT cb.id, cb.idConteoFactura, cb.codigoBarras, cb.unidades, cb.isMobile AS desdeCelular,
                   CONVERT(varchar,cb.created_at,120) AS created_at, uc.username AS creadoPor,
                   CONVERT(varchar,cb.updated_at,120) AS updated_at, uu.username AS actualizadoPor
            FROM conteobylecturacodigo cb
            LEFT JOIN [user] uc ON cb.created_by = uc.id
            LEFT JOIN [user] uu ON cb.updated_by = uu.id
            WHERE cb.idConteoFactura IN ($facturasStr)
            ORDER BY cb.created_at DESC
        ")->queryAll();

        $escaneos7abril = $db->createCommand("
            SELECT cb.id, cb.codigoBarras, cb.unidades,
                   CONVERT(varchar,cb.created_at,120) AS created_at, uc.username AS creadoPor,
                   CONVERT(varchar,cb.updated_at,120) AS updated_at, uu.username AS actualizadoPor
            FROM conteobylecturacodigo cb
            LEFT JOIN [user] uc ON cb.created_by = uc.id
            LEFT JOIN [user] uu ON cb.updated_by = uu.id
            WHERE cb.idConteoFactura IN ($facturasStr)
              AND (CAST(cb.created_at AS DATE) = '2026-04-07' OR CAST(cb.updated_at AS DATE) = '2026-04-07')
            ORDER BY cb.created_at
        ")->queryAll();

        $resumenFechas = $db->createCommand("
            SELECT MIN(CONVERT(varchar,c.created_at,120)) AS primerConteo,
                   MAX(CONVERT(varchar,c.created_at,120)) AS ultimoConteo,
                   MIN(CONVERT(varchar,c.updated_at,120)) AS primeraModif,
                   MAX(CONVERT(varchar,c.updated_at,120)) AS ultimaModif,
                   COUNT(*) AS totalFilas, SUM(c.unidadesConteo) AS totalUnidadesActual
            FROM conteoentregamercancia c
            WHERE c.idProgramacionEntregaMercancia IN ($idsProgStr)
        ")->queryAll();

        $facturas = $idAgenda ? $db->createCommand("
            SELECT f.id, f.numeroFacturaLegaliza, f.consecutivoDocumentoEntrada,
                   CONVERT(varchar,f.updated_at,120) AS updated_at, uu.username AS actualizadoPor
            FROM facturaentregamercancia f
            LEFT JOIN [user] uu ON f.updated_by = uu.id
            WHERE f.idAgendaEntregaMercancia = $idAgenda
        ")->queryAll() : [];

        return $this->render('investigacion6427', compact(
            'oc','programaciones','conteos','actividad7abril',
            'escaneos','escaneos7abril','resumenFechas','facturas','facturasStr'
        ));
    }

    /**
     * Index global de borrados de conteo — todas las OC, con filtros.
     */
    public function actionIndexborradosglobal()
    {
        $searchModel  = new LogborradoconteoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index_borrados_global', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Historial de eliminaciones de conteos para una programación.
     */
    public function actionHistorialborrados($idprogramacion)
    {
        $modelprogramacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);

        $logs = Logborradoconteo::find()
            ->where(['idProgramacionEntregaMercancia' => $idprogramacion])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('historial_borrados', [
            'logs'              => $logs,
            'modelprogramacion' => $modelprogramacion,
        ]);
    }

    /**
     * Devuelve true si la agenda asociada a la programación ya fue legalizada (codigo 2).
     */
    protected function estaLegalizado($programacion)
    {
        if (!$programacion) return false;
        $agenda = Agendaentregamercancia::findOne(['id' => $programacion->idAgendaEntregaMercancia]);
        if (!$agenda) return false;
        // idEstadoLegalizacion = 2 → legalizado
        return (int) $agenda->idEstadoLegalizacion === 2;
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
