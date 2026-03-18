<?php

namespace frontend\modules\despacho\controllers;

use Yii;
use frontend\models\Tipodocumentodespacho;
use frontend\models\search\TipodocumentodespachoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

class TipodocumentodespachoController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ]);
    }

    public function actionIndex()
    {
        // Agrupar registros por (origen, accion) para la vista matricial
        $records = Tipodocumentodespacho::find()->with('tipoDocumento')->all();

        $matriz = [
            'Cedi'   => ['Despachar' => [], 'Recibir' => []],
            'Tienda' => ['Despachar' => [], 'Recibir' => []],
        ];
        foreach ($records as $r) {
            $origen = trim($r->origen);
            $accion = trim($r->accion);
            if (isset($matriz[$origen][$accion])) {
                $matriz[$origen][$accion][] = $r;
            }
        }

        return $this->render('index', ['matriz' => $matriz]);
    }

    public function actionCreate()
    {
        $model = new Tipodocumentodespacho();

        // Pre-llenar origen/accion si vienen por GET (desde el botón de la celda)
        $model->origen = Yii::$app->request->get('origen', null);
        $model->accion = Yii::$app->request->get('accion', null);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->validate() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Registro guardado correctamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al guardar el registro.');
            }
            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', ['model' => $model]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->validate() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Registro actualizado correctamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al actualizar el registro.');
            }
            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', ['model' => $model]);
        }
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Registro eliminado.');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Tipodocumentodespacho::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('El registro solicitado no existe.');
    }
}
