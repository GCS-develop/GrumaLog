<?php

namespace frontend\modules\productostiquetesprecio\controllers;

// use Exception;
use common\models\ProcedimientosGenerales;
use common\models\User;
use frontend\models\FileAgendaInput;
use frontend\models\Productostiquetesprecio;
use frontend\models\search\ProductostiquetesprecioSearch;
use Yii;
use yii\db\Exception;
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
        $idusuario = Yii::$app->user->id;
        $modeluser = User::findOne(['id' => $idusuario]);
        $bodega = $modeluser->bodegarecibir->nombre;

        if ($idusuario == 17) {
            $bodega = null;
            // var_dump($bodega);
            // die();
        }

        $searchModel = new ProductostiquetesprecioSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $bodega);


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
    // public function actionCreate()
    // {
    //     $model = new Productostiquetesprecio();

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
        $model = new Productostiquetesprecio();

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
     * Updates an existing Productostiquetesprecio model.
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
        Yii::trace('Iniciando la impresion de tiquetes.', __METHOD__);

        $modelo = $this->findModel($id);
        $idusuario = Yii::$app->user->id;
        $modeluser = User::findOne(['id' => $idusuario]);

        if (!$modeluser) {
            Yii::$app->session->setFlash('error', 'Usuario no encontrado.');
            return $this->redirect(['index']);
        }

        // Verificar si bodegarecibir está definido
        if (!$modeluser->bodegarecibir) {
            Yii::$app->session->setFlash('error', 'No se encontró una bodega asociada al usuario.');
            return $this->redirect(['index']);
        }


        $impresora = $modeluser->bodegarecibir->impresorapaxar;

        if ($modeluser && $modeluser->bodegarecibir) {
            $impresora = $modeluser->bodegarecibir->impresorapaxar;

            // Verificar si la impresora está definida
            if ($impresora) {
                $epl = $impresora->tipo === 'epl';
            } else {
                Yii::$app->session->setFlash('error', 'No se encontró una impresora asociada.');
                Yii::error('No se encontró una impresora asociada.', __METHOD__);
                return $this->redirect(['index']);
            }
        } else {
            Yii::$app->session->setFlash('error', 'No se encontró la configuración de la impresora.');
            Yii::error('No se encontró la configuración de la impresora.', __METHOD__);
            return $this->redirect(['index']);
        }

        $totalStickers = $modelo->existencia; // Número total de stickers a imprimir.

        if ($totalStickers <= 0) {
            Yii::$app->session->setFlash('error', 'Cantidad inválida para imprimir.');
            return $this->redirect(['index']);
        }

        // Construir el código ZPL dinámico.
        $zpl = "^XA\n";
        $x = 30; // Posición inicial horizontal (en la primera columna).
        $y = 80; // Posición inicial vertical (en la primera fila).
        $incrementoX = 270; // Separación horizontal entre stickers (movemos hacia la siguiente columna).
        $incrementoY = 100; // Separación vertical entre stickers (bajamos por la columna).
        $lineasPorColumna = 1; // Cantidad de stickers por columna.
        $saltoColumnaStikers = 3; // Número de stickers por "gran columna" (6 stickers).
        $yInicio = $y; // Guardar la posición de inicio para saltar correctamente después de 6 stickers.
        $xInicio = $x;
        $envio = false;
        $contenido = ''; // Contenido a enviar a la impresora.
        $config = [
            'tipo' => $impresora->tipo,
            'ip' => $impresora->ip, // IP de tu impresora
            'puerto' => $impresora->puerto,       // Puerto (por defecto: 9100)
            'recurso' => $impresora->recurso,
        ];

        // Iterar según la existencia (cantidad de stickers).
        for ($i = 0; $i < $totalStickers; $i++) {
            // Añadir solo el precio en la etiqueta.
            if ($epl) {
                // Generar etiqueta en formato EPL.
                $contenido .= "A{$x},{$y},0,7,1,1,N,\"" . number_format($modelo->precio ?? 0, 0, ',', '.') . "\"\n";
                // Ajustar posición.
                $y += $incrementoY;

                // Si hemos llegado al final de la columna, movernos a la siguiente columna.
                if (($i + 1) % $lineasPorColumna == 0) {
                    $y = $yInicio; // Reiniciar la posición vertical.
                    $x += $incrementoX; // Mover a la siguiente columna.
                }

                // Enviar a la impresora después de cada gran bloque o al final.
                if (($i + 1) % $saltoColumnaStikers == 0 || ($i + 1) == $totalStickers) {
                    $contenido = "N\n" . $contenido . "P1\n"; // Iniciar nuevo bloque y enviar.
                    $config['tipo'] = 'recurso';
                    $envio = $this->enviarImpresora($contenido, $config); // Enviar a la impresora.
                    $contenido = ''; // Reiniciar el contenido para el siguiente bloque.
                    $x = $xInicio; // Reiniciar la posición horizontal.
                    $y = $yInicio; // Reiniciar la posición vertical.
                }
            } else {
                // $zpl .= "^FO{$x},{$y}^A0N,50,50^FD$" . number_format($modelo->precio ?? 0, 0, ',', '.') . "^FS\n";
                // $y1 = $y + 20;
                // // Tamaño de la etiqueta

                // // Margen inicial
                // $xMargen = 1;

                // // Código de barras
                // $codigoBarra = "9931404602310"; // Código de ejemplo
                // $alturaCodigoBarra = 50;
                // $anchoModulo = 2;
                // $anchoMaximoCodigo = 200;

                // // Ajustar ancho del módulo para que no exceda el espacio disponible
                // $longitudCodigo = strlen($codigoBarra);
                // $anchoCodigo = $longitudCodigo * $anchoModulo;

                // if ($anchoCodigo > $anchoMaximoCodigo) {
                //     // Calcular el nuevo ancho del módulo para que no exceda el máximo
                //     $anchoModulo = $anchoMaximoCodigo / $longitudCodigo;
                // }

                // // Código de barras con altura uniforme
                // // $zpl .= "^FO0,30^ BY3 ^BCN,50 ,,,, ^FD^FS\n";
                // $zpl .= "^FO0,0^BY1,3,55^FT20,100^BCN,,Y,N,,A ,^FD{$codigoBarra}^FS\n";

                // $y += $alturaCodigoBarra + 10;


                // // Texto debajo del código de barras
                // // $zpl .= "^FO{$xMargen},{$y}^A0N,25,25^FD{$codigoBarra}^FS\n";
                // $y += 35;

                // // Texto del producto
                // $zpl .= "^FO{$xMargen},{$y}^A0N,10,20^FDSHORT HOMBRE KAKI^FS\n";
                // $y += 15;
                // $zpl .= "^FO{$xMargen},{$y}^A0N,10,30^FD242407^FS\n";
                // $y += 10;
                // $zpl .= "^FO{$xMargen},{$y}^A0N,10,30^FD314046^FS\n";
                // $y += 10;
                // $zpl .= "^FO{$xMargen},{$y}^A0N,10,30^FDSURTIDO^FS\n";
                // $y += 15;

                // // Texto "Unidad a $49.900"
                // $zpl .= "^FO{$xMargen},{$y}^A0N,25,25^FDUnidad   a $49900^FS\n";
                // $y += 15;

                // // Talla y precio grande
                // $talla = "30";
                // $precio = "$49.900";
                // $zpl .= "^FO{$xMargen},{$y}^A0N,60,60^FD{$talla}^FS\n";
                // $zpl .= "^FO100,{$y}^A0N,60,60^FD{$precio}^FS\n";

                // // Texto vertical "HERPO"
                // $zpl .= "^FO200,25^A0N,30,30^FDH^FS\n";
                // $zpl .= "^FO200,50^A0N,30,30^FDE^FS\n";
                // $zpl .= "^FO200,80^A0N,30,30^FDR^FS\n";
                // $zpl .= "^FO200,110^A0N,30,30^FDP^FS\n";
                // $zpl .= "^FO200,140^A0N,30,30^FDO^FS\n";



                $zpl .= "^FO{$x},{$y}^A0N,50,50^FD" . number_format($modelo->precio ?? 0, 0, ',', '.') . "^FS\n";
                // $zpl .= "^FO{$x},{$y1}^B7N,N, 10, 5, 5, hola^FD" . (int)($modelo->codigoBarra ?? 0) . "^FS\n";

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

                    //    Impresora con ip

                    $envio = $this->enviarImpresora($zpl, $config);

                    // Reiniciar el ZPL para la siguiente columna de stickers, si quedan más stickers.
                    if (($i + 1) < $totalStickers) {
                        $zpl = "^XA\n"; // Reiniciar la impresión ZPL.
                    }
                    // Restablecer las posiciones para el siguiente bloque de 9 stickers.
                    $x = $xInicio; // Reiniciar la posición horizontal.
                    $y = $yInicio; // Restablecer la posición vertical a la columna inicial.
                }
            }
        }
        if ($envio['status'] == 'success') {
            Yii::$app->session->setFlash(
                'success',
                'Imprimiendo : ' . $modelo->existencia . ' codigos del ean: ' . $modelo->codigoBarra .
                ' en la tienda: ' . $impresora->bodega->nombre . ' recurso: ' . $impresora->recurso . ' tipo:  ' . $impresora->tipo
                // . $zpl
            );

            Yii::trace('Imprimiendo : ' . $modelo->existencia . ' codigos del ean: ' . $modelo->codigoBarra .
                ' en la tienda: ' . $impresora->bodega->nombre . ' recurso: ' . $impresora->recurso . ' tipo:' . $impresora->tipo);

        } else {
            Yii::$app->session->setFlash('error', 'Imprimiendo : ' . $modelo->existencia . ' codigos del ean: ' . $modelo->codigoBarra .
                ' en la tienda: ' . $impresora->bodega->nombre . ' recurso: ' . $impresora->recurso . ' tipo: ' . $impresora->tipo);

            Yii::error('Error Imprimiendo : ' . $modelo->existencia . ' codigos del ean: ' . $modelo->codigoBarra .
                ' en la tienda: ' . $impresora->bodega->nombre . ' recurso: ' . $impresora->recurso . ' tipo ' . $impresora->tipo);

        }
        return $this->redirect(['index']);
    }

    public function imprimirEtiquetas($inputValue, $modelo)
    {
        // Obtener el usuario actual
        $idusuario = Yii::$app->user->id;
        $modeluser = User::findOne(['id' => $idusuario]);

        if (!$modeluser) {
            // Yii::$app->session->setFlash('error', 'Usuario no encontrado.');
            return $this->asJson(['status' => 'error', 'message' => 'Usuario no encontrado.']);
            // return $this->redirect(['index']);
        }

        // Verificar si bodegarecibir está definido
        if (!$modeluser->bodegarecibir) {
            // Yii::$app->session->setFlash('error', 'No se encontró una bodega asociada al usuario.');
            return $this->asJson(['status' => 'error', 'message' => 'No se encontró una bodega asociada al usuario.']);
            // return $this->redirect(['index']);
        }

        $impresora = $modeluser->bodegarecibir->impresorapaxar;

        if (!$impresora) {
            // Yii::$app->session->setFlash('error', 'No se encontró una impresora asociada.');
            Yii::error('No se encontró una impresora asociada.', __METHOD__);
            return $this->asJson(['status' => 'error', 'message' => 'No se encontró una impresora asociada.']);
            // return $this->redirect(['index']);
        }

        // Verificar tipo de impresora es epl
        $epl = ($impresora->tipo === 'epl');

        // Configuración de impresora
        $config = [
            'tipo' => $impresora->tipo,
            'ip' => $impresora->ip,
            'puerto' => $impresora->puerto,
            'recurso' => $impresora->recurso,
        ];

        // Variables de posición de etiquetas
        $x = 30;
        $y = 80;
        $incrementoX = 270;
        $incrementoY = 100;
        $lineasPorColumna = 1;
        $saltoColumnaStikers = 3;
        $yInicio = $y;
        $xInicio = $x;
        $envio = false;
        $contenido = '';
        $zpl = "^XA\n";

        // Imprimir etiquetas
        for ($i = 0; $i < $inputValue; $i++) {
            if ($epl) {
                // Generar etiqueta en formato EPL
                $contenido .= "A{$x},{$y},0,7,1,1,N,\"" . number_format($modelo->precio ?? 0, 0, ',', '.') . "\"\n";
                $y += $incrementoY;

                if (($i + 1) % $lineasPorColumna == 0) {
                    $y = $yInicio;
                    $x += $incrementoX;
                }

                if (($i + 1) % $saltoColumnaStikers == 0 || ($i + 1) == $inputValue) {
                    $contenido = "N\n" . $contenido . "P1\n";
                    $config['tipo'] = 'recurso';
                    $envio = $this->enviarImpresora($contenido, $config);
                    $contenido = '';
                    $x = $xInicio;
                    $y = $yInicio;
                }
            } else {
                // Generar etiqueta en formato ZPL
                $y1 = $y + 20;
                $zpl .= "^FO{$x},{$y}^A0N,50,50^FD" . number_format($modelo->precio ?? 0, 0, ',', '.') . "^FS\n";
                // $zpl .= "^FO{$x},{$y1}B3N,N,100,Y,N^FD" . (int) ($modelo->codigoBarra ?? 0) . "^FS\n"; 
                $y += $incrementoY;

                if (($i + 1) % $lineasPorColumna == 0) {
                    $y = $yInicio;
                    $x += $incrementoX;
                }

                if (($i + 1) % $saltoColumnaStikers == 0 || ($i + 1) == $inputValue) {
                    $zpl .= "^XZ";
                    $envio = $this->enviarImpresora($zpl, $config);
                    if (($i + 1) < $inputValue) {
                        $zpl = "^XA\n";
                    }
                    $x = $xInicio;
                    $y = $yInicio;
                }
            }
        }

        if ($envio['status'] == 'success') {
            // var_dump($envio['message']);
            // die('?-');
            // Yii::$app->session->setFlash('success', 'Imprimiendo : ' . $inputValue . ' codigos del ean: ' . $modelo->codigoBarra . ' en la tienda: ' . $impresora->bodega->nombre . ' tipo: ' . $impresora->tipo);
            Yii::trace('Imprimiendo : ' . $inputValue . ' codigos del ean: ' . $modelo->codigoBarra . ' en la tienda: ' . $impresora->bodega->nombre . ' recurso: ' . $impresora->recurso
                . ' tipo: ' . $impresora->tipo);

            return $this->asJson([
                'status' => 'success',
                'message' => 'Imprimiendo : ' . $inputValue . ' codigos del ean: ' . $modelo->codigoBarra . ' en la tienda: ' . $impresora->bodega->nombre . ' recurso: ' . $impresora->recurso
                    . ' tipo: ' . $impresora->tipo
            ]);

        } else {
            Yii::error('Error Imprimiendo : ' . $inputValue . ' codigos del ean: ' . $modelo->codigoBarra . ' en la tienda: ' . $impresora->bodega->nombre . ' recurso: ' . $impresora->recurso
                . ' tipo: ' . $impresora->tipo);

            return $this->asJson(['status' => 'error', 'message' => $envio['message']]);

        }
    }

    public function actionPrintajax()
    {
        $request = Yii::$app->request;

        if ($request->isPost) {
            Yii::trace('Iniciando la impresion de tiquetes.', __METHOD__);
            $id = $request->post('id');
            $inputValue = $request->post('input');

            if (!is_numeric($inputValue) || $inputValue <= 0) {
                // Yii::$app->session->setFlash('error', 'Cantidad inválida para imprimir.');
                return $this->asJson(['status' => 'error', 'message' => 'Cantidad inválida para imprimir.']);
            }

            $modelo = $this->findModel($id);

            $this->imprimirEtiquetas($inputValue, $modelo);

        }
        // return $this->redirect(['index']);
    }



    private function enviarImpresora($zpl, $config)
    {
        $tipo = $config['tipo'];
        $ip = $config['ip'] ?? null;
        $puerto = $config['puerto'] ?? 9100;
        $recurso = $config['recurso'] ?? null;
        Yii::$app->session->removeAllFlashes();

        Yii::trace("-----------Impresión enviada a $ip: con codigo:  $zpl", __METHOD__);
        // var_dump($zpl);
        // die();
        if ($tipo == 'ip') {

            //             $zpl = " 
// ^XA

            // ^FO0,0
// ^BY1.4,2.8,50
// ^FT5,75
// ^A0,50,50
// ^B3N,N,50,Y,N
// ^FD9931404602310^FS

            // ^FO210,25^A0N,15,15^FDH^FS 
// ^FO210,45^A0N,15,15^FDE^FS 
// ^FO210,65^A0N,15,15^FDR^FS 
// ^FO210,85^A0N,15,15^FDP^FS 
// ^FO210,105^A0N,15,15^FDO^FS 

            // ^FO5,95^A0N,20,20
// ^FDSHORT HOMBRE KAKI^FS 
// ^FO5,117^A0N,20,20^FD080^FS 
// ^FO130,120^A0N,20,20^FDvinotinto^FS 

            // ^FO2,140^A0N,25,20^FD314046^FS 
// ^FO95,145^A0N,17,15^FDUnidad a $49900^FS 


            // ^FO30,170^A0N,28,28^FD36^FS 
// ^FO80,168^A0N,35,35^FD33.900^FS 





            // ^XZ
//              ";

            //  https://labelary.com/viewer.html

            $socket = @fsockopen($ip, $puerto, $errno, $errstr, 10);
            // var_dump($socket);die($zpl);

            if (!$socket) {

                Yii::error("Error al conectar a $ip:$puerto: $errstr ($errno)", __METHOD__);
                Yii::$app->session->setFlash('error', "No se pudo conectar a la impresora, revisar que este conectada por favor!, Ip: $ip:$puerto Error: $errstr ($errno)");

                return ['status' => 'error', 'message' => "No se pudo conectar a la impresora, revisar que este conectada por favor!, Ip: $ip:$puerto Error: $errstr ($errno)"];
            }

            fwrite($socket, $zpl);

            // var_dump($zpl);die();
            fclose($socket);

            Yii::info("Impresión enviada a $ip:$puerto", __METHOD__);
            Yii::$app->session->setFlash('success', "Impresión enviada correctamente a $ip:$puerto.");
            return ['status' => 'success', 'message' => "Impresión enviada correctamente a $ip:$puerto."];
        }

        if ($tipo === 'recurso') {
            Yii::trace("Inicio de impresión enviada al recurso:  $recurso", __METHOD__);

            // Crear el directorio temporal si no existe
            $tempDir = Yii::getAlias('@frontend') . '/temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true); // Crear con permisos recursivos
            }

            // Crear el archivo temporal dentro de ./frontend/temp
            $tempFile = $tempDir . '/zpl_' . uniqid() . '.tmp';
            file_put_contents($tempFile, $zpl);

            // Construir el comando con la IP primero y luego el recurso compartido
            $command = sprintf(
                'print %s /D:%s "%s"',
                $ip, // La dirección IP (sin escapar, ya es válida)
                '\\\\' . str_replace('\\', '\\\\', ltrim($recurso, '\\')), // Recurso compartido (doble \\ inicial)
                $tempFile // El archivo temporal, ahora entre comillas
            );

            $userId = Yii::$app->user->id;

            // if ($userId == 17) {

            //     var_dump($zpl);
            //     die();

            // }

            // Ejecutar el comando
            exec($command, $output, $returnVar);

            unlink($tempFile);

            if ($returnVar !== 0) {
                $errorMessage = "Error al imprimir en $recurso: " . implode("\n", $output);
                Yii::error($errorMessage, __METHOD__);
                Yii::error("Error al imprimir en $recurso: " . implode("\n", $output), __METHOD__);
                // Notificar al usuario del error
                Yii::$app->session->setFlash('error', $errorMessage);
                // return false;
                return ['status' => 'error', 'message' => $errorMessage];
            }

            Yii::trace("Impresión enviada a $recurso, correctamente!", __METHOD__);

            // return true;
            return ['status' => 'success', 'message' => "Impresión enviada a $recurso, correctamente!"];
        }

        Yii::error("Tipo de impresora no reconocido: $tipo", __METHOD__);
        // return false;
        return ['status' => 'error', 'message' => 'error!'];
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

            $nombreArchivoSinExtension = $model->archivo->baseName;


            try {
                // Llamada al método de carga del archivo
                $respuesta = Productostiquetesprecio::upload($model->archivo);

                if ($respuesta['estado']) {

                    Yii::$app->session->setFlash('success', "El Archivo se ha cargado el archivo $nombreArchivoSinExtension  correctamente, con "
                        . $respuesta['rows'] . " filas esperadas y" . $respuesta['insert'] . "insertadas");

                    return $this->redirect(['index']);

                } else {

                    $errorString = ProcedimientosGenerales::erroresModelo($model->getErrors());

                    Yii::$app->session->setFlash('error', "Ocurrió un error al cargar el archivo:  $nombreArchivoSinExtension  con: " .
                        $respuesta['rows'] . " filas." . $errorString . $respuesta['insert'] . ' insert');

                    Yii::error("Ocurrió un error al cargar el archivo:  $nombreArchivoSinExtension  con: " .
                        $respuesta['rows'] . " filas." . $errorString . $respuesta['insert'] . ' insert');

                }
            } catch (\Exception $e) {
                // Capturar cualquier error inesperado durante la carga del archivo
                Yii::$app->session->setFlash('error', "Ocurrió un error al intentar cargar el archivo: $nombreArchivoSinExtension. Detalles: " . $e->getMessage());
                Yii::error("Error inesperado al cargar el archivo $nombreArchivoSinExtension. Detalles: " . $e->getMessage() . $respuesta['insert'] . ' insert');
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



// ^FO100,0^BY2,3,50
// ^FT260,70^BCN,,Y,N,,A,
// ^FD9931404602310^FS 

// ^FO10,10^BY2,3,50
// ^FT520,70^BCN,,Y,N,,A,
// ^FD9931404602310^FS 



// ----


// ^FO0,0
// ^BY1.2,2.5,40
// ^FT5,50
// ^A0,20,20
// ^BCN,,Y,N,,A,
// ^FD9931404602310^FS