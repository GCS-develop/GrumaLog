<?php

namespace frontend\modules\traspaso\controllers;

use Codeception\Module\Yii2;
use Yii;
use frontend\models\Traspaso;
use frontend\models\search\TraspasoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\db\Expression;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


/**
 * TraspasoController implements the CRUD actions for Traspaso model.
 */
class TraspasoController extends Controller
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
     * Lists all Traspaso models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TraspasoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        Yii::$app->session['traspasoIndexUrl'] = Yii::$app->request->url;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Traspaso model.
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
     * Creates a new Traspaso model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Traspaso();

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

                return $this->redirect(Yii::$app->request->referrer ?: ['index']);
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


    // public function actionRetornar($id)
    // {
    //     $model = $this->findModel($id);

    //     if ($model->bodegaOrigen->cedi === 1 && $model->idEstado == 6) {

    //         $model->idEstado = 3; // Retorna al estado "Muelle"
    //         $model->anula_at = null; // Limpiar la fecha de anulación
    //         $model->anula_by = null; // Limpiar el usuario que anuló


    //     } elseif ($model->bodegaOrigen->cedi === 0 && $model->idEstado == 6) {

    //         $model->idEstado = 1; // Retorna al estado "Terminado"
    //         $model->anula_at = null; // Limpiar la fecha de anulación
    //         $model->anula_by = null; // Limpiar el usuario que anuló
    //     }

    //     if ($model->save()) {

    //         Yii::$app->session->setFlash('success', 'El traspaso se retorno a estado ' . $model->estado->nombre . ' correctamente.');
    //     } else {

    //         Yii::$app->session->setFlash('error', 'Hubo un error al intentar retornar el traspaso. Por favor, inténtalo de nuevo.');
    //     }

    //     return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    // }

    public function actionRetornar($id)
    {
        $model = $this->findModel($id);

        $verificarAEN = $model->verificarEstadoAEN();

        if (!empty($verificarAEN)) {
            Yii::$app->session->setFlash('warning', 'No se puede retornar el traspaso porque tiene un AEN activo.');
            return $this->redirect(Yii::$app->request->referrer ?: ['index']);
        }

        if ($model->bodegaOrigen->cedi === 1 && $model->idEstado == 6) {

            $model->idEstado = 3; // Retorna al estado "Muelle"
            $model->anula_at = null; // Limpiar la fecha de anulación
            $model->anula_by = null; // Limpiar el usuario que anuló


        } elseif ($model->bodegaOrigen->cedi === 0 && $model->idEstado == 6) {

            $model->idEstado = 1; // Retorna al estado "Terminado"
            $model->anula_at = null; // Limpiar la fecha de anulación
            $model->anula_by = null; // Limpiar el usuario que anuló
        }

        if ($model->save()) {

            Yii::$app->session->setFlash('success', 'El traspaso se retorno a estado ' . $model->estado->nombre . ' correctamente.');
        } else {

            Yii::$app->session->setFlash('error', 'Hubo un error al intentar retornar el traspaso. Por favor, inténtalo de nuevo.');
        }

        return $this->redirect(Yii::$app->session['traspasoIndexUrl'] ?? ['index']);
    }



    /**
     * Updates an existing Traspaso model.
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

    public function verificarEstadoAEN($id)
    {
        $model = $this->findModel($id);

        $verificarAEN = $model->verificarEstadoAEN();

        if (!empty($verificarAEN)) {
            Yii::$app->session->setFlash('warning', 'No se puede retornar el traspaso porque tiene un AEN activo.');
            return $this->redirect(Yii::$app->request->referrer ?: ['index']);
        }

        // if ($model->bodegaOrigen->cedi === 1 && $model->idEstado == 6) {

        //     $model->idEstado = 3; // Retorna al estado "Muelle"
        //     $model->anula_at = null; // Limpiar la fecha de anulación
        //     $model->anula_by = null; // Limpiar el usuario que anuló


        // } elseif ($model->bodegaOrigen->cedi === 0 && $model->idEstado == 6) {

        //     $model->idEstado = 1; // Retorna al estado "Terminado"
        //     $model->anula_at = null; // Limpiar la fecha de anulación
        //     $model->anula_by = null; // Limpiar el usuario que anuló
        // }

        if ($model->save()) {

            Yii::$app->session->setFlash('success', 'El traspaso se retorno a estado ' . $model->estado->nombre . ' correctamente.');
        } else {

            Yii::$app->session->setFlash('error', 'Hubo un error al intentar retornar el traspaso. Por favor, inténtalo de nuevo.');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }


    public function actionExportarExcel()
    {
        $searchModel = new TraspasoSearch();
        $params = Yii::$app->request->queryParams;

        // Obtener los filtros de búsqueda aplicados
        $searchModel->load($params);

        // Ejecuta el query con los filtros aplicados
        $query = $searchModel->search($params)->query;

        // Ejecutar el query directamente para obtener los datos sin paginación
        $results = $query->all();

        // Crear un nuevo objeto Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Establecer los encabezados de las columnas
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Consecutivo');
        $sheet->setCellValue('C1', 'Creado Por');
        $sheet->setCellValue('D1', 'Bodega Origen');
        $sheet->setCellValue('E1', 'Bodega Destino');
        $sheet->setCellValue('F1', 'Tipo Movimiento');
        $sheet->setCellValue('G1', 'Consecutivo Siesa');
        $sheet->setCellValue('H1', 'Estado Planilla');
        $sheet->setCellValue('I1', 'Fecha Recibido');
        // Añadir más columnas si es necesario

        // Rellenar las filas con los datos
        $rowNum = 2; // Comenzamos desde la segunda fila (debido a los encabezados)
        foreach ($results as $row) {
            $sheet->setCellValue('A' . $rowNum, $row->id);
            $sheet->setCellValue('B' . $rowNum, $row->consecutivo);
            $sheet->setCellValue('C' . $rowNum, $row->created_by);
            $sheet->setCellValue('D' . $rowNum, $row->idBodegaOrigen);
            $sheet->setCellValue('E' . $rowNum, $row->idBodegaDestino);
            $sheet->setCellValue('F' . $rowNum, $row->tipoMovimiento);
            $sheet->setCellValue('G' . $rowNum, $row->consecutivosiesa);
            $sheet->setCellValue('H' . $rowNum, $row->estadoPlanilla);
            $sheet->setCellValue('I' . $rowNum, $row->fechaRecibido);
            // Añadir más filas si es necesario

            $rowNum++;
        }

        // Crear un objeto Writer
        $writer = new Xlsx($spreadsheet);

        // Configurar el nombre del archivo y el tipo de respuesta
        $fileName = 'traspasos_export.xlsx';
        Yii::$app->response->getHeaders()->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        Yii::$app->response->getHeaders()->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        Yii::$app->response->getHeaders()->set('Cache-Control', 'max-age=0');

        // Guardar el archivo en la respuesta
        $writer->save('php://output');
        exit; // Asegurarse de que no se realice más procesamiento

    }


    /**
     * Deletes an existing Traspaso model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model !== null) {
            try {
                $model->delete();

                Yii::$app->session->setFlash('success', 'Registro Eliminado');
            } catch (\yii\db\IntegrityException $e) {
                Yii::$app->session->setFlash('error', 'No se puede eliminar este registro debido a que tiene subcategorías asociadas.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Registro no encontrado.');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    public function actionSincronizar($id)
    {

        Traspaso::sincronizarTraspaso($id);

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    /**
     * Finds the Traspaso model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Traspaso the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Traspaso::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionAnular($id)
    {
        $model = $this->findModel($id);
        $filasAfectadas = 0;
        $unidsPedido     = 0;

        if ($model->idEstado === 2) {

            Yii::$app->session->setFlash('warning', 'No puedes anular en este estado!');
            return $this->redirect(Yii::$app->request->referrer ?: ['index']);
        }

        $existeEnPlanilla = $model->ultimaplanillaembarquetraspaso ? $model->ultimaplanillaembarquetraspaso->estadoPlanillaNoAnulado : false;

        if ($existeEnPlanilla) {

            Yii::$app->session->setFlash(
                'warning',
                'No puedes anular si existe en una planilla, numero de planilla: ' . $model->ultimaplanillaembarquetraspaso->idPlanillaEmbarque . ' estado: ' . $existeEnPlanilla->nombre
            );
            return $this->redirect(Yii::$app->request->referrer ?: ['index']);
        }

        if ($this->request->isPost) {
            $valid = $model->validarPertenenciaItemsPedido();
            if (!$valid['ok']) {
                $lineas = array_map(
                    fn($x) => "· Item {$x['idItem']} (cant {$x['cantidad']})",
                    $valid['noPertenecen']
                );
                Yii::$app->session->addFlash(
                    'warning',
                    "Hay ítems del traspaso que NO pertenecen al pedido #{$model->idPedido} (bodega destino {$model->bodegaDestino->nombre}):\n" . implode("\n", $lineas)
                );
                return $this->redirect(Yii::$app->request->referrer ?: ['index']);
            }
            $model->anula_at = new Expression('GETDATE()');
            $model->anula_by = Yii::$app->user->id;

            $model->idEstado = 2;


            if ($model->save()) {

                foreach ($model->traspasodetalles as $detalle) {
                    $filasAfectadas += $detalle->retornarInventario();
                    $unidsPedido    += (int)$detalle->retornarPedidoAR(); // ahora devuelve UNIDADES
                }

                Yii::$app->session->addFlash('info', 'Items: ' . $filasAfectadas . ' regresaron fueron regresados al inventario');
                Yii::$app->session->addFlash('info',    "Pedido: {$unidsPedido} unidades revertidas en unidadesRecibidas.");

                Yii::$app->session->addFlash(
                    'success',
                    'Traspaso anulado con éxito! ' .
                        $model->tipodocumento->codigo .
                        (isset($model->codigoerp) ? $model->codigoerp->f350_consec_docto : ' interno: ' . $model->consecutivo)
                );

                return $this->redirect(Yii::$app->request->referrer ?: ['index']);
            } else {

                Yii::$app->session->setFlash('error', 'Ups!, ocurrio un problema con : ' . $model);
            }
        }
    }

    public function actionFactura($id)
    {
        $model = $this->findModel($id);
        if ($model->idEstado == 0) {
            Yii::$app->session->setFlash('warning', 'No puedes imprimir en este estado!');
            return $this->redirect(Yii::$app->request->referrer ?: ['index']);
        }

        return $this->redirect(['/traspaso/traspasodetalle/print', 'idtraspaso' => $model->id]);
    }

    public function actionCambiarEstado()
    {
        // Yii::info('Acción Cambiar Estado ejecutada para mandar a muelle de forma masiva los 207 (VMI)', __METHOD__);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $ids = Yii::$app->request->post('ids');

        if (Yii::$app->request->isAjax && Yii::$app->request->post()) {

            // Yii::debug($ids, 'ajax');

            if ($ids) {
                // Primero obtenemos los registros que están en estado "terminado"
                $traspasos = Traspaso::find()
                    ->where(['id' => $ids])
                    ->andWhere(['in', 'idEstado', [1, 4]])
                    ->all();

                // Verificar si hay registros en estado "terminado"
                if (empty($traspasos)) {
                    return ['success' => false, 'message' => 'No se encontraron registros en estado "terminado" para actualizar.'];
                }

                // Array para almacenar los errores
                $errores = [];

                // Intentamos actualizar los registros
                foreach ($traspasos as $traspaso) {
                    // Actualizar el estado de cada registro
                    $traspaso->idEstado = 3;  // Aquí pones el nuevo estado que deseas
                    $traspaso->muelle_at = new Expression('GETDATE()');
                    $traspaso->muelle_by = Yii::$app->user->id;

                    if (!$traspaso->save()) {
                        // Si algo falla, guardamos el error
                        $errores[] = 'Error al actualizar el registro con ID ' . $traspaso->id;
                    }
                }

                // Si no hubo errores, confirmamos la actualización
                if (empty($errores)) {


                    return ['success' => true, 'message' => 'Todos los registros se actualizaron correctamente.'];
                } else {
                    // Si hubo errores, reportamos qué registros fallaron
                    return ['success' => false, 'message' => implode(', ', $errores)];
                }
            } else {
                return ['success' => false, 'message' => 'No se seleccionaron registros.'];
            }
        }

        return ['success' => false, 'message' => 'La solicitud no es válida.'];
    }



    public function actionDirecto($id)
    {
        $model = $this->findModel($id);

        if ($model->idEstado != 2) {

            $model->idEstado = 4;

            if ($model->save()) {

                Yii::$app->session->setFlash('success', 'Estado cambiado!');
                return $this->redirect(Yii::$app->request->referrer ?: ['index']);
            } else {

                Yii::$app->session->setFlash('error', 'Ups!, ocurrio un problema');
            }
        } else {

            Yii::$app->session->setFlash('warning', 'No puedes cambiar a ' . $model->estado->nombre . ' desde este estado!');
            return $this->redirect(Yii::$app->request->referrer ?: ['index']);
        }
    }

    public function actionInterno($id)
    {
        $model = $this->findModel($id);

        if ($model->idEstado != 2) {

            $model->idEstado = 5;

            if ($model->save()) {

                Yii::$app->session->setFlash('success', 'Estado cambiado!');
                return $this->redirect(Yii::$app->request->referrer ?: ['index']);
            } else {

                Yii::$app->session->setFlash('error', 'Ups!, ocurrio un problema');
            }
        } else {

            Yii::$app->session->setFlash('warning', 'No puedes cambiar a ' . $model->estado->nombre . ' desde este estado!');
            return $this->redirect(Yii::$app->request->referrer ?: ['index']);
        }
    }

    // public function actionEjecutarExe()
    // {
    // $ruta = 'C:/Users/soporte/Desktop/sincronizar inventario grumasiesa.bat';

    // if (file_exists($ruta)) {
    //     $salida = shell_exec("cmd /c \"$ruta\"");
    //     return $this->renderContent("<pre>$salida</pre>");
    // } else {
    //     throw new \yii\web\NotFoundHttpException("El archivo no existe.");
    // }

    // }


    // public function actionEjecutarExe()
    // {
    //     // Comando completo EXACTAMENTE como en CMD
    //     $comando = '"E:\laragon\bin\php\php-8.1.10-win32-vs16-x64\php.exe" E:\laragon\www\conektasiesav2\yii siesa/actualizarinventariomanual';

    //     // Ejecutar y capturar salida (con errores)
    //     $salida = shell_exec("cmd /c $comando 2>&1");

    //     // Mostrar salida
    //     return $this->renderContent("<pre>$salida</pre>");
    // }

    public function actionEjecutarExe()
    {
        $php = '"E:\laragon\bin\php\php-8.1.10-win32-vs16-x64\php.exe"';
        $rutaProyecto = 'C:\Apache24\htdocs\conektasiesav2';
        $comando = "$php $rutaProyecto\\yii siesa/actualizarinventariomanual";
        // $comando = "$php $rutaProyecto\\yii siesa/actualizarinventario";

        // Ejecutar en el directorio correcto
        $salida = shell_exec("cd /d $rutaProyecto && $comando 2>&1");

        return $this->renderContent("<pre>$salida</pre>");
    }
}
