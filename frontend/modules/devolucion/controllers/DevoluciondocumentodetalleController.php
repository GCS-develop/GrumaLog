<?php

namespace frontend\modules\devolucion\controllers;


use Yii;
use frontend\models\DataCodigoBarrasDevolucion;
use frontend\models\Devoluciondocumento;
use frontend\models\Devoluciondocumentodetalle;
use frontend\models\search\DevoluciondocumentodetalleSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Expression;

use common\models\User;
use frontend\models\Conteobylecturacodigo;
use frontend\models\search\ConteobylecturacodigoSearch;

/**
 * DevoluciondocumentodetalleController implements the CRUD actions for Devoluciondocumentodetalle model.
 */
class DevoluciondocumentodetalleController extends Controller
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
     * Lists all Devoluciondocumentodetalle models.
     *
     * @return string
     */
    public function actionIndex($iddocumento)
    {
        $model = Devoluciondocumento::findOne(['id' => $iddocumento]);

        $idusuario = Yii::$app->user->id;
        $modeluser = User::findOne(['id' => $idusuario]);
        
        $searchModel = new DevoluciondocumentodetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $iddocumento);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'modeluser' => $modeluser
        ]);
    }

    public function actionIndexall()
    {
        $iddocumento = null;
        $idusuario = Yii::$app->user->id;
        $modeluser = User::findOne(['id' => $idusuario]);
        
        $searchModel = new DevoluciondocumentodetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $iddocumento);

        return $this->render('index_all', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modeluser' => $modeluser
        ]);
    }

    /**
     * Displays a single Devoluciondocumentodetalle model.
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
     * Creates a new Devoluciondocumentodetalle model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($iddocumento)
    {
        $model = new DataCodigoBarrasDevolucion();
        $model->cantidad = 1;

        $modeldocumento = Devoluciondocumento::findOne(['id' => $iddocumento]);

        $idusuario = Yii::$app->user->id;
        $modeluser = User::findOne(['id' => $idusuario]);

        if ($this->request->isPost && $model->load($this->request->post())) {
            
            $modeldetalle = Devoluciondocumentodetalle::find()->where(['idDocumento' => $iddocumento, 'codigoBarras' => $model->codigobarras])->one();

            if ($modeldetalle != null){
                $modeldetalle->cantidadRegistrada = $modeldetalle->cantidadRegistrada + $model->cantidad;
                $modeldetalle->registrada = 1;
                $modeldetalle->usuarioRegistra = Yii::$app->user->id;
                $modeldetalle->fechaRegistra = new Expression('GETDATE()');;

                if ($modeldetalle->save()){
                    $tipo = 3; // Devoluciones
                    $ok = Conteobylecturacodigo::grabarRegistro($tipo, 
                                                                $model->codigobarras, 
                                                                $model->cantidad,
                                                                $modeldocumento->idInterfase,
                                                                $modeldocumento->id,
                                                                $modeldetalle->id,
                                                            ); 
                }

                Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
            }else{
                Yii::$app->session->setFlash( 'error', 'Número Documento NO EXISTE');
            }
            return $this->redirect(['create', 'iddocumento' => $iddocumento]);
        }

        return $this->render('create', [
            'model' => $model,
            'modeldocumento' => $modeldocumento,
            'modeluser' => $modeluser
        ]);
    }

    /**
     * Updates an existing Devoluciondocumentodetalle model.
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
     * Deletes an existing Devoluciondocumentodetalle model.
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

    public function actionDeleteconteo($id)
    {
        $modelconteo = Conteobylecturacodigo::findOne(['id' => $id]);
        $iddetalle = $modelconteo->idConteoDetalle;
        $unidades = $modelconteo->unidades;

        $modelconteo->delete();

        $model = $this->findModel($iddetalle);
        $model->cantidadRegistrada = $model->cantidadRegistrada - $unidades; 
        $model->save();

        return $this->redirect(['viewconteo', 'id' => $model->id]);
    }

    public function actionViewconteo($id)
    {
        $model = $this->findModel($id);

        $modeldocumento = Devoluciondocumento::findOne(['id' => $model->idDocumento]);

        $modulo = 3;
        $idusuario = Yii::$app->user->id;
        $modeluser = User::findOne(['id' => $idusuario]);
        
        $searchModel = new ConteobylecturacodigoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $modulo, $modeldocumento->id, $id);

        return $this->render('index_conteo', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'modeldocumento' => $modeldocumento,
            'modeluser' => $modeluser
        ]);
    }

    /**
     * Finds the Devoluciondocumentodetalle model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Devoluciondocumentodetalle the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Devoluciondocumentodetalle::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
