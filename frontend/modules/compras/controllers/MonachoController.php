<?php

namespace frontend\modules\compras\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use common\models\MonachoParser;
use frontend\models\MonachoPedido;
use frontend\models\MonachoArticulo;
use frontend\models\MonachoDetalle;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;

/**
 * MonachoController — Pedidos Monacho dentro del módulo Compras
 *
 * FLUJO SESIÓN (upload Excel):
 *   actionIndex()         → Formulario carga Excel
 *   actionProcesar()      → Parsea Excel, genera EAN, guarda en sesión
 *   actionPreview()       → Vista previa editable (sesión)
 *   actionActualizarEan() → AJAX: editar EAN en sesión
 *   actionExportarExcel() → Descarga Excel SIESA desde sesión
 *   actionGuardarSesion() → Persiste sesión en BD → redirect ver
 *   actionLimpiar()       → Limpia sesión
 *
 * FLUJO BD (pedidos guardados):
 *   actionLista()              → Listado de pedidos guardados
 *   actionCrear()              → Formulario creación manual
 *   actionGuardar()            → POST JSON → guarda en BD
 *   actionVer($id)             → Vista detalle pedido guardado
 *   actionEditarEanPedido()    → AJAX: editar EAN en BD
 *   actionEliminar($id)        → Eliminar pedido
 *   actionPdf($id)             → Genera PDF Monacho
 *   actionExportarExcelPedido($id) → Exporta Excel SIESA desde BD
 */
class MonachoController extends Controller
{
    const SESSION_KEY = 'monacho_products';

    /** Acciones AJAX que envían JSON — se valida el CSRF manualmente */
    public $enableCsrfValidation = false;

    // =========================================================================
    // FLUJO SESIÓN (upload Excel)
    // =========================================================================

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionProcesar()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['index']);
        }

        $file            = UploadedFile::getInstanceByName('monacho_file');
        $contadorInicial = (int) Yii::$app->request->post('contador_inicial', 1);

        if (!$file || $file->error) {
            Yii::$app->session->setFlash('error', 'Error al subir el archivo. Verifique que sea un .xlsx válido.');
            return $this->redirect(['index']);
        }

        $tempPath = Yii::$app->runtimePath . '/monacho_' . time() . '.' . $file->extension;

        if (!$file->saveAs($tempPath)) {
            Yii::$app->session->setFlash('error', 'No se pudo guardar el archivo temporal.');
            return $this->redirect(['index']);
        }

        try {
            $parser   = new MonachoParser();
            $products = $parser->parse($tempPath);

            if (empty($products)) {
                Yii::$app->session->setFlash('error', 'No se encontraron artículos. Verifique que sea un Monacho válido.');
                @unlink($tempPath);
                return $this->redirect(['index']);
            }

            $counter = $contadorInicial;
            foreach ($products as &$product) {
                $product['ean_codes'] = [];
                foreach ($product['colores'] as $colorData) {
                    $color = $colorData['nombre'];
                    $product['ean_codes'][$color] = [];
                    foreach ($colorData['cantidades'] as $talla => $cantidad) {
                        $product['ean_codes'][$color][$talla] =
                            MonachoParser::generarEan13($product['codigo'], $counter);
                        $counter++;
                    }
                }
            }
            unset($product);

            Yii::$app->session->set(self::SESSION_KEY, $products);
            @unlink($tempPath);
            return $this->redirect(['preview']);

        } catch (\Exception $e) {
            @unlink($tempPath);
            Yii::$app->session->setFlash('error', 'Error procesando el archivo: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    public function actionPreview()
    {
        $products = Yii::$app->session->get(self::SESSION_KEY);

        if (empty($products)) {
            Yii::$app->session->setFlash('warning', 'No hay datos cargados. Por favor cargue un archivo Monacho.');
            return $this->redirect(['index']);
        }

        return $this->render('preview', ['products' => $products]);
    }

    public function actionActualizarEan()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Método no permitido'];
        }

        $products = Yii::$app->session->get(self::SESSION_KEY);
        if (empty($products)) {
            return ['success' => false, 'message' => 'Sesión expirada'];
        }

        $codigo = (int)    Yii::$app->request->post('codigo');
        $color  = (string) Yii::$app->request->post('color');
        $talla  = (string) Yii::$app->request->post('talla');
        $ean    = (string) Yii::$app->request->post('ean');

        if (!MonachoParser::validarEan13($ean)) {
            return ['success' => false, 'message' => 'EAN13 inválido (13 dígitos, dígito de control incorrecto)'];
        }

        foreach ($products as &$product) {
            if ($product['codigo'] === $codigo) {
                $product['ean_codes'][$color][$talla] = $ean;
                break;
            }
        }
        unset($product);

        Yii::$app->session->set(self::SESSION_KEY, $products);
        return ['success' => true];
    }

    public function actionExportarExcel()
    {
        $products = Yii::$app->session->get(self::SESSION_KEY);

        if (empty($products)) {
            Yii::$app->session->setFlash('error', 'No hay datos para exportar.');
            return $this->redirect(['index']);
        }

        $spreadsheet = MonachoParser::generarExcelSiesa($products);
        $writer      = new Xlsx($spreadsheet);
        $filename    = 'SIESA_Articulos_' . date('Ymd_His') . '.xlsx';
        $tempFile    = Yii::$app->runtimePath . '/' . $filename;

        $writer->save($tempFile);

        Yii::$app->response->sendFile($tempFile, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->send();

        @unlink($tempFile);
        Yii::$app->end();
    }

    /** Guarda los datos de sesión (preview) como un pedido persistente en BD */
    public function actionGuardarSesion()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Método no permitido'];
        }

        $products = Yii::$app->session->get(self::SESSION_KEY);
        if (empty($products)) {
            return ['success' => false, 'message' => 'Sesión vacía'];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $pedido                     = new MonachoPedido();
            $pedido->proveedor          = $products[0]['proveedor'] ?? 'Sin proveedor';
            $pedido->oc_siesa           = $products[0]['oc_siesa']  ?? null;
            $pedido->oc_icg             = $products[0]['oc_icg']    ?? null;
            $pedido->fecha_despacho     = $products[0]['fecha_despacho'] ?? null;
            $pedido->contador_ean_inicio = 1;
            $pedido->estado             = 'BORRADOR';

            if (!$pedido->save()) {
                throw new \Exception('Error guardando cabecera: ' . json_encode($pedido->errors));
            }

            foreach ($products as $p) {
                $art               = new MonachoArticulo();
                $art->pedido_id    = $pedido->id;
                $art->codigo       = $p['codigo'];
                $art->descripcion  = $p['descripcion'];
                $art->referencia   = $p['referencia']    ?? '';
                $art->estilo1      = $p['estilo1']       ?? '';
                $art->estilo2      = $p['estilo2']       ?? '';
                $art->estilo3      = $p['estilo3']       ?? '';
                $art->estilo4      = $p['estilo4']       ?? '';
                $art->estilo5      = $p['estilo5']       ?? '';
                $art->concepto     = $p['concepto']      ?? '';
                $art->consumidor   = $p['consumidor']    ?? '';
                $art->universo     = $p['universo']      ?? '';
                $art->prenda       = $p['prenda']        ?? '';
                $art->tendencia    = $p['tendencia']     ?? '';
                $art->costo        = $p['costo']         ?? 0;
                $art->precio_venta = $p['precio_venta']  ?? 0;
                $art->margen       = $p['margen']        ?? 0;
                $art->rango        = $p['rango']         ?? '';
                $art->pvp_mayorista = $p['pvp_mayorista'] ?? 0;

                if (!$art->save()) {
                    throw new \Exception('Error guardando artículo ' . $p['codigo'] . ': ' . json_encode($art->errors));
                }

                foreach ($p['colores'] as $colorData) {
                    $color = $colorData['nombre'];
                    foreach ($colorData['cantidades'] as $talla => $cantidad) {
                        $det             = new MonachoDetalle();
                        $det->articulo_id = $art->id;
                        $det->color      = $color;
                        $det->talla      = $talla;
                        $det->cantidad   = $cantidad;
                        $det->ean13      = $p['ean_codes'][$color][$talla] ?? null;

                        if (!$det->save()) {
                            throw new \Exception('Error guardando detalle: ' . json_encode($det->errors));
                        }
                    }
                }
            }

            $transaction->commit();
            Yii::$app->session->remove(self::SESSION_KEY);
            return ['success' => true, 'id' => $pedido->id];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionLimpiar()
    {
        Yii::$app->session->remove(self::SESSION_KEY);
        return $this->redirect(['index']);
    }

    // =========================================================================
    // FLUJO BD — Pedidos guardados
    // =========================================================================

    public function actionLista()
    {
        $query = MonachoPedido::find()->orderBy(['id' => SORT_DESC]);

        $search    = Yii::$app->request->get('q', '');
        $estado    = Yii::$app->request->get('estado', '');

        if ($search !== '') {
            $query->andWhere(['like', 'proveedor', $search]);
        }
        if ($estado !== '') {
            $query->andWhere(['estado' => $estado]);
        }

        $count    = $query->count();
        $pageSize = 15;
        $page     = max(1, (int) Yii::$app->request->get('page', 1));
        $pedidos  = $query->offset(($page - 1) * $pageSize)->limit($pageSize)->all();

        return $this->render('lista', [
            'pedidos'  => $pedidos,
            'count'    => $count,
            'page'     => $page,
            'pageSize' => $pageSize,
            'search'   => $search,
            'estado'   => $estado,
        ]);
    }

    public function actionCrear()
    {
        return $this->render('crear');
    }

    /** Recibe JSON con la estructura del pedido y lo persiste en BD */
    public function actionGuardar()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Método no permitido'];
        }

        // Leer JSON del cuerpo crudo (Yii2 no parsea JSON sin configuración extra)
        $raw  = file_get_contents('php://input');
        $body = $raw ? (json_decode($raw, true) ?? []) : Yii::$app->request->bodyParams;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $pedido                      = new MonachoPedido();
            $pedido->proveedor           = trim($body['proveedor']   ?? '');
            $pedido->oc_siesa            = trim($body['oc_siesa']    ?? '') ?: null;
            $pedido->oc_icg              = trim($body['oc_icg']      ?? '') ?: null;
            $pedido->fecha_despacho      = $body['fecha_despacho']   ?? null ?: null;
            $pedido->contador_ean_inicio = (int) ($body['contador_ean'] ?? 1);
            $pedido->estado              = 'BORRADOR';
            $pedido->notas               = trim($body['notas'] ?? '') ?: null;

            if (!$pedido->save()) {
                throw new \Exception('Error cabecera: ' . json_encode($pedido->errors));
            }

            $counter = $pedido->contador_ean_inicio;
            $articulos = $body['articulos'] ?? [];

            foreach ($articulos as $a) {
                $art               = new MonachoArticulo();
                $art->pedido_id    = $pedido->id;
                $codigoVal         = isset($a['codigo']) && $a['codigo'] !== '' ? (int)$a['codigo'] : null;
                $art->codigo       = $codigoVal;
                $art->descripcion  = trim($a['descripcion']    ?? '');
                $art->referencia   = trim($a['referencia']     ?? '');
                $art->estilo1      = trim($a['estilo1']        ?? '');
                $art->estilo2      = trim($a['estilo2']        ?? '');
                $art->estilo3      = trim($a['estilo3']        ?? '');
                $art->estilo4      = trim($a['estilo4']        ?? '');
                $art->estilo5      = trim($a['estilo5']        ?? '');
                $art->concepto     = trim($a['concepto']       ?? '');
                $art->consumidor   = trim($a['consumidor']     ?? '');
                $art->universo     = trim($a['universo']       ?? '');
                $art->prenda       = trim($a['prenda']         ?? '');
                $art->tendencia    = trim($a['tendencia']      ?? '');
                $art->costo        = (float) ($a['costo']      ?? 0);
                $art->precio_venta = (float) ($a['precio_venta'] ?? 0);
                $art->margen       = (float) ($a['margen']     ?? 0);
                $art->rango        = trim($a['rango']          ?? '');
                $art->pvp_mayorista = (float) ($a['pvp_mayorista'] ?? 0);

                if (!$art->save()) {
                    throw new \Exception('Error artículo ' . $art->codigo . ': ' . json_encode($art->errors));
                }

                foreach ($a['colores'] as $c) {
                    $color = trim($c['nombre'] ?? '');
                    if ($color === '') continue;

                    foreach ($c['cantidades'] as $talla => $cantidad) {
                        $det              = new MonachoDetalle();
                        $det->articulo_id = $art->id;
                        $det->color       = $color;
                        $det->talla       = (string) $talla;
                        $det->cantidad    = (int) $cantidad;
                        // EAN solo si ya tiene código SIESA asignado
                        $det->ean13 = $art->codigo
                            ? MonachoParser::generarEan13($art->codigo, $counter)
                            : null;
                        if ($art->codigo) $counter++;

                        if (!$det->save()) {
                            throw new \Exception('Error detalle: ' . json_encode($det->errors));
                        }
                    }
                }
            }

            $transaction->commit();
            return ['success' => true, 'id' => $pedido->id];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionVer($id)
    {
        $pedido = $this->findPedido($id);

        $articulos = MonachoArticulo::find()
            ->where(['pedido_id' => $pedido->id])
            ->with('detalles')
            ->all();

        return $this->render('ver', [
            'pedido'    => $pedido,
            'articulos' => $articulos,
        ]);
    }

    /** AJAX: actualiza el EAN de un detalle específico en BD */
    public function actionEditarEanPedido()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Método no permitido'];
        }

        $detalleId = (int)    Yii::$app->request->post('detalle_id');
        $ean       = (string) Yii::$app->request->post('ean');

        if (!MonachoParser::validarEan13($ean)) {
            return ['success' => false, 'message' => 'EAN13 inválido'];
        }

        $detalle = MonachoDetalle::findOne($detalleId);
        if (!$detalle) {
            return ['success' => false, 'message' => 'Detalle no encontrado'];
        }

        $detalle->ean13 = $ean;
        if (!$detalle->save()) {
            return ['success' => false, 'message' => json_encode($detalle->errors)];
        }

        return ['success' => true];
    }

    /** Elimina un pedido y todo su detalle (cascade via FK) */
    public function actionEliminar($id)
    {
        $pedido = $this->findPedido($id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Borrar detalles de cada artículo
            foreach ($pedido->articulos as $art) {
                MonachoDetalle::deleteAll(['articulo_id' => $art->id]);
            }
            MonachoArticulo::deleteAll(['pedido_id' => $pedido->id]);
            $pedido->delete();
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Pedido #' . $id . ' eliminado.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'No se pudo eliminar: ' . $e->getMessage());
        }

        return $this->redirect(['lista']);
    }

    /** Genera PDF del pedido en formato Monacho */
    public function actionPdf($id)
    {
        $pedido    = $this->findPedido($id);
        $articulos = MonachoArticulo::find()
            ->where(['pedido_id' => $pedido->id])
            ->with('detalles')
            ->all();

        $html = $this->renderPartial('_pdf', [
            'pedido'    => $pedido,
            'articulos' => $articulos,
        ]);

        $mpdf = new Mpdf([
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 12,
            'margin_bottom' => 12,
            'orientation'   => 'L',   // Landscape para la tabla de tallas
        ]);

        // SetFooter requiere exactamente 3 columnas separadas por |
        $mpdf->SetFooter(
            'Pedido Monacho #' . $pedido->id . ' — ' . $pedido->proveedor
            . '|{DATE d/m/Y}|Página {PAGENO} de {nbpg}'
        );

        $mpdf->WriteHTML($html);

        $filename = 'Monacho_' . $pedido->id . '_' . date('Ymd') . '.pdf';
        return $mpdf->Output($filename, 'I');
    }

    /** Exporta Excel SIESA desde un pedido guardado en BD */
    public function actionExportarExcelPedido($id)
    {
        $pedido    = $this->findPedido($id);
        $articulos = MonachoArticulo::find()
            ->where(['pedido_id' => $pedido->id])
            ->with('detalles')
            ->all();

        // Convertir a la estructura que espera generarExcelSiesa()
        $products = [];
        foreach ($articulos as $art) {
            $coloresMap = [];
            foreach ($art->detalles as $d) {
                $coloresMap[$d->color][$d->talla] = $d->cantidad;
            }
            $coloresArr = [];
            $eanCodes   = [];
            foreach ($coloresMap as $color => $cantidades) {
                $coloresArr[] = ['nombre' => $color, 'cantidades' => $cantidades, 'total' => array_sum($cantidades)];
                $eanCodes[$color] = [];
                foreach ($cantidades as $talla => $qty) {
                    foreach ($art->detalles as $d) {
                        if ($d->color === $color && $d->talla === $talla) {
                            $eanCodes[$color][$talla] = $d->ean13;
                        }
                    }
                }
            }

            $products[] = [
                'codigo'       => $art->codigo,
                'descripcion'  => $art->descripcion,
                'referencia'   => $art->referencia,
                'estilo1'      => $art->estilo1,
                'estilo2'      => $art->estilo2,
                'estilo3'      => $art->estilo3,
                'estilo4'      => $art->estilo4,
                'estilo5'      => $art->estilo5,
                'concepto'     => $art->concepto,
                'consumidor'   => $art->consumidor,
                'universo'     => $art->universo,
                'prenda'       => $art->prenda,
                'tendencia'    => $art->tendencia,
                'costo'        => $art->costo,
                'precio_venta' => $art->precio_venta,
                'margen'       => $art->margen,
                'rango'        => $art->rango,
                'pvp_mayorista'=> $art->pvp_mayorista,
                'proveedor'    => $pedido->proveedor,
                'oc_siesa'     => $pedido->oc_siesa,
                'fecha_despacho' => $pedido->fecha_despacho,
                'colores'      => $coloresArr,
                'ean_codes'    => $eanCodes,
            ];
        }

        $spreadsheet = MonachoParser::generarExcelSiesa($products);
        $writer      = new Xlsx($spreadsheet);
        $filename    = 'SIESA_Monacho_' . $pedido->id . '_' . date('Ymd_His') . '.xlsx';
        $tempFile    = Yii::$app->runtimePath . '/' . $filename;

        $writer->save($tempFile);

        Yii::$app->response->sendFile($tempFile, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->send();

        @unlink($tempFile);
        Yii::$app->end();
    }

    /** Muestra el formulario de creación cargado con los datos del pedido existente */
    public function actionEditar($id)
    {
        $pedido = $this->findPedido($id);
        if ($pedido->estado === 'APROBADO') {
            Yii::$app->session->setFlash('error', 'No se puede editar un pedido aprobado.');
            return $this->redirect(['ver', 'id' => $id]);
        }
        $articulos = MonachoArticulo::find()
            ->where(['pedido_id' => $pedido->id])
            ->with('detalles')
            ->all();
        return $this->render('crear', [
            'pedidoEditar'    => $pedido,
            'articulosEditar' => $articulos,
        ]);
    }

    /** Recibe JSON con la estructura del pedido y actualiza un pedido existente en BD */
    public function actionActualizar()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) return ['success'=>false,'message'=>'Método no permitido'];

        $raw  = file_get_contents('php://input');
        $body = $raw ? (json_decode($raw, true) ?? []) : Yii::$app->request->bodyParams;

        $pedidoId = (int)($body['pedido_id'] ?? 0);
        if (!$pedidoId) return ['success'=>false,'message'=>'ID de pedido requerido'];

        $pedido = MonachoPedido::findOne($pedidoId);
        if (!$pedido) return ['success'=>false,'message'=>'Pedido no encontrado'];
        if ($pedido->estado === 'APROBADO') return ['success'=>false,'message'=>'No se puede editar un pedido aprobado'];

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $pedido->proveedor           = trim($body['proveedor']   ?? '');
            $pedido->oc_siesa            = trim($body['oc_siesa']    ?? '') ?: null;
            $pedido->oc_icg              = trim($body['oc_icg']      ?? '') ?: null;
            $pedido->fecha_despacho      = $body['fecha_despacho']   ?? null ?: null;
            $pedido->contador_ean_inicio = (int)($body['contador_ean'] ?? 1);
            $pedido->notas               = trim($body['notas'] ?? '') ?: null;
            if (!$pedido->save()) throw new \Exception('Error cabecera: '.json_encode($pedido->errors));

            // Borrar articulos + detalles anteriores y recrear
            foreach ($pedido->articulos as $art) {
                MonachoDetalle::deleteAll(['articulo_id' => $art->id]);
            }
            MonachoArticulo::deleteAll(['pedido_id' => $pedido->id]);

            $counter = $pedido->contador_ean_inicio;
            foreach ($body['articulos'] ?? [] as $a) {
                $art               = new MonachoArticulo();
                $art->pedido_id    = $pedido->id;
                $codigoVal         = isset($a['codigo']) && $a['codigo'] !== '' ? (int)$a['codigo'] : null;
                $art->codigo       = $codigoVal;
                $art->descripcion  = trim($a['descripcion']    ?? '');
                $art->referencia   = trim($a['referencia']     ?? '');
                $art->estilo1      = trim($a['estilo1']        ?? '');
                $art->estilo2      = trim($a['estilo2']        ?? '');
                $art->estilo3      = trim($a['estilo3']        ?? '');
                $art->estilo4      = trim($a['estilo4']        ?? '');
                $art->estilo5      = trim($a['estilo5']        ?? '');
                $art->concepto     = trim($a['concepto']       ?? '');
                $art->consumidor   = trim($a['consumidor']     ?? '');
                $art->universo     = trim($a['universo']       ?? '');
                $art->prenda       = trim($a['prenda']         ?? '');
                $art->tendencia    = trim($a['tendencia']      ?? '');
                $art->costo        = (float)($a['costo']       ?? 0);
                $art->precio_venta = (float)($a['precio_venta']?? 0);
                $art->margen       = (float)($a['margen']      ?? 0);
                $art->rango        = trim($a['rango']          ?? '');
                $art->pvp_mayorista= (float)($a['pvp_mayorista']??0);
                if (!$art->save()) throw new \Exception('Error artículo: '.json_encode($art->errors));

                foreach ($a['colores'] as $c) {
                    $color = trim($c['nombre'] ?? '');
                    if ($color === '') continue;
                    foreach ($c['cantidades'] as $talla => $cantidad) {
                        $det              = new MonachoDetalle();
                        $det->articulo_id = $art->id;
                        $det->color       = $color;
                        $det->talla       = (string)$talla;
                        $det->cantidad    = (int)$cantidad;
                        $det->ean13       = $art->codigo ? MonachoParser::generarEan13($art->codigo, $counter) : null;
                        if ($art->codigo) $counter++;
                        if (!$det->save()) throw new \Exception('Error detalle: '.json_encode($det->errors));
                    }
                }
            }
            $transaction->commit();
            return ['success'=>true,'id'=>$pedido->id];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success'=>false,'message'=>$e->getMessage()];
        }
    }

    /** Genera PDF y lo envía por email al proveedor */
    public function actionEnviarEmail($id)
    {
        $pedido    = $this->findPedido($id);
        $articulos = MonachoArticulo::find()->where(['pedido_id'=>$pedido->id])->with('detalles')->all();

        $html = $this->renderPartial('_pdf', ['pedido'=>$pedido,'articulos'=>$articulos]);
        $mpdf = new Mpdf(['margin_left'=>10,'margin_right'=>10,'margin_top'=>12,'margin_bottom'=>12,'orientation'=>'L']);
        $mpdf->SetFooter($pedido->proveedor.'|{DATE d/m/Y}|Página {PAGENO} de {nbpg}');
        $mpdf->WriteHTML($html);
        $pdfContent = $mpdf->Output('', 'S'); // String output

        $to       = Yii::$app->request->post('email_destino', '');
        $asunto   = 'Pedido Monacho #'.$pedido->id.' — '.$pedido->proveedor;
        $cuerpo   = Yii::$app->request->post('email_cuerpo',
            "Estimados,\n\nAdjunto encontrarán el pedido Monacho #".$pedido->id.".\n\nSaludos,\nGRUMALog");
        $filename = 'Monacho_'.$pedido->id.'_'.date('Ymd').'.pdf';

        if (empty($to)) {
            Yii::$app->session->setFlash('error', 'Debe ingresar un correo destino.');
            return $this->redirect(['ver','id'=>$id]);
        }

        // Encode PDF for email attachment
        $pdfB64   = base64_encode($pdfContent);
        $boundary = md5(time());
        $headers  = "From: GRUMALog <noreply@grumalog.com>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $body .= $cuerpo."\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: application/pdf; name=\"{$filename}\"\r\n";
        $body .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split($pdfB64)."\r\n";
        $body .= "--{$boundary}--";

        $sent = mail($to, $asunto, $body, $headers);

        if ($sent) {
            $pedido->estado = 'ENVIADO';
            $pedido->save(false);
            Yii::$app->session->setFlash('success', "PDF enviado a {$to} exitosamente.");
        } else {
            Yii::$app->session->setFlash('error', 'Error al enviar el correo. Verifique la configuración de email del servidor.');
        }
        return $this->redirect(['ver','id'=>$id]);
    }

    /** Marca el pedido como APROBADO */
    public function actionAprobar($id)
    {
        $pedido = $this->findPedido($id);
        $pedido->estado = 'APROBADO';
        $pedido->save(false);
        Yii::$app->session->setFlash('success','Pedido aprobado. Ahora puede asignar los códigos de artículo.');
        return $this->redirect(['ver','id'=>$id]);
    }

    /** AJAX: asigna códigos consecutivos desde SIESA a los artículos sin código y genera EAN13 */
    public function actionAsignarCodigos($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $pedido = MonachoPedido::findOne((int)$id);
        if (!$pedido) return ['success'=>false,'message'=>'Pedido no encontrado'];

        try {
            $maxCodigo = Yii::$app->db->createCommand(
                "SELECT ISNULL(MAX(CAST(f120_id AS INT)), 320000) FROM t120_mc_items WHERE id_cia = 1"
            )->queryScalar();
        } catch (\Exception $e) {
            return ['success'=>false,'message'=>'No se pudo consultar SIESA. Ingrese los códigos manualmente. Error: '.$e->getMessage()];
        }

        $articulos = MonachoArticulo::find()->where(['pedido_id'=>$pedido->id,'codigo'=>null])->all();
        $nextCodigo = (int)$maxCodigo + 1;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $counter = $pedido->contador_ean_inicio;
            foreach ($articulos as $art) {
                $art->codigo = $nextCodigo++;
                if (!$art->save(false)) throw new \Exception('Error asignando código');
                // Generar EAN13 para todos los detalles de este artículo
                foreach (MonachoDetalle::find()->where(['articulo_id'=>$art->id])->all() as $det) {
                    $det->ean13 = MonachoParser::generarEan13($art->codigo, $counter++);
                    $det->save(false);
                }
            }
            $transaction->commit();
            return ['success'=>true,'message'=>count($articulos).' artículo(s) con código asignado desde '.((int)$maxCodigo+1)];
        } catch(\Exception $e) {
            $transaction->rollBack();
            return ['success'=>false,'message'=>$e->getMessage()];
        }
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    protected function findPedido($id)
    {
        $model = MonachoPedido::findOne((int) $id);
        if ($model === null) {
            throw new NotFoundHttpException("Pedido #{$id} no encontrado.");
        }
        return $model;
    }
}
