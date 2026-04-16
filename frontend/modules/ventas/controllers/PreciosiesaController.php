<?php

namespace frontend\modules\ventas\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ArrayDataProvider;
use yii\filters\VerbFilter;
use common\models\User;
use frontend\models\forms\PrecioItemForm;

class PreciosiesaController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'printajax' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new PrecioItemForm();

        // DataProvider vacío por defecto (pantalla inicial)
        $dataProvider = new ArrayDataProvider([
            'allModels' => [],
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'attributes' => [
                    'item',
                    'referencia',
                    'detalleExt1',
                    'detalleExt2',
                    'estadoPrecio',
                    'precio',
                    'fechaActivacion',
                ],
                'defaultOrder' => ['item' => SORT_ASC],
            ],
        ]);

        // Solo consultamos si viene GET y el form valida
        if ($model->load($this->request->get()) && $model->validate()) {
            $term = trim((string)$model->term);
            if ($term !== '') {
                $rows = Yii::$app->siesaPrecio->buscarConHistorial($term, 7, '001');

                $dataProvider = new ArrayDataProvider([
                    'allModels' => $rows,
                    'pagination' => ['pageSize' => 50],
                    'sort' => [
                        'attributes' => [
                            'item',
                            'referencia',
                            'detalleExt1',
                            'detalleExt2',
                            'estadoPrecio',
                            'precio',
                            'fechaActivacion',
                        ],
                        'defaultOrder' => ['item' => SORT_ASC],
                    ],
                ]);
            }
        }

        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionPrintajax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $rowidItem = (int)Yii::$app->request->post('rowidItem');
        $cantidad  = (int)Yii::$app->request->post('input');
        $precio    = Yii::$app->request->post('precio');

        if ($rowidItem <= 0) {
            return ['status' => 'error', 'message' => 'rowidItem inválido'];
        }
        if ($cantidad <= 0) {
            return ['status' => 'error', 'message' => 'Cantidad inválida'];
        }

        // Usar el precio enviado desde la vista (precio1 o precio2 según el botón pulsado)
        if ($precio !== null && (float)$precio > 0) {
            $precioFinal = (float)$precio;
        } else {
            // Fallback: consultar el precio vigente/futuro más cercano
            $precioRow = Yii::$app->siesaPrecio->precioImprimirPorRowidItem($rowidItem, 7, '001');
            if (!$precioRow || empty($precioRow['precio'])) {
                return ['status' => 'error', 'message' => 'No se encontró precio vigente/futuro para este ítem'];
            }
            $precioFinal = (float)$precioRow['precio'];
        }

        $modelo = (object)[
            'precio' => $precioFinal,
            'codigoBarra' => (string)$rowidItem,
        ];

        return $this->imprimirEtiquetas($cantidad, $modelo);
    }

    private function imprimirEtiquetas($inputValue, $modelo)
    {
        $idusuario = Yii::$app->user->id;
        $modeluser = User::findOne(['id' => $idusuario]);

        if (!$modeluser) {
            return $this->asJson(['status' => 'error', 'message' => 'Usuario no encontrado.']);
        }

        if (!$modeluser->bodegarecibir) {
            return $this->asJson(['status' => 'error', 'message' => 'No se encontró una bodega asociada al usuario.']);
        }

        $impresora = $modeluser->bodegarecibir->impresorapaxar;

        if (!$impresora) {
            Yii::error('No se encontró una impresora asociada.', __METHOD__);
            return $this->asJson(['status' => 'error', 'message' => 'No se encontró una impresora asociada.']);
        }

        $epl = ($impresora->tipo === 'epl');

        $config = [
            'tipo' => $impresora->tipo,
            'ip' => $impresora->ip,
            'puerto' => $impresora->puerto,
            'recurso' => $impresora->recurso,
        ];

        $x = 30;
        $y = 80;
        $incrementoX = 270;
        $incrementoY = 100;
        $lineasPorColumna = 1;
        $saltoColumnaStikers = 3;
        $yInicio = $y;
        $xInicio = $x;

        $envio = ['status' => 'error', 'message' => 'No se envió nada'];
        $contenido = '';
        $zpl = "^XA\n";

        for ($i = 0; $i < $inputValue; $i++) {
            if ($epl) {
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
                $zpl .= "^FO{$x},{$y}^A0N,50,50^FD" . number_format($modelo->precio ?? 0, 0, ',', '.') . "^FS\n";
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

        if (($envio['status'] ?? 'error') === 'success') {
            return $this->asJson([
                'status' => 'success',
                'message' => 'Imprimiendo : ' . $inputValue .
                    ' codigo: ' . ($modelo->codigoBarra ?? '-') .
                    ' en la tienda: ' . $impresora->bodega->nombre .
                    ' recurso: ' . $impresora->recurso .
                    ' tipo: ' . $impresora->tipo
            ]);
        }

        return $this->asJson([
            'status' => 'error',
            'message' => $envio['message'] ?? 'Error imprimiendo'
        ]);
    }

    private function enviarImpresora($zpl, $config)
    {
        $tipo = $config['tipo'];
        $ip = $config['ip'] ?? null;
        $puerto = $config['puerto'] ?? 9100;
        $recurso = $config['recurso'] ?? null;

        Yii::$app->session->removeAllFlashes();

        if ($tipo == 'ip') {
            $socket = @fsockopen($ip, $puerto, $errno, $errstr, 10);

            if (!$socket) {
                Yii::error("Error al conectar a $ip:$puerto: $errstr ($errno)", __METHOD__);
                return ['status' => 'error', 'message' => "No se pudo conectar a la impresora. Ip: $ip:$puerto Error: $errstr ($errno)"];
            }

            fwrite($socket, $zpl);
            fclose($socket);

            return ['status' => 'success', 'message' => "Impresión enviada correctamente a $ip:$puerto."];
        }

        if ($tipo === 'recurso') {
            $tempDir = Yii::getAlias('@frontend') . '/temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $tempFile = $tempDir . '/zpl_' . uniqid() . '.tmp';
            file_put_contents($tempFile, $zpl);

            $command = sprintf(
                'print %s /D:%s "%s"',
                $ip,
                '\\\\' . str_replace('\\', '\\\\', ltrim($recurso, '\\')),
                $tempFile
            );

            exec($command, $output, $returnVar);
            @unlink($tempFile);

            if ($returnVar !== 0) {
                $errorMessage = "Error al imprimir en $recurso: " . implode("\n", $output);
                Yii::error($errorMessage, __METHOD__);
                return ['status' => 'error', 'message' => $errorMessage];
            }

            return ['status' => 'success', 'message' => "Impresión enviada a $recurso, correctamente!"];
        }

        return ['status' => 'error', 'message' => 'Tipo de impresora no reconocido'];
    }
}
