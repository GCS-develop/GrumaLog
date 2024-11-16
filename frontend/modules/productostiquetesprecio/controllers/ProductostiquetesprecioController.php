<?php

namespace frontend\modules\productostiquetesprecio\controllers;
use yii\db\Exception;

// use Exception;
use frontend\models\FileAgendaInput;
use frontend\models\Productostiquetesprecio;
use frontend\models\search\ProductostiquetesprecioSearch;
use common\models\ProcedimientosGenerales;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

/**
 * ProductostiquetesprecioController implements the CRUD actions for Productostiquetesprecio model.
 */
class ProductostiquetesprecioController extends Controller
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
     * Lists all Productostiquetesprecio models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ProductostiquetesprecioSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Productostiquetesprecio model.
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
     * Creates a new Productostiquetesprecio model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Productostiquetesprecio();

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
     * Updates an existing Productostiquetesprecio model.
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
     * Deletes an existing Productostiquetesprecio model.
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
     * Finds the Productostiquetesprecio model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Productostiquetesprecio the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Productostiquetesprecio::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionPrint($id)
    {
        $modelo = $this->findModel($id);

        Yii::trace('Iniciando la impresion de tiquetes.', __METHOD__);


        $totalStickers = $modelo->existencia; // Número total de stickers a imprimir.

        if ($totalStickers <= 0) {
            Yii::$app->session->setFlash('error', 'Cantidad inválida para imprimir.');
            return $this->redirect(['index']);
        }

        // Construir el código ZPL dinámico.
        $zpl = "^XA\n";
        $x = 20; // Posición inicial horizontal (en la primera columna).
        $y = 30; // Posición inicial vertical (en la primera fila).
        $incrementoX = 270; // Separación horizontal entre stickers (movemos hacia la siguiente columna).
        $incrementoY = 100; // Separación vertical entre stickers (bajamos por la columna).
        $lineasPorColumna = 2; // Cantidad de stickers por columna.
        $saltoColumnaStikers = 6; // Número de stickers por "gran columna" (6 stickers).
        $yInicio = $y; // Guardar la posición de inicio para saltar correctamente después de 6 stickers.
        $xInicio = $x;

        // Iterar según la existencia (cantidad de stickers).
        for ($i = 0; $i < $totalStickers; $i++) {
            // Añadir solo el precio en la etiqueta.

            // $zpl .= "^FO{$x},{$y}^A0N,50,50^FD$" . $modelo->precio . "^FS\n";
            
            $zpl .= "^FO{$x},{$y}^A0N,50,50^FD$" . number_format($modelo->precio, 0, ',', '.') . "^FS\n";


            // Desplazar hacia abajo para el siguiente sticker.
            $y += $incrementoY;

            // Si hemos llegado al final de la columna (3 stickers), reiniciamos $y y movemos a la siguiente columna.
            if (($i + 1) % $lineasPorColumna == 0) {
                $y = $yInicio; // Reiniciar la posición vertical para la siguiente columna.
                $x += $incrementoX; // Mover a la siguiente columna.
            }

            // Si hemos alcanzado el salto después de 6 stickers, enviar a la impresora y mover a la siguiente "gran columna".
            if (($i + 1) % $saltoColumnaStikers == 0 || ($i + 1) == $totalStickers) {
                // Enviar el bloque de stickers impresos hasta este momento.
                $zpl .= "^XZ"; // Fin de la etiqueta ZPL de esta "gran columna".
                $envio = $this->enviarImpresora($zpl);

                // Reiniciar el ZPL para la siguiente columna de stickers, si quedan más stickers.
                if (($i + 1) < $totalStickers) {
                    $zpl = "^XA\n"; // Reiniciar la impresión ZPL.
                }

                // Restablecer las posiciones para el siguiente bloque de 9 stickers.
                $x = $xInicio; // Reiniciar la posición horizontal.
                $y = $yInicio; // Restablecer la posición vertical a la columna inicial.
            }
        }
        if ($envio) {
            Yii::$app->session->setFlash('success', 'Imprimiendo... : ' . $modelo->existencia . ' del codigo: ' . $modelo->codigoBarra);
        }
        return $this->redirect(['index']);
    }

    private function enviarImpresora($zpl)
    {
        $ip = '192.168.3.11'; // Cambia a la IP de tu impresora.
        // $ip = '192.168.1.233'; // Cambia a la IP de tu impresora.
        $puerto = 9100;

        // Intentar establecer la conexión.
        $socket = @fsockopen($ip, $puerto, $errno, $errstr, 10); // El '@' suprime los warnings
        if (!$socket) {
            // Log de error en lugar de lanzar la excepción
            Yii::error("No se pudo conectar a la impresora en $ip:$puerto. Error: $errstr ($errno)", __METHOD__);

            // Opcional: Puedes mostrar un mensaje al usuario
            Yii::$app->session->setFlash('error', 'No se pudo conectar a la impresora. Intenta nuevamente.');

            // O también podrías enviar una notificación de error o registrar un error en un archivo de log
            return false; // Indicar que no se pudo conectar
        }

        // Si la conexión fue exitosa, enviar los datos.
        fwrite($socket, $zpl);
        fclose($socket);

        return true; // Conexión y envío exitoso
    }

    public function actionUpload()
    {
        $model = new FileAgendaInput();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            // $userId = Yii::$app->user->id;
            $model->archivo = UploadedFile::getInstance($model, 'archivo');

            $respuesta = Productostiquetesprecio::upload($model->archivo);

            if ($respuesta) {

                Yii::$app->session->setFlash('success', 'El Archivo se ha cargado correctamente. ');
                return $this->redirect(['index']);
            } else {
                $errorString = ProcedimientosGenerales::erroresModelo($model->getErrors());
                Yii::$app->session->setFlash('error', 'Ocurrió un error al cargar los archivos: ' . $errorString);
            }

            return $this->redirect(['index']);

        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('uploaddata', [
                'model' => $model,
            ]);
        }
    }

}

