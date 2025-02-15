<?php

namespace frontend\modules\despacho\controllers;

use frontend\models\Planillaembarquetraspaso;
use Yii;
use frontend\models\Planillaembarque;
use frontend\models\search\PlanillaembarqueSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\search\PlanillaembarquetraspasoSearch;
use frontend\models\Parametroscontrol;
use frontend\models\Estadodespacho;
use frontend\models\Estadorecepcion;
use frontend\models\Transportadora;


use kartik\mpdf\Pdf;
use Mpdf\Mpdf;

use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

/**
 * PlanillaembarqueController implements the CRUD actions for Planillaembarque model.
 */
class PlanillaembarqueController extends Controller
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
     * Lists all Planillaembarque models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PlanillaembarqueSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Planillaembarque model.
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
     * Creates a new Planillaembarque model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    // public function actionCreate()
    // {
    //     $model = new Planillaembarque();

    //     if ($this->request->isPost) {
    //         if ($model->load($this->request->post()) && $model->save()) {
    //             return $this->redirect(['view', 'id' => $model->id]);
    //         }
    //     } else {
    //         $model->loadDefaultValues();
    //     }

    //     return $this->render('create', [
    //         'model' => $model,
    //     ]);
    // }

    public function actionCreate()
    {
        $model = new Planillaembarque();

        $modelestado = Estadodespacho::find()->where(['codigo' => '01'])->one();

        $model->flotaPropia = 1;
        $model->idEstado = $modelestado->id;
        $model->fechaDespacho = date('Y-m-d');
        $model->horaDespacho = date('H:i');

        $transportadora = Transportadora::find()->
            where(['nombre' => 'HERPO FLOTA PROPIA'])->one();

        $model->idTransportadora = $transportadora->id;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                if ($model->validate()) {
                    $id = $model->save();
                }

                if ($id != null) {
                    Yii::$app->session->setFlash('success', 'Registro Actualizado');
                } else {
                    Yii::$app->session->setFlash('error', 'Error Actualizando Registro');
                }

                return $this->redirect(['index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Planillaembarque model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    // public function actionUpdate($id)
    // {
    //     $model = $this->findModel($id);

    //     if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
    //         return $this->redirect(['view', 'id' => $model->id]);
    //     }

    //     return $this->render('update', [
    //         'model' => $model,
    //     ]);
    // }

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
                if ($model->validate()) {
                    $id = $model->save();
                }

                if ($id != null) {
                    Yii::$app->session->setFlash('success', 'Registro Actualizado');
                } else {
                    Yii::$app->session->setFlash('error', 'Error Actualizando Registro');
                }

                return $this->redirect(['index']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Planillaembarque model.
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

    /**
     * Finds the Planillaembarque model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Planillaembarque the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Planillaembarque::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionAnular($id)
    {
        $modelestadorecepcion = Estadorecepcion::find()->where(['codigo' => '03'])->one();
        $idestadorecepcion = $modelestadorecepcion->id;

        $modelestadodespacho = Estadodespacho::find()->where(['codigo' => '04'])->one();
        $idestadodespacho = $modelestadodespacho->id;

        $model = $this->findModel($id);

        $filas = Planillaembarquetraspaso::find()->where([
            'idPlanillaEmbarque' => $id,
            'idEstado' => $idestadorecepcion
        ], )->count();
        if ($filas > 0) {
            Yii::$app->session->setFlash('error', 'Error: Planilla Tiene Traspasos Recibidos: ' . $filas);
            return $this->redirect(['index']);
        }

        if ($this->request->isPost) {

            $model->idEstado = $idestadodespacho;

            if ($model->save()) {

                $model->anularplanillatraspaso;

                return $this->redirect(['index']);

            } else {
                Yii::error('Error de anulacion PlanillaEmbarque. ' . __METHOD__ . ' ' . print_r($model->getErrors(), true), __METHOD__);

                Yii::$app->session->setFlash('error', 'Ups!, ocurrio un problema la planilla de emabarque, no se puede anular por que: ' . json_encode($model->getErrors()));

                return $this->redirect(['index']);

            }
        }
    }

    public function actionGeneratepdf($id)
    {
        // Crear una nueva instancia de Mpdf
        $mpdf = new Mpdf();

        // Configurar el pie de página para incluir la paginación
        $mpdf->SetFooter('{PAGENO} de {nbpg}'); // PAGENO para el número de página actual, nbpg para el total de páginas

        $username = Yii::$app->user->identity->username;
        $planillaembarque = Planillaembarque::findOne(['id' => $id]);

        $usernamePlanilla = $planillaembarque->usuario->username;


        $nombreEmpresa = Parametroscontrol::getValorparametro('001');
        $nitEmpresa = Parametroscontrol::getValorparametro('002');
        $direccion = Parametroscontrol::getValorparametro('003');
        $telefono = Parametroscontrol::getValorparametro('004');
        $email = Parametroscontrol::getValorparametro('006');
        $ciudad = Parametroscontrol::getValorparametro('007');
        $paginaweb = Parametroscontrol::getValorparametro('008');

        $planillaData = [
            'nombreEmpresa' => $nombreEmpresa,
            'nitEmpresa' => $nitEmpresa,
            'direccion' => $direccion,
            'telefono' => $telefono,
            'email' => $email,
            'ciudad' => $ciudad,
            'paginaweb' => $paginaweb
        ];

        $searchModel = new PlanillaembarquetraspasoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $id);

        $dataProvider->pagination = false;

        $data = $dataProvider->getModels();

        $totalGeneral = 0; // Para almacenar el total general
        $totalGeneralUndEmp = 0;

        $previousDestino = null;
        $totalDestino = 0;
        $totalUnidadesEmp = 0;
        $tableRows = ''; // Para acumular las filas de la tabla por destino
        $numeroBodegasDestino = 0;

        // var_dump($data); die("hola");
        
        foreach ($data as $registro) {
            if ($previousDestino !== null && $previousDestino !== $registro['almacenDestino']) {

                $htmlTotalDestino = $this->renderPartial('_tabla_destino', [
                    'rows' => $tableRows,
                    'destino' => $previousDestino,
                    'totalDestino' => $totalDestino,
                    'totalUnidadesEmp' => $totalUnidadesEmp,
                    'planillaData' => $planillaData,
                    'planillaembarque' => $planillaembarque
                ]);
                $mpdf->WriteHTML($htmlTotalDestino);

                $htmlTotalDestino = $this->renderPartial('_total_destino', [
                    'destino' => $previousDestino,
                    'totalDestino' => $totalDestino,
                    'totalUnidadesEmp' => $totalUnidadesEmp,
                ]);

                $mpdf->WriteHTML($htmlTotalDestino);

                // Hacer un salto de página
                $mpdf->AddPage();  

                // Resetear la tabla y el total del nuevo destino
                $tableRows = '';
                $totalDestino = 0;
                $totalUnidadesEmp = 0;
                $numeroBodegasDestino += 1;
            }

            // Acumular las filas de la tabla para el destino actual
            $tableRows .= $this->renderPartial('_fila_registro', [
                'registro' => $registro,
            ]);

            // Acumular unidades para el destino actual
            $totalDestino += $registro['unidades'];
            $totalUnidadesEmp += $registro['unidadesEmp'];

            // Acumular el total general
            $totalGeneral += $registro['unidades'];
            $totalGeneralUndEmp += $registro['unidadesEmp'];

            // Actualizar el destino previo
            $previousDestino = $registro['almacenDestino'];
        }

        // Después de recorrer todos los registros, renderizar la última tabla acumulada
        if (!empty($tableRows)) {
            $numeroBodegasDestino += 1;

            $htmlTable = $this->renderPartial('_tabla_destino', [
                'rows' => $tableRows,
                'destino' => $previousDestino,
                'totalDestino' => $totalDestino,
                'totalUnidadesEmp' => $totalUnidadesEmp,
                'planillaData' => $planillaData,
                'planillaembarque' => $planillaembarque
            ]);
            $mpdf->WriteHTML($htmlTable);

            $htmlTotalDestino = $this->renderPartial('_total_destino', [
                'destino' => $previousDestino,
                'totalDestino' => $totalDestino,
                'totalUnidadesEmp' => $totalUnidadesEmp,
            ]);

            $mpdf->WriteHTML($htmlTotalDestino);

        }

        // Agregar un salto de página antes del total general
        //$mpdf->AddPage();

        // Mostrar el total general
        $htmlTotalGeneral = $this->renderPartial('_total_general', [
            'username' => $usernamePlanilla,
            'totalGeneral' => $totalGeneral,
            'totalGeneralUndEmp' => $totalGeneralUndEmp,
            'numeroBodegasDestino' => $numeroBodegasDestino
        ]);
        $mpdf->WriteHTML($htmlTotalGeneral);


        // Generar y mostrar el PDF en una nueva pestaña
        return $mpdf->Output('reporte.pdf', 'I');
    }

}
