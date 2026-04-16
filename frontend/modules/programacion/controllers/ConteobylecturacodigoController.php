<?php

namespace frontend\modules\programacion\controllers;

use Yii;
use frontend\models\Conteobylecturacodigo;
use frontend\models\search\ConteobylecturacodigoSearch;
use frontend\models\Conteoentregamercancia;
use frontend\models\Programacionentregamercancia;
use frontend\models\Logborradoconteo;
use frontend\models\Item;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ConteobylecturacodigoController implements the CRUD actions for Conteobylecturacodigo model.
 */
class ConteobylecturacodigoController extends Controller
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
     * Lists all Conteobylecturacodigo models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ConteobylecturacodigoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexusuario()
    {
        $searchModel = new ConteobylecturacodigoSearch();
        $dataProvider = $searchModel->searchxUsuario($this->request->queryParams);

        return $this->render('index_usuario', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Conteobylecturacodigo model.
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
     * Creates a new Conteobylecturacodigo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Conteobylecturacodigo();

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
     * Updates an existing Conteobylecturacodigo model.
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
     * Deletes an existing Conteobylecturacodigo model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $idconteodetalle = $model->idConteoDetalle;
        $idprogramacion  = $model->idConteoDestino;
        $unidadesconteo  = $model->unidades;

        // Capturar datos del item antes de eliminar el scan
        $itemModel  = Item::findOne(['codigoBarras' => $model->codigoBarras]);
        $itemCodigo = $itemModel ? $itemModel->item : null;
        $itemColor  = $itemModel && $itemModel->color ? $itemModel->color->nombre : null;
        $itemTalla  = $itemModel && $itemModel->talla ? trim($itemModel->talla->nombre) : null;

        $model->delete();

        // Actualizar el total del conteo
        $modelconteo = Conteoentregamercancia::findOne(['id' => $idconteodetalle]);
        if ($modelconteo) {
            $unidadesAntes = (int) $modelconteo->unidadesConteo;
            $modelconteo->unidadesConteo = $modelconteo->unidadesConteo - $unidadesconteo;
            $modelconteo->save(false);

            // Registrar en el log de borrados
            $programacion = Programacionentregamercancia::findOne(['id' => $idprogramacion]);
            if ($programacion) {
                Logborradoconteo::registrar(
                    $programacion,
                    'ELIMINAR_SCAN',
                    $unidadesAntes,
                    $unidadesconteo,
                    $itemCodigo,
                    $itemColor,
                    $itemTalla
                );
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Conteobylecturacodigo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Conteobylecturacodigo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Conteobylecturacodigo::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
