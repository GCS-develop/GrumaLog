<?php

namespace frontend\modules\agenda\controllers;

use Yii;
use frontend\models\Agendapresupuestosubcategoria;
use frontend\models\search\AgendapresupuestosubcategoriaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\Agendapresupuesto;
use frontend\models\search\AgendapresupuestoSearch;

/**
 * AgendapresupuestosubcategoriaController implements the CRUD actions for Agendapresupuestosubcategoria model.
 */
class AgendapresupuestosubcategoriaController extends Controller
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

    public function actionIndexperiodo()
    {
        $searchModel = new AgendapresupuestoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index_periodo', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Agendapresupuestosubcategoria models.
     *
     * @return string
     */
    public function actionIndex($idagendapresupuesto, $menu = null)
    {
        $model = Agendapresupuesto::findOne(['id' => $idagendapresupuesto]);

        $searchModel = new AgendapresupuestosubcategoriaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idagendapresupuesto);

        $dataProviderDia = $searchModel->searchPivotCupoSubcategoriaDia($this->request->queryParams, $idagendapresupuesto);

        // Agrupar los datos por bodega
        $dataByCrossDocking = [];
        foreach ($dataProviderDia->getModels() as $modelagenda) {
            $crossdocking = $modelagenda['crossDocking'];
            if (!isset($dataByCrossDocking[$crossdocking])) {
                $dataByCrossDocking[$crossdocking] = [];
            }
            $dataByCrossDocking[$crossdocking][] = $modelagenda;
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dataProviderDia' => $dataProviderDia,
            'dataByCrossDocking' => $dataByCrossDocking,
            'model' => $model,
            'menu' => $menu,
        ]);
    }

    public function actionIndexupdate($idagendapresupuesto)
    {
        $model = Agendapresupuesto::findOne(['id' => $idagendapresupuesto]);

        $searchModel = new AgendapresupuestosubcategoriaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idagendapresupuesto);

        return $this->render('index_update', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }

    /**
     * Displays a single Agendapresupuestosubcategoria model.
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
     * Creates a new Agendapresupuestosubcategoria model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($idagendapresupuesto)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $modelAgenda = Agendapresupuesto::findOne(['id' => $idagendapresupuesto]);

            $model = new Agendapresupuestosubcategoria();

            $model->idAgendaPresupuesto = $idagendapresupuesto;
            $model->periodoAnio = $model->agendaPresupuesto->periodoAnio;
            $model->periodoMes = $model->agendaPresupuesto->periodoMes;
            $model->cantidad = 0;

            if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } 

            if ($this->request->isPost) {
                if ($model->load($this->request->post())) {
                    $id = null;
                    $model->categoria = $model->idCategoria->nombre;
                    $model->subcategoria = $model->idSubcategoria->nombre;
                    $model->crossDocking = $model->idCrossdocking->nombre;

                    //var_dump($model->categoria . ' - ' . $model->subcategoria . ' - ' . $model->crossDocking . ' - ' . $model->periodoAnio . ' - '. $model->periodoMes); die("hola");

                    if ($model->validate()){
                        $id = $model->save();
                    }
                    
                    if ($id != null){
                        $ok = Agendapresupuestosubcategoria::actualizarPresupuesto ($model->idAgendaPresupuesto, 
                                                                                    $model->fechaLlegada, 
                                                                                    $model->crossdocking_id, 
                                                                                    $model->categoria_id, 
                                                                                    $model->subcategoria_id);

                        $transaction->commit();
                        Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
                    }else{
                        Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
                    }

                    return $this->redirect(['indexupdate', 'idagendapresupuesto' => $model->idAgendaPresupuesto]);
                }
            } else {
                $model->loadDefaultValues();
            }

            if (Yii::$app->request->isAjax){  
                return $this->renderAjax('create', [
                    'model' => $model,
                    'modelAgenda' => $modelAgenda,
                ]);
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }  
    }

    /**
     * Updates an existing Agendapresupuestosubcategoria model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = $this->findModel($id);

            $modelAgenda = Agendapresupuesto::findOne(['id' => $model->idAgendaPresupuesto]);
            $model->categoria_id = $model->nombreCategoria->id;
            $model->subcategoria_id = $model->nombreSubcategoria->id;
            $model->crossdocking_id = $model->nombreCrossdocking->id;

            if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } 

            if ($this->request->isPost) {
                if ($model->load($this->request->post())) {

                    $id = null;
                    if ($model->validate()){
                        $id = $model->save();
                    }

                    if ($id != null){
                        $ok = Agendapresupuestosubcategoria::actualizarPresupuesto ($model->idAgendaPresupuesto, 
                                                                                    $model->fechaLlegada, 
                                                                                    $model->crossdocking_id, 
                                                                                    $model->categoria_id, 
                                                                                    $model->subcategoria_id);

                        $transaction->commit();
                        Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
                    }else{
                        Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
                    }

                    return $this->redirect(['indexupdate', 'idagendapresupuesto' => $model->idAgendaPresupuesto]);
                }
            }

            if (Yii::$app->request->isAjax){  
                return $this->renderAjax('update', [
                    'model' => $model,
                    'modelAgenda' => $modelAgenda,
                ]);
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }  
    }

    /**
     * Deletes an existing Agendapresupuestosubcategoria model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = $this->findModel($id);

            $idagendapresupuesto = $model->idAgendaPresupuesto; 
            $fechallegada = $model->fechaLlegada; 
            $crossdocking_id = $model->crossdocking_id; 
            $categoria_id = $model->categoria_id; 
            $subcategoria_id = $model->subcategoria_id;

            if ($model !== null) {
                try {
                    $model->delete();

                    $ok = Agendapresupuestosubcategoria::actualizarPresupuesto ($idagendapresupuesto, 
                                                                                    $fechallegada, 
                                                                                    $crossdocking_id, 
                                                                                    $categoria_id, 
                                                                                    $subcategoria_id);

                    $transaction->commit();

                    Yii::$app->session->setFlash( 'success', 'Registro Eliminado');
                } catch (\yii\db\IntegrityException $e) {
                    Yii::$app->session->setFlash('error', 'No se puede eliminar este registro debido a que tiene agendamientos asociados.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'Registro no encontrado.');
            }

            return $this->redirect(['indexupdate', 'idagendapresupuesto' => $model->idAgendaPresupuesto]);
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Finds the Agendapresupuestosubcategoria model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Agendapresupuestosubcategoria the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Agendapresupuestosubcategoria::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
