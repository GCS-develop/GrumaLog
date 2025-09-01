<?php

namespace frontend\modules\devolucion\controllers;

use frontend\models\Transferdevdocumentos;
use frontend\models\search\TransferdevdocumentosSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii;
use yii\data\ActiveDataProvider;
use frontend\models\Devoluciondocumentodetalle;

class TransferdevdocumentosController extends Controller
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
     * Lists all Transferdevdocumentos models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TransferdevdocumentosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Transferdevdocumentos model.
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
     * Creates a new Transferdevdocumentos model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Transferdevdocumentos();

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
     * Updates an existing Transferdevdocumentos model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id_transferencia)
    {
        $model = Transferdevdocumentos::findOne(['id_transferencia' => $id_transferencia]);

        if (!$model) {
            throw new NotFoundHttpException("No se encontró el documento con id_transferencia: $id_transferencia");
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Documento actualizado correctamente.');
            return $this->redirect(['viewtransferenciaocerp', 'id' => $model->id_transferencia]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }
    /**
     * Deletes an existing Transferdevdocumentos model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['ver-transferencia', 'id' => $id]); // o redirige donde prefieras
    }



    /**
     * Finds the Transferdevdocumentos model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Transferdevdocumentos the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Transferdevdocumentos::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionTransferencia($iddocumento)
    {
        $idtransferenciaerp = Transferdevdocumentos::find()
            ->select('id')
            ->where(['id_devoluciondocumento' => $iddocumento])
            ->scalar();

        if ($idtransferenciaerp === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $model = $this->findModel($idtransferenciaerp);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Transferencia actualizada correctamente.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('transferenciaerp', [
            'model' => $model,
        ]);
    }

    public function actionViewtransferenciaocerp($id)
    {
        // Obtener todos los documentos asociados al id_transferencia
        $documentos = Transferdevdocumentos::find()
            ->where(['id_transferencia' => $id])
            ->all();

        if (empty($documentos)) {
            throw new NotFoundHttpException("No se encontraron documentos para la transferencia #$id");
        }

        // Tomar el primero como principal para mostrar encabezado
        $documento = $documentos[0];

        // Obtener los ids de todos los documentos de devolución relacionados
        $idsDevoluciones = array_column($documentos, 'id_devoluciondocumento');

        // DataProvider para los detalles (devoluciones asociadas)
        $detalles = new ActiveDataProvider([
            'query' => \frontend\models\Devoluciondocumentodetalle::find()
                ->where(['id' => $idsDevoluciones]),
            'pagination' => false,
        ]);

        return $this->render('index_transferenciaocerp', [
            'documento' => $documento,   // Solo uno como referencia
            'detalles' => $detalles,     // Todos los detalles relacionados
        ]);
    }

    public function actionEjecutarTransferencia($id)
    {
        $transferencias = Transferdevdocumentos::find()
            ->where(['id_transferencia' => $id])
            ->all();

        if (empty($transferencias)) {
            Yii::$app->session->setFlash('error', 'No se encontraron datos de transferencia.');
            return $this->redirect(['viewtransferenciaocerp', 'id' => $id]);
        }

         if ($transferencias[0]->estado_envio === 'enviado') {
        Yii::$app->session->setFlash('warning', 'Esta transferencia ya fue enviada a Siesa.');
        return $this->redirect(['viewtransferenciaocerp', 'id' => $id]);

        
    }

        // Obtener los id_devoluciondocumento de las transferencias
        $idsDevoluciones = array_column($transferencias, 'id_devoluciondocumento');

        // Obtener detalles de devolución relacionados con esos documentos
        $detalles = \frontend\models\Devoluciondocumentodetalle::find()
            ->where(['id' => $idsDevoluciones])  // <-- nota: asegúrate que sea 'id_documento'
            ->all();

        if (empty($detalles)) {
            Yii::$app->session->setFlash('error', 'No se encontraron detalles para la transferencia.');
            return $this->redirect(['viewtransferenciaocerp', 'id' => $id]);
        }



        // Extraer información general (Documentos)
        $doc = $transferencias[0]; // Documento cabecera
        $json = [
            "Documentos" => [[
                "CENTRO DE OPERACION" => $doc->centro_operacion,
                "TIPO DE DOCUMENTO" => $doc->tipo_documento,
                "CONSECUTIVO DOCUMENTO" => $doc->consecutivo_documento,
                "FECHA DEL DOCUMENTO AAAMMDD" => $doc->fecha_documento,
                "TERCERO PROVEEDOR" => (string)$doc->tercero_proveedor,
                "NOTAS" => $doc->notas,
                "SUCURSAL PROVEEDOR" => $doc->sucursal_proveedor,
                "COMPRADOR" => (string)$doc->comprador,
                "CONSIGNACION" => (string)$doc->consignacion
            ]],
            "Movimientos" => []
        ];

        // Recorrer los detalles para armar la sección "Movimientos"
        foreach ($detalles as $detalle) {

    // Cálculo de equivalencia
    $equivalencia = 1;
    if ($detalle->unidadempaque && $detalle->unidadempaque->equivalencia > 0) {
        $equivalencia = $detalle->unidadempaque->equivalencia;
    }

    // Calcular cantidad en unidades (no en paquetes)
    $cantidadBase = $detalle->cantidadRegistrada * $equivalencia;

    // Costo unitario en base a UND
    $costoUnitario = $detalle->getCosto();

    // Valor bruto (ya multiplicado por cantidad)
    $valorBruto = number_format(round($cantidadBase * $costoUnitario), 2, '.', '');

    $json["Movimientos"][] = [
        "CENTRO DE OPERACION" => $doc->centro_operacion,
        "TIPO DE DOCUMENTO" => $doc->tipo_documento,
        "BODEGA" => '214',
        "MOTIVO" => $doc->motivo,
        "CENTRO DE OPERACION MOVIMIENTO" => $doc->centro_operacion,
        "UNIDAD DE MEDIDA" => "UND",  // ← forzado a unidad siempre
        "CANTIDAD BASE" => number_format($cantidadBase, 2, '.', ''),  // ← cantidad real
        "VALOR BRUTO" => $valorBruto,
        "ITEMS" => $detalle->item,
        "COLOR" => $detalle->color,
        "TALLA" => $detalle->talla
    ];
}

       //* // 👉 Mostrar el JSON en pantalla para depuración
    /*header('Content-Type: application/json');
    echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    Yii::$app->end();*/ // ← Detiene aquí antes de enviar a Siesa
//https://serviciosqa.siesacloud.com/api/siesa/v3.1/conectoresimportar

        // Configurar cliente HTTP (usa Guzzle por defecto)
        $client = new \yii\httpclient\Client([
            'transport' => 'yii\httpclient\CurlTransport'
        ]);

        $baseUrl = 'https://servicios.siesacloud.com/api/siesa/v3.1/conectoresimportar';
        $params = [
            'idCompania' => 8203,
            'idSistema' => 8203,
            'idDocumento' => 210985,
            'nombreDocumento' => 'DEVOLUCION DE COMPRAS DIRECTAS',
        ];

        $url = $baseUrl . '?' . http_build_query($params);

        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($url)
            ->addHeaders([
                'Content-Type' => 'application/json',
                'ConniKey' => '461ee4af939cfd45fe2a20e596c02346',
                'ConniToken' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1laWRlbnRpZmllciI6IjJhNjU1YjdjLTY1ODQtNGMyZS1iYTI3LTMwNGYxNjVmN2U0ZiIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcHJpbWFyeXNpZCI6IjVlNmE2OTgyLTMxMTEtNGY2OS1hMjViLWEzOWU1ODI5NmY2MiJ9.l78vnBKJJ15ND7ro-VE52RS7ChSYnIR8Qu8UpvR9obY',
            ])
            ->setOptions([
                CURLOPT_SSL_VERIFYPEER => false, // solo para entornos de pruebas
            ])
            ->setContent(json_encode($json))
            ->send();

        // Procesar respuesta
        $estadoEnvio = $response->isOk ? 1 : 0;
        $respuesta = $response->getContent();

        // Guardar resultado en cada fila
        foreach ($transferencias as $mov) {
            $mov->estado_envio = $estadoEnvio;
            $mov->respuesta_siesa = $respuesta;
            $mov->save(false); // sin validar para evitar errores por campos no requeridos
        }

        // Mostrar feedback al usuario
        if ($estadoEnvio === 1) {
            Yii::$app->session->setFlash('success', 'Transferencia enviada exitosamente a Siesa.');
        } else {
            Yii::$app->session->setFlash('error', 'Error al enviar a Siesa: ' . $respuesta);
        }

        // 🔁 Redirige a una vista resumen
        return $this->redirect(['transferencias-enviadas', 'id' => $id]);
    }

    public function actionTransferenciasEnviadas($id = null)
{
    $documentoActual = null;
    if ($id !== null) {
        $documentoActual = Transferdevdocumentos::findOne(['id_transferencia' => $id]);
    }

    // Subconsulta para obtener el ID más alto por cada id_transferencia
    $subquery = (new \yii\db\Query())
        ->select(['MAX(id) AS id'])
        ->from('transferdevdocumentos')
        ->groupBy('id_transferencia');

    // Buscar solo esos documentos (uno por transferencia)
    $documentosEnviados = Transferdevdocumentos::find()
        ->where(['id' => $subquery])
        ->orderBy(['id_transferencia' => SORT_DESC])
        ->all();

    return $this->render('transferencias_enviadas', [
        'documentoActual' => $documentoActual,
        'documentosEnviados' => $documentosEnviados,
    ]);
}


 public function actionVerTransferencia($id)
{
    // 1. Obtener los documentos asociados a la transferencia
    $documentos = Transferdevdocumentos::find()
        ->where(['id_transferencia' => $id])
        ->all();

    if (empty($documentos)) {
        throw new NotFoundHttpException("No se encontraron documentos para la transferencia #$id");
    }

    // 2. Tomar uno como referencia para el encabezado
    $documento = $documentos[0];

    // 3. ActiveDataProvider optimizado para la vista
    $dataProvider = new ActiveDataProvider([
        'query' => Devoluciondocumentodetalle::find()
            ->select(['devoluciondocumentodetalle.id', 'cantidadRegistrada', 'item', 'talla', 'color', 'codigoBarras'])
            ->innerJoin('transferdevdocumentos t', 't.id_devoluciondocumento = devoluciondocumentodetalle.id')
            ->where(['t.id_transferencia' => $id])
            ->orderBy(['devoluciondocumentodetalle.id' => SORT_ASC]),
        'pagination' => false, // importante para totales y envío a Siesa
    ]);

    // 4. Obtener los modelos del DataProvider para sumar cantidades
    $detalles = $dataProvider->getModels();

    // 5. Calcular la suma total de cantidadRegistrada
    $totalCantidad = array_sum(array_column($detalles, 'cantidadRegistrada'));

    // 6. Renderizar la vista
    return $this->render('index_transferenciaocerp', [
        'documento' => $documento,
        'detalles' => $dataProvider,
        'totalCantidad' => $totalCantidad,
    ]);
}


    // Editar la transferencia
    public function actionEditarTransferencia($id)
    {
        $modelo = Transferdevdocumentos::findOne($id);
        if (!$modelo) {
            throw new NotFoundHttpException("Transferencia no encontrada.");
        }

        if ($modelo->load(Yii::$app->request->post()) && $modelo->save()) {
            Yii::$app->session->setFlash('success', 'Transferencia actualizada correctamente.');
            return $this->redirect(['transferencias-enviadas']);
        }

        return $this->render('editar_transferencia', [
            'modelo' => $modelo,
        ]);
    }

    // Ver log de la transferencia (respuesta SIESA)
    public function actionLogTransferencia($id)
    {
        $modelo = Transferdevdocumentos::findOne($id);
        if (!$modelo) {
            throw new NotFoundHttpException("Transferencia no encontrada.");
        }

        return $this->render('log_transferencia', [
            'modelo' => $modelo,
            'log' => $modelo->respuesta_siesa, // Asegúrate de tener este campo
        ]);
    }

    // Reenviar transferencia a SIESA
    public function actionReenviarTransferencia($id)
    {
        $modelo = Transferdevdocumentos::findOne($id);
        if (!$modelo) {
            throw new NotFoundHttpException("Transferencia no encontrada.");
        }

        // Aquí deberías construir y enviar el JSON como ya lo hicimos antes
        $json = json_encode($modelo->getTransferenciaJson(), JSON_UNESCAPED_UNICODE);

        // Endpoint y envío
        $client = new \yii\httpclient\Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl('https://serviciosqa.siesacloud.com/api/siesa/v3.1/conectoresimportar?idCompania=8203&idSistema=8203&idDocumento=210985&nombreDocumento=DEVOLUCION DE COMPRAS DIRECTAS')
            ->addHeaders(['Content-Type' => 'application/json'])
            ->setContent($json)
            ->send();

        if ($response->isOk) {
            $modelo->estado_envio = 1;
            $modelo->respuesta_siesa = $response->getContent();
            $modelo->save(false);
            Yii::$app->session->setFlash('success', 'Transferencia reenviada correctamente.');
        } else {
            $modelo->respuesta_siesa = $response->getContent();
            $modelo->save(false);
            Yii::$app->session->setFlash('error', 'Error al reenviar a SIESA.');
        }

        return $this->redirect(['transferencias-enviadas']);
    }



   public function actionAnularTransferencia($id)
{
    $transferencias = Transferdevdocumentos::find()
        ->where(['id_transferencia' => $id])
        ->all();

    if (empty($transferencias)) {
        Yii::$app->session->setFlash('error', 'No se encontraron documentos asociados a la transferencia.');
        return $this->redirect(['transferencias-enviadas']);
    }

    $anulados = 0;
    foreach ($transferencias as $transferencia) {
        $transferencia->estado_envio = 9;
        $transferencia->respuesta_siesa = 'ANULADO MANUALMENTE';
        $transferencia->usuario_anula = Yii::$app->user->id;
        $transferencia->fecha_anulacion = new \yii\db\Expression('GETDATE()'); // SQL Server
        $transferencia->observacion_anulacion = 'Anulado desde la interfaz por el usuario.';

        if ($transferencia->save(false)) {
            $anulados++;
        }
    }

    if ($anulados > 0) {
        Yii::$app->session->setFlash('success', "Se anularon $anulados documentos correctamente.");
    } else {
        Yii::$app->session->setFlash('error', 'No se pudo anular ningún documento.');
    }

    return $this->redirect(['viewtransferenciaocerp', 'id' => $id]);
}


}
