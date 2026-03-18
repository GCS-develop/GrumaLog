<?php

namespace frontend\modules\compras\controllers;

use Yii;
use frontend\models\Comprasimportacion;
use frontend\models\Comprasimportaciondetalle;
use frontend\models\search\ComprasimportacionSearch;
use frontend\models\FileAgendaInput;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\web\UploadedFile;
use yii\data\ActiveDataProvider;
use frontend\components\SiesaService;

class ImportacionController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'delete'                => ['POST'],
                    'envio-siesa'           => ['POST'],
                    'asignar-consecutivo'   => ['POST'],
                    'generar-txt-carvajal'  => ['GET'],
                    'descargar-formato'     => ['GET'],
                ],
            ],
        ]);
    }

    // ----------------------------------------------------------
    // INDEX — Lista de archivos subidos
    // ----------------------------------------------------------
    public function actionIndex()
    {
        $searchModel  = new ComprasimportacionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // ----------------------------------------------------------
    // UPLOAD — Modal para subir Excel
    // ----------------------------------------------------------
    public function actionUpload()
    {
        $model = new FileAgendaInput();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->archivo = UploadedFile::getInstance($model, 'archivo');
            $respuesta = Comprasimportaciondetalle::upload($model->archivo);

            if ($respuesta === false) {
                Yii::$app->session->setFlash('error',
                    'Ocurrió un error al cargar el archivo. Verifique que tenga las columnas CC y UDS.'
                );
            } else {
                $registros = $respuesta['registros'];
                $sinCosto  = $respuesta['sinCosto'];

                Yii::$app->session->setFlash('success',
                    "<i class='fas fa-check-circle mr-1'></i> Archivo cargado correctamente. "
                    . "<strong>{$registros} registros</strong> importados."
                );

                if ($sinCosto > 0) {
                    Yii::$app->session->setFlash('warning',
                        "<i class='fas fa-exclamation-triangle mr-1'></i> "
                        . "<strong>{$sinCosto} registro(s)</strong> tienen <strong>Costo = 0</strong> o sin costo. "
                        . "Revisa el archivo antes de enviar a SIESA o Carvajal."
                    );
                }
            }
            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('uploaddata', ['model' => $model]);
        }
    }

    // ----------------------------------------------------------
    // VIEW — Detalle de una importación
    // ----------------------------------------------------------
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $dataProvider = new ActiveDataProvider([
            'query'      => Comprasimportaciondetalle::find()->where(['idImportacion' => $id]),
            'pagination' => ['pageSize' => 50],
            'sort'       => ['defaultOrder' => ['id' => SORT_ASC]],
        ]);

        return $this->render('view', [
            'model'        => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    // ----------------------------------------------------------
    // DELETE — Elimina cabecera + detalles
    // ----------------------------------------------------------
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        Comprasimportaciondetalle::deleteAll(['idImportacion' => $model->id]);
        $model->delete();
        Yii::$app->session->setFlash('success', 'Registro eliminado correctamente.');
        return $this->redirect(['index']);
    }

    // ----------------------------------------------------------
    // ENVÍO A SIESA — construye JSON y llama al conector
    // ----------------------------------------------------------
    public function actionEnvioSiesa($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model  = $this->findModel($id);
        $post   = Yii::$app->request->post();

        // Validar campos requeridos del formulario
        $campos = [
            'tipoDocumento'    => 'Tipo de documento',
            'fechaDocumento'   => 'Fecha del documento',
            'terceroComprador' => 'Tercero comprador',
            'terceroProveedor' => 'Tercero proveedor',
            'sucursalProveedor'=> 'Sucursal del proveedor',
            'condicionPago'    => 'Condición de pago',
            'bodega'           => 'Bodega',
        ];
        foreach ($campos as $key => $label) {
            if (empty($post[$key])) {
                return ['success' => false, 'message' => "El campo «$label» es requerido."];
            }
        }

        $resultado = SiesaService::enviar((int)$id, $post);

        // Guardar estado — truncar a 500 chars para no exceder la columna NVARCHAR
        $mensajeSiesa = is_array($resultado['message'])
            ? implode(' | ', array_map('strval', $resultado['message']))
            : (string)$resultado['message'];
        $mensajeSiesa = mb_substr($mensajeSiesa, 0, 500);
        try {
            $jsonEnviado   = $resultado['jsonEnviado'] ?? null;
            $jsonRespuesta = isset($resultado['response'])
                ? json_encode($resultado['response'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                : null;
            $tipoDoc = ($resultado['ok'] && !empty($resultado['tipoDoc']))
                ? mb_substr((string)$resultado['tipoDoc'], 0, 10) : null;
            $numDoc  = ($resultado['ok'] && !empty($resultado['numDoc']))
                ? mb_substr((string)$resultado['numDoc'],  0, 20) : null;

            // updateAll para campos simples (no NVARCHAR(MAX))
            Comprasimportacion::updateAll([
                'estadoSiesa'  => $resultado['ok'] ? 1 : 0,
                'mensajeSiesa' => $mensajeSiesa,
                'tipoDocSiesa' => $tipoDoc,
                'numDocSiesa'  => $numDoc,
            ], ['id' => (int)$id]);

            // Raw SQL para NVARCHAR(MAX) — Yii2 SQLSRV no bindea bien estos tipos
            Yii::$app->db->createCommand(
                "UPDATE comprasimportacion
                 SET jsonEnviadoSiesa   = :je,
                     jsonRespuestaSiesa = :jr
                 WHERE id = :id"
            )->bindValue(':je',  $jsonEnviado,   \PDO::PARAM_STR)
             ->bindValue(':jr',  $jsonRespuesta,  \PDO::PARAM_STR)
             ->bindValue(':id',  (int)$id,         \PDO::PARAM_INT)
             ->execute();

            // Guardar en tabla de log
            Yii::$app->db->createCommand(
                "INSERT INTO comprasimportacion_siesa_log
                    (idImportacion, tipoDoc, consecutivo, estadoEnvio, mensaje, jsonEnviado, jsonRespuesta, idUsuario)
                 VALUES
                    (:imp, :tipo, :consec, :estado, :msg, :je, :jr, :usr)"
            )->bindValue(':imp',    (int)$id,                     \PDO::PARAM_INT)
             ->bindValue(':tipo',   $tipoDoc,                     \PDO::PARAM_STR)
             ->bindValue(':consec', $numDoc ? (int)$numDoc : null, \PDO::PARAM_INT)
             ->bindValue(':estado', $resultado['ok'] ? 1 : 0,     \PDO::PARAM_INT)
             ->bindValue(':msg',    $mensajeSiesa,                 \PDO::PARAM_STR)
             ->bindValue(':je',     $jsonEnviado,                  \PDO::PARAM_STR)
             ->bindValue(':jr',     $jsonRespuesta,                \PDO::PARAM_STR)
             ->bindValue(':usr',    Yii::$app->user->id,          \PDO::PARAM_INT)
             ->execute();

        } catch (\Exception $e) {
            Yii::error('updateAll Siesa: ' . $e->getMessage(), __METHOD__);
        }

        // Devolver mensaje legible (sin XML crudo)
        $msgMostrar = is_array($resultado['message'])
            ? implode(' | ', array_map('strval', $resultado['message']))
            : (string)$resultado['message'];
        if (mb_strlen($msgMostrar) > 300) {
            $msgMostrar = mb_substr(strip_tags($msgMostrar), 0, 300) . '...';
        }

        return [
            'success'    => $resultado['ok'],
            'message'    => $msgMostrar,
            'tipoDoc'    => $resultado['tipoDoc'] ?? null,
            'numDoc'     => $resultado['numDoc']  ?? null,
            'docSiesa'   => (!empty($resultado['tipoDoc']) && !empty($resultado['numDoc']))
                            ? $resultado['tipoDoc'] . '-' . $resultado['numDoc'] : null,
        ];
    }

    // ----------------------------------------------------------
    // GENERAR TXT CARVAJAL — descarga directa del archivo EDI
    // ----------------------------------------------------------
    public function actionGenerarTxtCarvajal($id)
    {
        $model = $this->findModel($id);

        // Necesitamos el consecutivo de SIESA para armar el número de orden
        if (empty($model->tipoDocSiesa) || empty($model->numDocSiesa)) {
            Yii::$app->session->setFlash('warning',
                'Debe enviar primero a SIESA para obtener el consecutivo antes de generar el TXT de Carvajal.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // ── Constantes fijas ────────────────────────────────────────
        $glnComprador = '9000911750000';   // GLN Carvajal / Éxito (comprador)
        $glnProveedor = '7702277000013';   // GLN GRUMA (proveedor)
        $tipoOrden    = 'YB1';
        $numOrden     = $model->tipoDocSiesa . '-' . $model->numDocSiesa;

        // ── Fechas ──────────────────────────────────────────────────
        $tsCreacion    = strtotime($model->created_at);
        $fechaCreacion = date('YmdHi', $tsCreacion);
        // Fecha de entrega: 30 días después a las 07:00
        $tsEntrega     = mktime(7, 0, 0,
            date('n', $tsCreacion),
            date('j', $tsCreacion) + 30,
            date('Y', $tsCreacion));
        $fechaEntrega  = date('YmdHi', $tsEntrega);

        // ── Leer detalles agrupados por código + CC ─────────────────
        $rows = Yii::$app->db->createCommand(
            "SELECT codigo, costo, cc, SUM(uds) AS uds
             FROM comprasimportaciondetalle
             WHERE idImportacion = :id
             GROUP BY codigo, costo, cc
             ORDER BY codigo, cc"
        )->bindValue(':id', (int)$id, \PDO::PARAM_INT)->queryAll();

        // Agrupar por código de producto
        $productos = [];
        foreach ($rows as $row) {
            $cod = trim($row['codigo']);
            if (!isset($productos[$cod])) {
                $productos[$cod] = ['costo' => $row['costo'], 'total' => 0, 'tiendas' => []];
            }
            $productos[$cod]['total']    += $row['uds'];
            $productos[$cod]['tiendas'][] = [
                'cc'  => str_pad((int)$row['cc'], 3, '0', STR_PAD_LEFT),
                'uds' => $row['uds'],
            ];
        }

        // ── Armar líneas del TXT ────────────────────────────────────
        $lines   = [];
        $lines[] = "ENC,{$glnComprador},{$glnProveedor},{$numOrden},{$tipoOrden},{$numOrden},ORDERS";
        $lines[] = "DTM,{$fechaCreacion},{$fechaEntrega},{$fechaEntrega}";
        $lines[] = "FTX,PEDIDO FIRME";
        $lines[] = "BYOC,{$glnComprador}";
        $lines[] = "SUSR,{$glnProveedor}";
        $lines[] = "DPGR,{$glnComprador}";
        $lines[] = "IVAD,{$glnComprador}";
        $lines[] = "ITO,{$glnComprador}";

        $linNum = 1;
        foreach ($productos as $codigo => $data) {
            // Buscar EAN en tabla local item (sincronizada desde SIESA)
            $ean = Yii::$app->db->createCommand(
                "SELECT TOP 1 codigoBarras
                 FROM item
                 WHERE CAST(item AS NVARCHAR(50)) = :cod
                    OR referencia = :ref"
            )->bindValue(':cod', $codigo, \PDO::PARAM_STR)
             ->bindValue(':ref', $codigo, \PDO::PARAM_STR)
             ->queryScalar();

            // Fallback: si no está en tabla local, buscar EAN en SIESA directamente
            if (!$ean) {
                try {
                    $ean = Yii::$app->dbSiesa->createCommand(
                        "SELECT TOP 1 bar.f131_id
                         FROM t120_mc_items t120
                         JOIN t121_mc_items_extensiones itx
                              ON itx.f121_id_item = t120.f120_id
                             AND itx.f121_id_cia  = t120.f120_id_cia
                         JOIN t131_mc_items_barras bar
                              ON bar.f131_id = itx.f121_id_barras_principal
                         WHERE t120.f120_id     = :cod
                           AND t120.f120_id_cia = 1"
                    )->bindValue(':cod', $codigo, \PDO::PARAM_STR)->queryScalar();
                } catch (\Exception $ex) {
                    Yii::warning('EAN lookup SIESA fallback: ' . $ex->getMessage(), __METHOD__);
                }
            }

            // Último fallback: usar el código mismo
            if (!$ean) {
                $ean = $codigo;
            }

            $precio = number_format((float)$data['costo'], 2, '.', '');
            $total  = number_format((float)$data['total'],  2, '.', '');

            $lines[] = "LIN,{$linNum},{$ean},EN";
            $lines[] = "QTY,{$total},NAR";
            $lines[] = "PRI,{$precio},{$precio}";

            foreach ($data['tiendas'] as $tienda) {
                $gln = '9000911750' . $tienda['cc'];
                $qty = number_format((float)$tienda['uds'], 2, '.', '');
                $lines[] = "LOC,{$gln},{$qty}";
            }

            $linNum++;
        }

        $content  = implode("\r\n", $lines) . "\r\n";
        $filename = 'CARVAJAL_' . $numOrden . '_' . date('Ymd') . '.txt';

        // Registrar generación en BD
        $model->estadoCarvajal  = 1;
        $model->mensajeCarvajal = 'TXT generado: ' . $filename . ' — ' . date('Y-m-d H:i:s');
        $model->save(false);

        // Devolver como descarga
        $response = Yii::$app->response;
        $response->format  = \yii\web\Response::FORMAT_RAW;
        $response->headers->set('Content-Type',        'text/plain; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Content-Length',      (string)strlen($content));
        $response->content = $content;
        return $response;
    }

    // ----------------------------------------------------------
    // BUSCAR DOCS EN SIESA — devuelve los últimos 2CA del ERP
    // ----------------------------------------------------------
    public function actionBuscarOcSiesa()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $rows = Yii::$app->dbSiesa->createCommand(
                "SELECT TOP 20
                     f420_id_tipo_docto  AS tipoDoc,
                     f420_consec_docto   AS consecutivo,
                     CONVERT(VARCHAR(19), f420_fecha_ts_creacion, 120) AS fechaCreacion
                 FROM t420_cm_oc_docto
                 WHERE f420_id_tipo_docto = '2CA'
                 ORDER BY f420_consec_docto DESC"
            )->queryAll();
            return ['ok' => true, 'docs' => $rows];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    // ----------------------------------------------------------
    // ASIGNAR CONSECUTIVO manualmente a una importación
    // ----------------------------------------------------------
    public function actionAsignarConsecutivo($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model    = $this->findModel($id);
        $tipoDoc  = Yii::$app->request->post('tipoDoc');
        $numDoc   = Yii::$app->request->post('numDoc');

        if (empty($tipoDoc) || empty($numDoc)) {
            return ['ok' => false, 'message' => 'Tipo doc y consecutivo son requeridos.'];
        }

        try {
            Comprasimportacion::updateAll([
                'tipoDocSiesa' => mb_substr((string)$tipoDoc, 0, 10),
                'numDocSiesa'  => mb_substr((string)$numDoc,  0, 20),
            ], ['id' => (int)$id]);

            return [
                'ok'      => true,
                'docSiesa'=> $tipoDoc . '-' . $numDoc,
            ];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    // ----------------------------------------------------------
    // DESCARGAR FORMATO EXCEL — plantilla vacía con cabeceras y ejemplo
    // ----------------------------------------------------------
    public function actionDescargarFormato()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Formato');

        // ── Cabeceras ───────────────────────────────────────────────
        $headers = ['CC', 'Código', 'Talla', 'Color', 'Producto', 'UDS', 'MES', 'Tienda', 'Costo'];
        foreach ($headers as $col => $name) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet->setCellValue($cell, $name);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '343A40']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                                 'color'       => ['rgb' => 'CCCCCC']]],
            ]);
        }

        // ── Fila de ejemplo (fondo amarillo claro) ─────────────────
        $ejemplo = ['011', '297038', 'NA', 'NA', 'ACONDICIONADOR RESTAURAMAX COCO', 6, '45118', '11-PASOANCHO AC', 13160];
        foreach ($ejemplo as $col => $val) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '2';
            $sheet->setCellValue($cell, $val);
            $sheet->getStyle($cell)->applyFromArray([
                'fill'    => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                              'startColor' => ['rgb' => 'FFF9C4']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                               'color'       => ['rgb' => 'CCCCCC']]],
                'font'    => ['italic' => true, 'color' => ['rgb' => '888888']],
            ]);
        }

        // ── Ajustar anchos de columna ────────────────────────────────
        $anchos = [8, 12, 8, 8, 40, 8, 10, 28, 12];
        foreach ($anchos as $col => $ancho) {
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth($ancho);
        }

        // ── Comentario en fila 2 ─────────────────────────────────────
        $sheet->getComment('A2')->getText()->createTextRun('Fila de ejemplo — eliminar antes de importar.');

        // ── Congelar fila de cabecera ────────────────────────────────
        $sheet->freezePane('A2');

        // ── Generar descarga ─────────────────────────────────────────
        $filename = 'Formato_Importacion_Compras.xlsx';
        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $response = Yii::$app->response;
        $response->format  = \yii\web\Response::FORMAT_RAW;
        $response->headers->set('Content-Type',        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control',       'max-age=0');
        $response->content = $content;
        return $response;
    }

    // ----------------------------------------------------------
    protected function findModel($id)
    {
        if (($model = Comprasimportacion::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('El registro no existe.');
    }
}
