<?php

namespace frontend\modules\contabilidad\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\db\Expression;
use frontend\models\search\DevolucionmercanciaSearch;

class DevolucionmercanciaController extends Controller
{
    /**
     * Index: consulta de devoluciones
     */
    public function actionIndex()
{
    $model      = new DevolucionmercanciaSearch();
    $data       = [];
    $resultados = [];

    if ($model->load(Yii::$app->request->get()) && $model->validate()) {
        $db = Yii::$app->dbSiesa;

        // 🔹 Normalizamos parámetros
        $params = [
            ':fecha_inicio'   => $model->fecha_inicio,
            ':fecha_fin'      => $model->fecha_fin,
            ':tipo_documento' => $model->tipo_documento,
            ':consecutivo'    => $model->consecutivo ?: null,
            ':bodega'         => $model->bodega ?: null,
        ];

        // 🔹 Consulta principal
        $sql = "
            SELECT 
                D.f350_id_tipo_docto AS tipo_documento,
                D.f350_consec_docto AS consecutivo,
                B.F150_ID AS bodega,
                CAST(D.f350_fecha AS DATE) AS fecha_documento,
                I.f470_rowid_item_ext,
                v.v121_id_item,
                LTRIM(RTRIM(v.v121_descripcion)) AS descripcion,
                RTRIM(LTRIM(v.v121_id_ext1_detalle)) AS extension1,
                RTRIM(LTRIM(v.v121_id_ext2_detalle)) AS extension2,
                I.f470_id_unidad_medida AS unidad,
                I.f470_cant_1 as cantidad,
                I.f470_costo_prom_uni AS costo_unitario,
                I.f470_costo_prom_tot AS costo_total,
                I.f470_rowid_tercero_vend,
                COALESCE(I.f470_notas, '') AS notas_item,
                COALESCE(D.F350_NOTAS, '') AS notas_docto
            FROM t470_cm_movto_invent AS I
            INNER JOIN t350_co_docto_contable AS D 
                ON I.f470_rowid_docto = D.f350_rowid
            INNER JOIN t150_mc_bodegas AS B 
                ON B.f150_rowid = I.f470_rowid_bodega
            INNER JOIN V121 AS V 
                ON I.f470_rowid_item_ext = v.v121_rowid_item_ext
            WHERE D.f350_id_tipo_docto = :tipo_documento
              AND CAST(D.f350_fecha AS DATE) BETWEEN :fecha_inicio AND :fecha_fin
              AND ((:consecutivo IS NULL OR :consecutivo = '') OR D.f350_consec_docto = :consecutivo)
              AND ((:bodega IS NULL OR :bodega = '') OR B.F150_ID = :bodega)
            ORDER BY D.f350_fecha DESC, D.f350_consec_docto;
        ";

        $rows = $db->createCommand($sql, $params)->queryAll();
        $data = $rows;

        // 🔹 Guardamos en sesión para usar después en actionGenerar
        Yii::$app->session->set('devoluciones_data', $rows);

        // 🔹 Ajuste de existencias (si se pidió)
        if (Yii::$app->request->get('ajustar') == 1) {
            foreach ($rows as $row) {
                $item     = $row['v121_id_item'];
                $ext1     = $row['extension1'];
                $ext2     = $row['extension2'];
                $bodega   = $row['bodega'];
                $cantidad = $row['cantidad'];

                // Consulta existencia en bodega actual
                $existencia = $db->createCommand("
                    SELECT c.f415_cant_existencia_1
                    FROM t415_cm_existencia_consig c
                    INNER JOIN v121 v 
                        ON c.f415_rowid_item_ext = v.v121_rowid_item_ext
                    INNER JOIN t150_mc_bodegas b 
                        ON c.f415_rowid_bodega = b.f150_rowid
                    WHERE v.v121_id_item = :item
                      AND RTRIM(LTRIM(v.v121_id_ext1_detalle)) = :ext1
                      AND RTRIM(LTRIM(v.v121_id_ext2_detalle)) = :ext2
                      AND b.F150_ID = :bodega
                ", [
                    ':item'   => $item,
                    ':ext1'   => $ext1,
                    ':ext2'   => $ext2,
                    ':bodega' => $bodega,
                ])->queryScalar();

                if ($existencia !== false && $existencia >= $cantidad) {
                    $resultados[] = [
                        'item'    => $item,
                        'bodega'  => $bodega,
                        'estado'  => 'OK',
                        'mensaje' => "Existencia suficiente en bodega {$bodega}",
                    ];
                    continue;
                }

                // Buscar en otras bodegas
                $otras = $db->createCommand("
                    SELECT b.F150_ID, c.f415_cant_existencia_1
                    FROM t415_cm_existencia_consig c
                    INNER JOIN v121 v 
                        ON c.f415_rowid_item_ext = v.v121_rowid_item_ext
                    INNER JOIN t150_mc_bodegas b 
                        ON c.f415_rowid_bodega = b.f150_rowid
                    WHERE v.v121_id_item = :item
                      AND RTRIM(LTRIM(v.v121_id_ext1_detalle)) = :ext1
                      AND RTRIM(LTRIM(v.v121_id_ext2_detalle)) = :ext2
                      AND c.f415_cant_existencia_1 >= :cantidad
                ", [
                    ':item'    => $item,
                    ':ext1'    => $ext1,
                    ':ext2'    => $ext2,
                    ':cantidad'=> $cantidad,
                ])->queryAll();

                if (!empty($otras)) {
                    $bodegaOrigen = $otras[0]['F150_ID'];
                    $resultados[] = [
                        'item'    => $item,
                        'bodega'  => $bodega,
                        'estado'  => 'AJUSTADO',
                        'mensaje' => "Se ajustó con existencia desde bodega {$bodegaOrigen}",
                    ];
                } else {
                    $resultados[] = [
                        'item'    => $item,
                        'bodega'  => $bodega,
                        'estado'  => 'SIN EXISTENCIA',
                        'mensaje' => "No se encontró existencia en ninguna bodega",
                    ];
                }
            }

// 🔹 Guardamos los datos ajustados (detalle + resultados) en sesión
            Yii::$app->session->set('devoluciones_data_ajustada', [
                'detalle'    => $rows,
                'data'       => $data,
                'resultados' => $resultados,
            ]);

        }
    }

    return $this->render('index', [
        'model'      => $model,
        'data'       => $data,
        'resultados' => $resultados,
    ]);
}




    /**
     * Generar y enviar documento
     */
public function actionGenerar()
{
    $post    = Yii::$app->request->post();
    $detalle = Yii::$app->session->get('devoluciones_data', []);

    if (!$detalle) {
        Yii::$app->session->setFlash('error', 'No hay devoluciones cargadas.');
        return $this->redirect(['index']);
    }

    if ($post) {
        // =========================
        // CABECERA DOCUMENTO
        // =========================
        Yii::$app->db->createCommand()->insert('devolucionmercancia_documento', [
            'tipo_documento'                  => $post['tipo_documento'],
            'fecha_documento'                 => $post['fecha_documento'],
            'nit_tercero'                     => $post['tercero_proveedor'],
            'sucursal_proveedor'              => $post['sucursal_proveedor'],
            'prefijo_documento_proveedor'     => $post['prefijo_documento_proveedor'],
            'consecutivo_documento_proveedor' => $post['consecutivo_documento_proveedor'],
            'fecha_documento_proveedor'       => $post['fecha_documento_proveedor'],
            'condicion_pago'                  => '015',
            'tipo_proveedor'                  => '0210',
            'valor_documento'                 => 0,
            'estado'                          => 'pendiente',
            'created_at'                      => new Expression('GETDATE()'),
            'updated_at'                      => new Expression('GETDATE()'),
        ])->execute();

        $id = Yii::$app->db->getLastInsertID();
        $detalleFinal = [];

        // =========================
        // 1️⃣ AGRUPAR DETALLES POR ITEM + EXT1 + EXT2
        // =========================
        $agrupados = [];
        foreach ($detalle as $row) {
            $key = trim($row['v121_id_item']).'|'.trim($row['extension1']).'|'.trim($row['extension2']);
            if (!isset($agrupados[$key])) {
                $agrupados[$key] = $row;
                $agrupados[$key]['cantidad_total'] = (int)$row['cantidad'];
            } else {
                $agrupados[$key]['cantidad_total'] += (int)$row['cantidad'];
            }
        }

        // =========================
        // 2️⃣ PROCESAR CADA GRUPO UNA SOLA VEZ
        // =========================
        foreach ($agrupados as $grupo) {
            $item      = trim($grupo['v121_id_item']);
            $ext1      = trim($grupo['extension1']);
            $ext2      = trim($grupo['extension2']);
            $unidad    = $grupo['unidad'];
            $descripcion = $grupo['descripcion'];
            $costoUnitario = (float)$grupo['costo_unitario'];
            $faltante  = (int)$grupo['cantidad_total'];

            // Buscar existencias reales válidas (solo > 0)
            $existencias = Yii::$app->dbSiesa->createCommand("
                SELECT b.F150_ID AS bodega,
                       CASE WHEN c.f415_cant_existencia_1 < 0 THEN 0 ELSE c.f415_cant_existencia_1 END AS saldo
                FROM t415_cm_existencia_consig c
                INNER JOIN v121 v ON c.f415_rowid_item_ext = v.v121_rowid_item_ext
                INNER JOIN t150_mc_bodegas b ON c.f415_rowid_bodega = b.f150_rowid
                WHERE v.v121_id_item = :item
                  AND RTRIM(LTRIM(v.v121_id_ext1_detalle)) = :ext1
                  AND RTRIM(LTRIM(v.v121_id_ext2_detalle)) = :ext2
                  AND ISNULL(c.f415_cant_existencia_1, 0) > 0
                ORDER BY c.f415_cant_existencia_1 DESC
            ", [
                ':item' => $item,
                ':ext1' => $ext1,
                ':ext2' => $ext2,
            ])->queryAll();

            // Distribuir existencias
            if (!empty($existencias)) {
                foreach ($existencias as $ex) {
                    if ($faltante <= 0) break;

                    $usar = min($faltante, (int)$ex['saldo']);
                    if ($usar <= 0) continue;

                    $costoTotal = (int)floor($usar * $costoUnitario);

                    Yii::$app->db->createCommand()->insert('devolucionmercancia_detalle', [
                        'documento_id'   => $id,
                        'item'           => $item,
                        'descripcion'    => $descripcion,
                        'extension1'     => $ext1,
                        'extension2'     => $ext2,
                        'unidad'         => $unidad,
                        'bodega'         => $ex['bodega'],
                        'cantidad'       => $usar,
                        'precio_unitario'=> (int)floor($costoUnitario),
                        'costo_total'    => $costoTotal,
                        'sin_existencia' => 0,
                    ])->execute();

                    $detalleFinal[] = [
                        'item'           => $item,
                        'descripcion'    => $descripcion,
                        'extension1'     => $ext1,
                        'extension2'     => $ext2,
                        'unidad'         => $unidad,
                        'bodega'         => $ex['bodega'],
                        'cantidad'       => $usar,
                        'costo_unitario' => (int)floor($costoUnitario),
                        'costo_total'    => $costoTotal,
                        'sin_existencia' => 0,
                    ];

                    $faltante -= $usar;
                }
            }

            // Si todavía queda faltante, registrar línea sin existencia (una sola)
            if ($faltante > 0) {
                $costoTotal = (int)floor($faltante * $costoUnitario);

                Yii::$app->db->createCommand()->insert('devolucionmercancia_detalle', [
                    'documento_id'   => $id,
                    'item'           => $item,
                    'descripcion'    => $descripcion,
                    'extension1'     => $ext1,
                    'extension2'     => $ext2,
                    'unidad'         => $unidad,
                    'bodega'         => 'SIN_EXIST',
                    'cantidad'       => $faltante,
                    'precio_unitario'=> (int)floor($costoUnitario),
                    'costo_total'    => $costoTotal,
                    'sin_existencia' => 1,
                ])->execute();

                $detalleFinal[] = [
                    'item'           => $item,
                    'descripcion'    => $descripcion,
                    'extension1'     => $ext1,
                    'extension2'     => $ext2,
                    'unidad'         => $unidad,
                    'bodega'         => 'SIN_EXIST',
                    'cantidad'       => $faltante,
                    'costo_unitario' => (int)floor($costoUnitario),
                    'costo_total'    => $costoTotal,
                    'sin_existencia' => 1,
                ];
            }
        }

        // =========================
        // 3️⃣ CALCULAR TOTAL REAL
        // =========================
        $totalExacto = 0;
        foreach ($detalleFinal as $d) {
            if ($d['sin_existencia'] == 0) {
                $totalExacto += (int)$d['costo_total'];
            }
        }

        Yii::$app->db->createCommand()->update('devolucionmercancia_documento', [
            'valor_documento' => $totalExacto,
            'updated_at'      => new Expression('GETDATE()'),
        ], ['id' => $id])->execute();

        // =========================
        // 4️⃣ ENVIAR A SIESA
        // =========================
        $doc = Yii::$app->db->createCommand("
            SELECT * FROM devolucionmercancia_documento WHERE id = :id
        ", [':id' => $id])->queryOne();

        $resultado = $this->enviarASiesa($doc, $detalleFinal);

        if ($resultado['success']) {
            Yii::$app->session->setFlash('success', 'Documento enviado correctamente a Siesa.');
        } else {
            Yii::$app->session->setFlash('error', 'Error al enviar: ' . $resultado['mensaje']);
        }

        return $this->redirect(['enviados']);
    }

    return $this->render('generar', [
        'detalle' => $detalle,
        'json'    => null,
    ]);
}






    /**
     * Ver enviados
     */
    public function actionEnviados()
    {
        $documentos = Yii::$app->db->createCommand("
            SELECT * FROM devolucionmercancia_documento ORDER BY created_at DESC
        ")->queryAll();

        return $this->render('enviados', [
            'documentos' => $documentos,
        ]);
    }

  
    /**
     * Reenviar documento
     */
    /**
 * Reenviar documento a Siesa
 */
public function actionReenviar($id)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $documento = Yii::$app->db->createCommand("
        SELECT * FROM devolucionmercancia_documento WHERE id=:id
    ", [':id' => $id])->queryOne();

    if (!$documento) {
        return ['success' => false, 'mensaje' => 'Documento no encontrado.'];
    }

    $detalle = Yii::$app->db->createCommand("
        SELECT * FROM devolucionmercancia_detalle WHERE documento_id = :id
    ", [':id' => $id])->queryAll();

    if (empty($detalle)) {
        return ['success' => false, 'mensaje' => 'No se encontró detalle para este documento.'];
    }

    $resultado = $this->enviarASiesa($documento, $detalle, true);

    return [
        'success' => $resultado['success'],
        'mensaje' => $resultado['mensaje'],
        'json'    => json_decode($documento['json_data'] ?? '[]', true),
    ];
}

 




private function enviarASiesa($doc, $items = [], $actualizar = false)
{
    // === 0) Estado inicial ===
    Yii::$app->db->createCommand()->update('devolucionmercancia_documento', [
        'estado'    => 'pendiente',
        'updated_at'=> new Expression('GETDATE()'),
    ], ['id' => $doc['id']])->execute();

    // === 1) Filtrar ítems válidos (con existencia y cantidad válida)
    $items = array_filter($items, function($it) {
        if (!isset($it['cantidad']) || $it['cantidad'] <= 0) return false;
        if (isset($it['sin_existencia']) && $it['sin_existencia'] == 1) return false;
        return true;
    });

    // === 2) Agrupar ítems (por item + ext1 + ext2 + bodega)
    $agrupados = [];
    foreach ($items as $it) {
        $itemId = $it['item'] ?? ($it['v121_id_item'] ?? null);
        if (!$itemId) continue;

        $key = implode('|', [
            trim((string)$itemId),
            trim($it['extension1'] ?? ''),
            trim($it['extension2'] ?? ''),
            trim($it['bodega'] ?? ''),
        ]);

        if (!isset($agrupados[$key])) {
            $agrupados[$key] = [
                'item'           => trim($itemId),
                'extension1'     => trim($it['extension1'] ?? ''),
                'extension2'     => trim($it['extension2'] ?? ''),
                'bodega'         => trim($it['bodega'] ?? ''),
                'cantidad'       => (int)($it['cantidad'] ?? 0),
                'costo_unitario' => (float)($it['costo_unitario'] ?? $it['precio_unitario'] ?? 0),
            ];
        } else {
            $agrupados[$key]['cantidad'] += (int)($it['cantidad'] ?? 0);
        }
    }

    // === 3) Normalizar totales — SIN decimales (enteros)
    $itemsFinal = [];
    $totCosto   = 0;

    foreach ($agrupados as $n) {
        if ($n['cantidad'] <= 0 || $n['costo_unitario'] <= 0) continue;

        $cantidad = (int)$n['cantidad'];
        $pu       = (float)$n['costo_unitario'];

        $costoTot = (int) floor($cantidad * $pu); // 🔹 truncado (entero)
        $itemsFinal[] = [
            'item'           => $n['item'],
            'extension1'     => $n['extension1'],
            'extension2'     => $n['extension2'],
            'bodega'         => $n['bodega'],
            'cantidad'       => $cantidad,
            'costo_unitario' => (int) floor($pu),
            'costo_total'    => $costoTot,
        ];
        $totCosto += $costoTot;
    }

    // === 4) Armar JSON — Usa el valor de documento exacto desde cabecera
    $valorDocumento = (int) $doc['valor_documento']; // 🔹 garantizado desde actionGenerar
    $json = [
        "Relación saldos por ítem V.3" => [],
        "Documentos" => [],
        "Cuotas CxP" => [],
    ];

    foreach ($itemsFinal as $n) {
        $json["Relación saldos por ítem V.3"][] = [
            "Centro de operación"      => "002",
            "Tipo de documento"        => $doc['tipo_documento'],
            "Consecutivo de documento" => "1",
            "Item"                     => $n['item'],
            "Extension 1"              => $n['extension1'],
            "Extension 2"              => $n['extension2'],
            "Unidad de medida"         => "UND",
            "Bodega"                   => $n['bodega'],
            "Motivo"                   => "02",
            "Cantidad base"            => $n['cantidad'],
            "Precio unitario"          => $n['costo_unitario'],
            "Costo total"              => $n['costo_total'],
        ];
    }

    $json["Documentos"][] = [
        "Centro de operación"                => "002",
        "Tipo de documento"                  => $doc['tipo_documento'],
        "Consecutivo de documento"           => "1",
        "Fecha del documento AAAAMMDD"       => date('Ymd', strtotime($doc['fecha_documento'])),
        "Tercero proveedor"                  => $doc['nit_tercero'],
        "Sucursal proveedor"                 => "001",
        "Prefijo documento proveedor"        => trim($doc['prefijo_documento_proveedor']),
        "Consecutivo documento proveedor"    => (string)$doc['consecutivo_documento_proveedor'],
        "Fecha documento proveedor AAAAMMDD" => date('Ymd', strtotime($doc['fecha_documento_proveedor'])),
        "Condición de pago"                  => $doc['condicion_pago'],
        "Valor del documento"                => $valorDocumento, // ✅ mismo que en cabecera
        "Tipo de proveedor"                  => $doc['tipo_proveedor'],
    ];

    $json["Cuotas CxP"][] = [
        "Centro de operación del documento"  => "002",
        "Tipo de documento"                  => $doc['tipo_documento'],
        "Numero de documento"                => "1",
        "Porcentaje de la cuota respecto al total del documento." => "100",
        "Fecha de vencimiento de la cuota AAAAMMDD" => date('Ymd', strtotime("+15 days", strtotime($doc['fecha_documento']))),
        "fecha pronto pago"                  => date('Ymd', strtotime("+15 days", strtotime($doc['fecha_documento']))),
    ];

    // === 5) Envío a Siesa ===
    $estadoLog = 'error';
    $responseContent = null;

    try {
        $client = new \yii\httpclient\Client(['transport' => 'yii\httpclient\CurlTransport']);
        $url = 'https://servicios.siesacloud.com/api/siesa/v3.1/conectoresimportar?' . http_build_query([
            'idCompania'      => 8203,
            'idSistema'       => 8203,
            'idDocumento'     => 217191,
            'nombreDocumento' => 'FACTURAS DE COMPRAS VMI',
        ]);

        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($url)
            ->addHeaders([
                'Content-Type' => 'application/json',
                'ConniKey'     => '461ee4af939cfd45fe2a20e596c02346',
                'ConniToken'   => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1laWRlbnRpZmllciI6IjJhNjU1YjdjLTY1ODQtNGMyZS1iYTI3LTMwNGYxNjVmN2U0ZiIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcHJpbWFyeXNpZCI6IjVlNmE2OTgyLTMxMTEtNGY2OS1hMjViLWEzOWU1ODI5NmY2MiJ9.l78vnBKJJ15ND7ro-VE52RS7ChSYnIR8Qu8UpvR9obY',
            ])
            ->setOptions([CURLOPT_SSL_VERIFYPEER => false])
            ->setContent(json_encode($json, JSON_UNESCAPED_UNICODE))
            ->send();

        $responseContent = $response->content;
        if ($response->isOk) {
            $estadoLog = 'enviado';
        }
    } catch (\Throwable $e) {
        $responseContent = $e->getMessage();
    }

    // === 6) Guardar estado y JSON final ===
    Yii::$app->db->createCommand()->update('devolucionmercancia_documento', [
        'estado'         => $estadoLog,
        'respuesta_siesa'=> $responseContent,
        'json_enviado'   => json_encode($json, JSON_UNESCAPED_UNICODE),
        'valor_documento'=> $valorDocumento,
        'updated_at'     => new Expression('GETDATE()'),
    ], ['id' => $doc['id']])->execute();

    return [
        'success' => ($estadoLog === 'enviado'),
        'estado'  => $estadoLog,
        'mensaje' => $responseContent,
    ];
}













    public function actionView($id)
{
    $doc = Yii::$app->db->createCommand("
        SELECT * FROM devolucionmercancia_documento WHERE id = :id
    ", [':id' => $id])->queryOne();

    if (!$doc) {
        throw new NotFoundHttpException("Documento no encontrado.");
    }

    $detalle = Yii::$app->db->createCommand("
        SELECT * FROM devolucionmercancia_detalle WHERE documento_id = :id
    ", [':id' => $id])->queryAll();

    // 👇 Intentamos decodificar JSON de respuesta_siesa
    $respuestaSiesa = null;
    if (!empty($doc['respuesta_siesa'])) {
        $decoded = json_decode($doc['respuesta_siesa'], true);
        $respuestaSiesa = $decoded ?: $doc['respuesta_siesa'];
    }

    return $this->render('view', [
        'documento'      => $doc,
        'detalle'        => $detalle,
        'respuestaSiesa' => $respuestaSiesa, // 👈 enviamos formateado
    ]);
}

}
