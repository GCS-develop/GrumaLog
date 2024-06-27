<?php

namespace frontend\modules\agenda\controllers;

use Yii;
use frontend\models\Agendapresupuesto;
use frontend\models\search\AgendapresupuestoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use yii\web\UploadedFile;

use frontend\models\Agendapresupuestosubcategoria;
use frontend\models\Agendapresupuestocategoria;
use frontend\models\Agendapresupuestosemana;
use frontend\models\Agendapresupuestodetalle;
use frontend\models\Agendaentregamercancia;
use frontend\models\FileAgendaInput;
use common\models\ProcedimientosGenerales;

/**
 * AgendapresupuestoController implements the CRUD actions for Agendapresupuesto model.
 */
class AgendapresupuestoController extends Controller
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
     * Lists all Agendapresupuesto models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AgendapresupuestoSearch();
        $idestado = 1;
        $dataProvider = $searchModel->search($this->request->queryParams, $idestado);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
     * Displays a single Agendapresupuesto model.
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
     * Creates a new Agendapresupuesto model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Agendapresupuesto();
        $model->periodoAnio = date('Y');
        $model->periodoMes = date('m') + 1;
        if ($model->periodoMes == 13){
            $model->periodoMes = 1;
            $model->periodoAnio = $model->periodoAnio + 1;
        }

        $model->tienePresupuesto = 0;
        $nombreMes = ProcedimientosGenerales::nombreMes ($model->periodoMes);
        $model->observacion = 'Agendamiento del Período: ' . $model->periodoAnio . ' - ';

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                if ($model->validate()){
                    $model->getPrimerUltimoDiaDelMes();
                    $id = $model->save();
                }

                if ($id != null){
                    Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
                }else{
                    Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
                }

                return $this->redirect(['indexperiodo']);
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }  
    }

    /**
     * Updates an existing Agendapresupuesto model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                $id = null;
                if ($model->validate()){
                    //$model->getPrimerUltimoDiaDelMes();
                    $id = $model->save();
                }

                if ($id != null){
                    Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
                }else{
                    Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
                }

                return $this->redirect(['indexperiodo']);
            }
        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }  
    }

    /**
     * Deletes an existing Agendapresupuesto model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $exists = Agendaentregamercancia::find()->where(['idAgenda' => $id])->exists();

        if ($exists){
            $nombreMes = ProcedimientosGenerales::nombreMes ($model->periodoMes);
            Yii::$app->session->setFlash('error', 'Este periodo ya tiene agendamientos: ' . $model->periodoAnio . ' - ' . $nombreMes);
            return $this->redirect(['index']);
        }
        
        $filasEliminadas = Agendapresupuestosubcategoria::deleteAll(['idAgendaPresupuesto' => $id]);
        $filasEliminadas = Agendapresupuestocategoria::deleteAll(['idAgendaPresupuesto' => $id]);
        $filasEliminadas = Agendapresupuestosemana::deleteAll(['idAgendaPresupuesto' => $id]);
        $filasEliminadas = Agendapresupuestodetalle::deleteAll(['idAgendaPresupuesto' => $id]);

        if ($model !== null) {
            try {
                $model->delete();

                Yii::$app->session->setFlash( 'success', 'Registro Eliminado');
            } catch (\yii\db\IntegrityException $e) {
                Yii::$app->session->setFlash('error', 'No se puede eliminar este registro debido a que tiene agendamientos asociados.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Registro no encontrado.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Agendapresupuesto model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Agendapresupuesto the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Agendapresupuesto::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionExtraerdatapresupuesto ($id){
        $model = new FileAgendaInput(); 

        $exists = Agendapresupuestosubcategoria::find()->where(['idAgendaPresupuesto' => $id])->exists();

        if ($exists){
            $modelAgenda = $this->findModel($id);
            $nombreMes = ProcedimientosGenerales::nombreMes ($modelAgenda->periodoMes);
            Yii::$app->session->setFlash('error', 'Este periodo ya fue ingresado: ' . $modelAgenda->periodoAnio . ' - ' . $nombreMes);
            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }         

        if ($model->load(Yii::$app->request->post())) {

            $userId = Yii::$app->user->id;
            $model->archivo = UploadedFile::getInstance($model, 'archivo');

            $modelAgenda = $this->findModel($id);

            if ($model->upload($modelAgenda->id)) {

                $modelAgenda = $this->findModel($id);
                $modelAgenda->tienePresupuesto = 1;
                $modelAgenda->save();

                Yii::$app->session->setFlash('success', 'El Archivo se ha cargado correctamente. ');
                return $this->redirect(['/agenda/agendapresupuestodetalle/index', 'idagendapresupuesto' => $id]);
            }else{
                $errorString = ProcedimientosGenerales::erroresModelo ($model->getErrors());
                Yii::$app->session->setFlash('error', 'Ocurrió un error al cargar los archivos: ' . $errorString);
            }

            return $this->redirect(['index']);

        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('uploadpresupuesto', [
                'model' => $model,
            ]);
        }  
    }

    public function actionImportaragendaxls ($id){
        $model = new FileAgendaInput(); 

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }         

        if ($model->load(Yii::$app->request->post())) {

            $userId = Yii::$app->user->id;
            $model->archivo = UploadedFile::getInstance($model, 'archivo');

            $modelAgenda = $this->findModel($id);

            if ($model->importaAgendamiento($modelAgenda->id)) {

                Yii::$app->session->setFlash('success', 'El Archivo se ha cargado correctamente. ');
                return $this->redirect(['/agenda/agendapresupuestodetalle/index', 'idagendapresupuesto' => $id]);
            }else{
                $errorString = ProcedimientosGenerales::erroresModelo ($model->getErrors());
                Yii::$app->session->setFlash('error', 'Ocurrió un error al cargar los archivos: ' . $errorString);
            }

            return $this->redirect(['index']);

        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('uploadpresupuesto', [
                'model' => $model,
            ]);
        }  
    }

    public function actionViewdatapresupuesto ($id){
        return $this->redirect(['/agenda/agendapresupuestodetalle/index', 'idagendapresupuesto' => $id]);
    }


    public function actionViewdatainconsistencia ($id){
        return $this->redirect(['/agenda/agendapresupuestodetalle/indexinconsistencia', 'idagendapresupuesto' => $id]);
    }
}
