<?php

namespace frontend\modules\auditoriamanual\controllers;

use Yii;
use frontend\models\Auditoriamanualimportacion;
use frontend\models\search\AuditoriamanualimportacionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\web\UploadedFile;

use frontend\models\FileAgendaInput;
use frontend\models\Auditoriamanualimportaciondetalle;

use common\models\ProcedimientosGenerales;

/**
 * AuditoriamanualimportacionController implements the CRUD actions for Auditoriamanualimportacion model.
 */
class AuditoriamanualimportacionController extends Controller
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
     * Lists all Auditoriamanualimportacion models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AuditoriamanualimportacionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpload (){
        $model = new FileAgendaInput(); 

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }         

        if ($model->load(Yii::$app->request->post())) {

            $userId = Yii::$app->user->id;
            $model->archivo = UploadedFile::getInstance($model, 'archivo');

            $respuesta = Auditoriamanualimportaciondetalle::upload($model->archivo);

            if ($respuesta) {

                Yii::$app->session->setFlash('success', 'El Archivo se ha cargado correctamente. ');
                return $this->redirect(['index']);
            }else{
                $errorString = ProcedimientosGenerales::erroresModelo ($model->getErrors());
                Yii::$app->session->setFlash('error', 'Ocurrió un error al cargar los archivos: ' . $errorString);
            }

            return $this->redirect(['index']);

        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('uploaddata', [
                'model' => $model,
            ]);
        }  
    }

    /**
     * Displays a single Auditoriamanualimportacion model.
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
     * Creates a new Auditoriamanualimportacion model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Auditoriamanualimportacion();

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
     * Updates an existing Auditoriamanualimportacion model.
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
     * Deletes an existing Auditoriamanualimportacion model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $filasEliminadas = Auditoriamanualimportaciondetalle::deleteAll([
            'idInterfase' => $model->id
        ]);

        $model->delete();
        Yii::$app->session->setFlash( 'success', 'Registro Eliminado');

        /*if ($model !== null) {
            try {
                $model->delete();

                Yii::$app->session->setFlash( 'success', 'Registro Eliminado');
            } catch (\yii\db\IntegrityException $e) {
                Yii::$app->session->setFlash('error', 'No se puede eliminar este registro debido a que tiene documentos asociados.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Registro no encontrado.');
        }*/

        return $this->redirect(['index']);

    }

    /**
     * Finds the Auditoriamanualimportacion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Auditoriamanualimportacion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Auditoriamanualimportacion::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
