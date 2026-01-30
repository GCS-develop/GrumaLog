<?php
namespace frontend\modules\contabilidad\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\base\DynamicModel;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use frontend\models\LogFactVmi;
use frontend\models\LogFactVmiItem;

// PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ConciliacionController extends Controller
{
    private const FIX_CO         = '002';
    private const FIX_TIPO_DOC   = 'ECG';
    private const FIX_CONSEC_DOC = '1';
    private const FIX_MOTIVO     = '02';
    private const FIX_SUC_PROV   = '001';
    private const FIX_COND_PAGO  = '015';
    private const FIX_PORC_CUOTA = 100;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'finalize' => ['POST'],
                    'cancel'   => ['POST'],
                    'export'   => ['POST'],
                ],
            ],
        ];
    }

    /** Formulario */
    public function actionIndex()
{
    // 🚨 Si es un nuevo documento → limpiar sesión
    if (Yii::$app->request->get('nuevo') == 1) {
        Yii::$app->session->remove('vmi_preview_rows');
        Yii::$app->session->remove('vmi_preview_meta');
    }

    $proveedores = [];
    try {
        $proveedoresRaw = Yii::$app->dbSiesa->createCommand("
            SELECT DISTINCT LTRIM(RTRIM(f106_id)) AS id, LTRIM(RTRIM(f106_descripcion)) AS nombre
            FROM t106_mc_criterios_item_mayores
            WHERE f106_id_plan = '015'
            ORDER BY LTRIM(RTRIM(f106_descripcion))
        ")->queryAll();

        foreach ($proveedoresRaw as $r) {
            $proveedores[$r['id']] = $r['nombre'].' ('.$r['id'].')';
        }
    } catch (\Throwable $e) {
        Yii::$app->session->setFlash('error', 'No se pudo cargar proveedores: '.$e->getMessage());
    }

    $model = new \yii\base\DynamicModel(['proveedor_id','fecha_inicio','fecha_fin']);
    $model->addRule(['proveedor_id','fecha_inicio','fecha_fin'], 'required')
          ->addRule(['fecha_inicio','fecha_fin'], 'date', ['format'=>'php:Y-m-d']);

    // 🚨 Si es nuevo, forzar show=false
    $show = false;
    if (Yii::$app->request->get('nuevo') != 1) {
        $show = Yii::$app->request->get('show') == 1 
             || !empty(Yii::$app->session->get('vmi_preview_meta', []));
    }

    return $this->render('index', [
        'model'       => $model,
        'proveedores' => $proveedores,
        'show'        => $show,
    ]);
}



public function actionPreview()
{
    $req = Yii::$app->request;

    if ($req->isGet) {
        $rows = Yii::$app->session->get('vmi_preview_rows');
        $meta = Yii::$app->session->get('vmi_preview_meta', []);
        if (is_array($rows) && !empty($rows)) {
            [$totUnidades, $totCosto] = $this->calcTotals($rows);
            $dp = new ArrayDataProvider([
                'allModels'  => $rows,
                'pagination' => ['pageSize' => 500],
            ]);
            return $this->render('preview', [
                'dp'         => $dp,
                'idProv'     => $meta['idProv'] ?? '',
                'provNom'    => $meta['provNom'] ?? '',
                'fini'       => $meta['fini'] ?? '',
                'ffin'       => $meta['ffin'] ?? '',
                'totUnidades'=> $totUnidades,
                'totCosto'   => $totCosto,
            ]);
        }
        Yii::$app->session->setFlash('error','No hay resultados previos para paginar. Ejecuta la consulta nuevamente.');
        return $this->redirect(['index']);
    }

    // POST normal
    $form    = (array)$req->post('DynamicModel', []);
    $idProv  = isset($form['proveedor_id']) ? trim($form['proveedor_id']) : trim((string)$req->post('proveedor_id'));
    $fini    = $form['fecha_inicio']  ?? $req->post('fecha_inicio');
    $ffin    = $form['fecha_fin']     ?? $req->post('fecha_fin');
    $provNom = $req->post('proveedor_nombre', '');

    if (!$idProv || !$fini || !$ffin) {
        Yii::$app->session->setFlash('error', 'Faltan parámetros.');
        return $this->reRenderIndexWithValues($idProv, $fini, $ffin);
    }

    try {
        $pFi = date('Y-m-d', strtotime($fini));
        $pFf = date('Y-m-d', strtotime($ffin));

        // ===== Consulta principal (POS + inventario) =====
        $sql = "
            SELECT
                q.Fecha,
                q.BodegaId,
                q.Bodega,
                q.Item,
                q.Codigo,
                q.CodigoBarras,
                q.Ext1,
                q.Ext2,
                'UND' AS unidad_medida,
                SUM(q.unidades) AS cantidad_base,
                SUM(q.total) / NULLIF(SUM(q.unidades),0) AS precio_unitario,
                SUM(q.total) AS costo_total
            FROM (
                -------------------------------------------------------------------
                -- Ventas POS (t9930)
                -------------------------------------------------------------------
                SELECT
                    CAST(vta.f9930_id_fecha_factura AS DATE) AS Fecha,
                    bod.f150_id AS BodegaId,
                    bod.f150_descripcion AS Bodega,
                    it.f120_id AS Item,
                    CONCAT(it.f120_id,'-',itx.f121_id_barras_principal) AS Codigo,
                    itx.f121_id_barras_principal AS CodigoBarras,
                    TRIM(itx.f121_id_ext1_detalle) AS Ext1,
                    TRIM(itx.f121_id_ext2_detalle) AS Ext2,

                    CASE
                        WHEN vta.f9930_ind_naturaleza = 1 
                            THEN vta.f9930_cant_base * -1 * ISNULL(NULLIF(vta.f9930_factor,0),1)
                        ELSE vta.f9930_cant_base * ISNULL(NULLIF(vta.f9930_factor,0),1)
                    END AS unidades,

                    CASE
                        WHEN vta.f9930_ind_naturaleza = 1
                            THEN vta.f9930_cant_base 
                                 * ROUND(ISNULL(ISNULL(precio_vigente.f212_precio, precio_minimo.f212_precio),
                                                vta.f9930_costo_prom_tot),0) * -1 * ISNULL(NULLIF(vta.f9930_factor,0),1)
                        ELSE vta.f9930_cant_base 
                                 * ROUND(ISNULL(ISNULL(precio_vigente.f212_precio, precio_minimo.f212_precio),
                                                vta.f9930_costo_prom_tot),0) * ISNULL(NULLIF(vta.f9930_factor,0),1)
                    END AS total
                FROM t9930_pdv_a_movto_venta vta
                INNER JOIN t121_mc_items_extensiones itx 
                        ON vta.f9930_rowid_item_ext = itx.f121_rowid
                INNER JOIN t120_mc_items it 
                        ON itx.f121_rowid_item = it.f120_rowid
                INNER JOIN t150_mc_bodegas bod 
                        ON bod.f150_rowid = vta.f9930_rowid_bodega
                LEFT JOIN t125_mc_items_criterios itc_prov 
                        ON it.f120_rowid = itc_prov.f125_rowid_item AND itc_prov.f125_id_plan = '015'
                LEFT JOIN t106_mc_criterios_item_mayores itcm4 
                        ON itc_prov.f125_id_plan = itcm4.f106_id_plan AND itc_prov.f125_id_criterio_mayor = itcm4.f106_id
                LEFT JOIN t125_mc_items_criterios itc_tipinv 
                        ON it.f120_rowid = itc_tipinv.f125_rowid_item AND itc_tipinv.f125_id_plan = '008'
                LEFT JOIN t106_mc_criterios_item_mayores itcm8 
                        ON itc_tipinv.f125_id_plan = itcm8.f106_id_plan AND itc_tipinv.f125_id_criterio_mayor = itcm8.f106_id

                OUTER APPLY (
                    SELECT TOP 1 cot.f212_precio
                    FROM t212_mm_cotizaciones cot
                    WHERE cot.f212_rowid_item_ext = vta.f9930_rowid_item_ext
                      AND cot.f212_id_um IN ('UND')
                      AND TRIM(itcm4.f106_id) = :prov
                      AND CAST(cot.f212_fecha_activacion AS DATE) <= CAST(vta.f9930_id_fecha_factura AS DATE)
                    ORDER BY cot.f212_fecha_activacion DESC
                ) AS precio_vigente

                OUTER APPLY (
                    SELECT TOP 1 cot.f212_precio
                    FROM t212_mm_cotizaciones cot
                    WHERE cot.f212_rowid_item_ext = vta.f9930_rowid_item_ext
                      AND cot.f212_id_um IN ('UND')
                      AND TRIM(itcm4.f106_id) = :prov
                    ORDER BY cot.f212_fecha_activacion ASC
                ) AS precio_minimo

                WHERE TRIM(itcm4.f106_id) = :prov
                  AND itcm8.f106_id_plan = '008' 
                  AND itcm8.f106_id = '0001'
                  AND CAST(vta.f9930_id_fecha_factura AS DATE) BETWEEN :fi AND :ff

                UNION ALL

                -------------------------------------------------------------------
                -- Ventas Inventario (t470)
                -------------------------------------------------------------------
                SELECT
                    CAST(t350_fact.f350_fecha AS DATE) AS Fecha,
                    t150.f150_id AS BodegaId,
                    t150.f150_descripcion AS Bodega,
                    f120_id AS Item,
                    CONCAT(f120_id,'-',f121_id_barras_principal) AS Codigo,
                    f121_id_barras_principal AS CodigoBarras,
                    TRIM(f121_id_ext1_detalle) AS Ext1,
                    TRIM(f121_id_ext2_detalle) AS Ext2,

                    CASE
                        WHEN f470_ind_naturaleza = 1 
                            THEN f470_cant_base * -1 * ISNULL(NULLIF(f470_factor,0),1)
                        ELSE f470_cant_base * ISNULL(NULLIF(f470_factor,0),1)
                    END AS unidades,

                    CASE
                        WHEN f470_ind_naturaleza = 1
                            THEN f470_cant_base 
                                 * ROUND(ISNULL(ISNULL(precio_vigente.f212_precio, precio_minimo.f212_precio),
                                                f470_costo_prom_tot),0) * -1 * ISNULL(NULLIF(f470_factor,0),1)
                        ELSE f470_cant_base 
                                 * ROUND(ISNULL(ISNULL(precio_vigente.f212_precio, precio_minimo.f212_precio),
                                                f470_costo_prom_tot),0) * ISNULL(NULLIF(f470_factor,0),1)
                    END AS total
                FROM t350_co_docto_contable t350_fact
                INNER JOIN t461_cm_docto_factura_venta 
                        ON f461_rowid_docto = t350_fact.f350_rowid
                INNER JOIN t470_cm_movto_invent t470 
                        ON f470_rowid_docto_fact = f461_rowid_docto
                INNER JOIN t150_mc_bodegas t150 
                        ON t150.f150_rowid = f470_rowid_bodega
                INNER JOIN t121_mc_items_extensiones 
                        ON f121_rowid = f470_rowid_item_ext
                INNER JOIN t120_mc_items 
                        ON f120_rowid = f121_rowid_item
                LEFT JOIN t125_mc_items_criterios itc_proveedor 
                       ON f120_rowid = itc_proveedor.f125_rowid_item AND itc_proveedor.f125_id_plan = '015'
                LEFT JOIN t106_mc_criterios_item_mayores itcm4 
                       ON itc_proveedor.f125_id_plan = itcm4.f106_id_plan AND itc_proveedor.f125_id_criterio_mayor = itcm4.f106_id
                LEFT JOIN t125_mc_items_criterios itc_tipinv 
                       ON f120_rowid = itc_tipinv.f125_rowid_item AND itc_tipinv.f125_id_plan = '008'
                LEFT JOIN t106_mc_criterios_item_mayores itcm8 
                       ON itc_tipinv.f125_id_plan = itcm8.f106_id_plan AND itc_tipinv.f125_id_criterio_mayor = itcm8.f106_id

                OUTER APPLY (
                    SELECT TOP 1 cot.f212_precio
                    FROM t212_mm_cotizaciones cot
                    WHERE cot.f212_rowid_item_ext = f470_rowid_item_ext
                      AND cot.f212_id_um IN ('UND')
                      AND TRIM(itcm4.f106_id) = :prov
                      AND CAST(cot.f212_fecha_activacion AS DATE) <= CAST(f470_id_fecha AS DATE)
                    ORDER BY cot.f212_fecha_activacion DESC
                ) AS precio_vigente

                OUTER APPLY (
                    SELECT TOP 1 cot.f212_precio
                    FROM t212_mm_cotizaciones cot
                    WHERE cot.f212_rowid_item_ext = f470_rowid_item_ext
                      AND cot.f212_id_um IN ('UND')
                      AND TRIM(itcm4.f106_id) = :prov
                    ORDER BY cot.f212_fecha_activacion ASC
                ) AS precio_minimo

                WHERE TRIM(itcm4.f106_id) = :prov
                  AND itcm8.f106_id_plan = '008' 
                  AND itcm8.f106_id = '0001'
                  AND CAST(t350_fact.f350_fecha AS DATE) BETWEEN :fi AND :ff
                  AND f350_id_tipo_docto IN ('FEI')
            ) q
            GROUP BY
                q.Fecha,
                q.BodegaId,
                q.Bodega,
                q.Item,
                q.Codigo,
                q.CodigoBarras,
                q.Ext1,
                q.Ext2
        ";

        $rows = Yii::$app->dbSiesa->createCommand($sql, [
            ':fi'   => $pFi,
            ':ff'   => $pFf,
            ':prov' => $idProv,
        ])->queryAll();

        // === Consulta NIT proveedor (probada) ===
        $nitProv = Yii::$app->dbSiesa->createCommand("
            SELECT TOP 1 g.f200_nit
            FROM t125_mc_items_criterios ic
            JOIN t106_mc_criterios_item_mayores pr
              ON ic.f125_id_plan = pr.f106_id_plan 
             AND ic.f125_id_criterio_mayor = pr.f106_id
            JOIN t120_mc_items i
              ON ic.f125_rowid_item = i.f120_rowid
            JOIN t121_mc_items_extensiones ie
              ON ie.f121_rowid_item = i.f120_rowid AND ie.f121_id_cia = 7
            JOIN t131_mc_items_barras cb 
              ON cb.f131_rowid_item_ext = ie.f121_rowid AND cb.f131_id_cia = 7
            JOIN t477_cm_movto_consig m
              ON m.f477_rowid_item_ext = ie.f121_rowid AND m.f477_id_cia = 7
            JOIN t202_mm_proveedores p
              ON m.f477_rowid_tercero = p.f202_rowid_tercero
            JOIN t200_mm_terceros g
              ON p.f202_rowid_tercero = g.f200_rowid
            WHERE ic.f125_id_plan = '015'
              AND LTRIM(RTRIM(pr.f106_id)) = :prov
        ", [':prov'=>$idProv])->queryScalar();

        [$totUnidades, $totCosto] = $this->calcTotals($rows);

        Yii::$app->session->set('vmi_preview_rows', $rows);
        Yii::$app->session->set('vmi_preview_meta', [
            'idProv'     => $idProv,
            'provNom'    => $provNom,
            'nit'        => $nitProv ?: '',
            'sucursal'   => self::FIX_SUC_PROV,
            'fini'       => $fini,
            'ffin'       => $ffin,
            'totU'       => $totUnidades,
            'totC'       => $totCosto,
            'itemsCount' => count($rows),
        ]);

        $dp = new ArrayDataProvider([
            'allModels'  => $rows,
            'pagination' => ['pageSize' => 500],
        ]);

        return $this->render('preview', [
            'dp'         => $dp,
            'idProv'     => $idProv,
            'provNom'    => $provNom,
            'fini'       => $fini,
            'ffin'       => $ffin,
            'totUnidades'=> $totUnidades,
            'totCosto'   => $totCosto,
        ]);

    } catch (\Throwable $e) {
        Yii::$app->session->setFlash('error', 'Fallo al consultar: '.$e->getMessage());
        return $this->reRenderIndexWithValues($idProv, $fini, $ffin);
    }
}





    /** Totales */
    private function calcTotals(array $rows): array
    {
        $u = 0; $c = 0.0;
        foreach ($rows as $r) {
            $u += (int)($r['cantidad_base'] ?? 0);
            $c += (int)($r['costo_total'] ?? 0);
        }
        return [$u, $c];
    }

    // ... el resto de métodos (export, finalize, cancel, review) quedan igual ...


    /** Re-render index con valores */
    private function reRenderIndexWithValues($idProv, $fini, $ffin)
    {
        $proveedores = [];
        try {
            $proveedoresRaw = Yii::$app->dbSiesa->createCommand("
                SELECT DISTINCT LTRIM(RTRIM(f106_id)) AS id, LTRIM(RTRIM(f106_descripcion)) AS nombre
                FROM t106_mc_criterios_item_mayores
                WHERE f106_id_plan = '015'
                ORDER BY LTRIM(RTRIM(f106_descripcion))
            ")->queryAll();
            foreach ($proveedoresRaw as $r) {
                $proveedores[$r['id']] = $r['nombre'].' ('.$r['id'].')';
            }
        } catch (\Throwable $e) {}

        $model = new DynamicModel(['proveedor_id','fecha_inicio','fecha_fin']);
        $model->addRule(['proveedor_id','fecha_inicio','fecha_fin'], 'required')
              ->addRule(['fecha_inicio','fecha_fin'], 'date', ['format'=>'php:Y-m-d']);
        $model->setAttributes([
            'proveedor_id' => $idProv,
            'fecha_inicio' => $fini,
            'fecha_fin'    => $ffin,
        ], false);

        return $this->render('index', [
            'model'       => $model,
            'proveedores' => $proveedores,
            'show'        => !empty(Yii::$app->session->get('vmi_preview_meta', [])),
        ]);
    }



public function actionEnviarSiesaVmi()
{
    $rows = Yii::$app->session->get('vmi_preview_rows');
    $meta = Yii::$app->session->get('vmi_preview_meta', []);

    if (empty($rows) || empty($meta)) {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'success' => false,
                'estado' => 'error',
                'mensaje' => 'No hay datos para enviar. Ejecuta la consulta primero.',
            ];
        }
        Yii::$app->session->setFlash('error', 'No hay datos para enviar. Ejecuta la consulta primero.');
        return $this->redirect(['index']);
    }

    // ✅ Aseguramos que NIT y sucursal vengan correctos
    $proveedorNit = $meta['nit'] ?? '';
    $sucursalProv = self::FIX_SUC_PROV; // siempre '001'

    $doc = new \frontend\models\LogFactVmi([
        'consulta_id'        => $meta['consulta_id'] ?? 0,
        'usuario'            => Yii::$app->user->identity->username ?? 'system',
        'proveedor_id'       => $meta['idProv'],   // código interno
        'proveedor_nit'      => $proveedorNit,     // 👈 NIT real
        'fecha_inicio'       => $meta['fini'],
        'fecha_fin'          => $meta['ffin'],
        'total_unidades'     => $meta['totU'],
        'total_items_unicos' => $meta['itemsCount'],
        'total_costo'        => $meta['totC'],
        'consec_doc_prov'    => Yii::$app->request->post('consec_doc_prov'),
        'prefijo_doc_prov'   => Yii::$app->request->post('prefijo_doc_prov'),
        'fecha_doc'          => Yii::$app->request->post('fecha_doc'),
    ]);

    // Guardamos la sucursal en meta y en doc (si tienes campo en BD)
    $meta['sucursal'] = $sucursalProv;

    $result = $this->enviarASiesa($doc, $rows, false);

    // 🔹 Si es AJAX → devolver JSON
    if (Yii::$app->request->isAjax) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $this->formatResult($doc, $result);
    }

    // 🔹 Si no es AJAX → flujo normal
    return $this->redirect(['view-log', 'id' => $result['logId']]);
}

public function actionReenviar($id)
{
    $doc = LogFactVmi::find()->with('items')->where(['id' => $id])->one();
    if (!$doc) {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'success' => false,
                'id' => $id,
                'estado' => 'no encontrado',
                'fecha_realizado' => null,
            ];
        }
        throw new \yii\web\NotFoundHttpException("No existe el documento $id");
    }

    $result = $this->enviarASiesa($doc, $doc->items, true);

    if (Yii::$app->request->isAjax) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $this->formatResult($doc, $result);
    }

    return $this->redirect(['view-log', 'id' => $doc->id]);
}

/**
 * Helper para devolver la estructura JSON estándar
 */
private function formatResult($doc, $result)
{
    return [
        'success' => $result['success'],
        'id' => $doc->id,
        'estado' => $doc->estado,
        'fecha_realizado' => Yii::$app->formatter->asDatetime($doc->fecha_realizado, 'php:Y-m-d H:i'),
        'mensaje' => $result['mensaje'] ?? null,
    ];
}




private function enviarASiesa(\frontend\models\LogFactVmi $doc, $items = [], $actualizar = false)
{
    // === 0) Estado inicial ===
    $doc->estado          = 'procesando';
    $doc->fecha_realizado = new \yii\db\Expression('GETDATE()');
    $doc->save(false);

    // === 1) Agrupar ítems ===
    $agrupados = [];
    foreach ($items as $it) {
        $n = $this->normalizeItem($it);

        $key = implode('|', [
            trim($n['item']),
            trim($n['extension1']),
            trim($n['extension2']),
            $this->normalizeBodegaId($n['bodega']),
            trim($n['codigo_barras'] ?? ''),
        ]);

        if (!isset($agrupados[$key])) {
            $agrupados[$key] = [
                'item'            => trim($n['item']),
                'extension1'      => trim($n['extension1']),
                'extension2'      => trim($n['extension2']),
                'bodega'          => $this->normalizeBodegaId($n['bodega']),
                'codigo_barras'   => trim($n['codigo_barras'] ?? ''),
                'cantidad'        => (int)($n['cantidad'] ?? 0),
                'precio_unitario' => (int) floor($n['precio_unitario'] ?? 0),
            ];
        } else {
            $agrupados[$key]['cantidad'] += (int)($n['cantidad'] ?? 0);
        }
    }

    // === 2) Normalizar totales sin decimales ===
    $itemsFinal = [];
    $totCant  = 0;
    $totCosto = 0;

    foreach ($agrupados as $n) {
        $cantidad = (int)$n['cantidad'];
        $pu       = (int)$n['precio_unitario'];
        $costoTot = (int) floor($cantidad * $pu);

        if ($cantidad <= 0 || $pu <= 0) continue;

        $n['cantidad']        = $cantidad;
        $n['precio_unitario'] = $pu;
        $n['costo_total']     = $costoTot;

        $itemsFinal[] = $n;
        $totCant     += $cantidad;
        $totCosto    += $costoTot;
    }

    // === 3) Armar JSON (solo enteros) ===
    $json = [
        "Relación saldos por ítem V.3" => [],
        "Documentos" => [],
        "Cuotas CxP" => [],
    ];

    foreach ($itemsFinal as $n) {
        $json["Relación saldos por ítem V.3"][] = [
            "Centro de operación"       => self::FIX_CO,
            "Tipo de documento"         => self::FIX_TIPO_DOC,
            "Consecutivo de documento"  => self::FIX_CONSEC_DOC,
            "Item"                      => $n['item'],
            "Extension 1"               => $n['extension1'],
            "Extension 2"               => $n['extension2'],
            "Unidad de medida"          => "UND",
            "Bodega"                    => trim($n['bodega']),
            "Motivo"                    => self::FIX_MOTIVO,
            "Cantidad base"             => $n['cantidad'],
            "Precio unitario"           => $n['precio_unitario'],
            "Costo total"               => $n['costo_total'],
        ];
    }

    $json["Documentos"][] = [
        "Centro de operación"                => self::FIX_CO,
        "Tipo de documento"                  => self::FIX_TIPO_DOC,
        "Consecutivo de documento"           => self::FIX_CONSEC_DOC,
        "Fecha del documento AAAAMMDD"       => $doc->fecha_doc ? date('Ymd', strtotime($doc->fecha_doc)) : '',
        "Tercero proveedor"                  => $doc->proveedor_nit,
        "Sucursal proveedor"                 => self::FIX_SUC_PROV,
        "Prefijo documento proveedor"        => $doc->prefijo_doc_prov,
        "Consecutivo documento proveedor"    => (int)preg_replace('/\D/', '', (string)$doc->consec_doc_prov),
        "Fecha documento proveedor AAAAMMDD" => $doc->fecha_doc ? date('Ymd', strtotime($doc->fecha_doc)) : '',
        "Condición de pago"                  => self::FIX_COND_PAGO,
        "Valor del documento"                => (int)$totCosto, // ✅ mismo que total
        "Tipo de proveedor"                  => "0210",
    ];

    $json["Cuotas CxP"][] = [
        "Centro de operación del documento"  => self::FIX_CO,
        "Tipo de documento"                  => self::FIX_TIPO_DOC,
        "Numero de documento"                => self::FIX_CONSEC_DOC,
        "Porcentaje de la cuota respecto al total del documento." => self::FIX_PORC_CUOTA,
        "Fecha de vencimiento de la cuota AAAAMMDD" => $doc->fecha_doc ? date('Ymd', strtotime("+15 days", strtotime($doc->fecha_doc))) : '',
        "fecha pronto pago"                  => $doc->fecha_doc ? date('Ymd', strtotime("+15 days", strtotime($doc->fecha_doc))) : '',
    ];

    // === 4) Envío a Siesa ===
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
        if ($response->isOk) $estadoLog = 'enviado';
    } catch (\Throwable $e) {
        $responseContent = $e->getMessage();
    }

    // === 5) Guardar log ===
    $doc->total_unidades = $totCant;
    $doc->total_costo    = $totCosto;
    $doc->estado         = $estadoLog;
    $doc->error_msg      = $responseContent;
    $doc->json_enviado   = json_encode($json, JSON_UNESCAPED_UNICODE);
    $doc->fecha_realizado = new \yii\db\Expression('GETDATE()');
    $doc->save(false);

    if ($actualizar) {
        \frontend\models\LogFactVmiItem::deleteAll(['log_id' => $doc->id]);
    }

    foreach ($itemsFinal as $n) {
        $doc->link('items', new \frontend\models\LogFactVmiItem([
            'item'            => $n['item'],
            'extension1'      => $n['extension1'],
            'extension2'      => $n['extension2'],
            'bodega'          => $n['bodega'],
            'codigo_barras'   => $n['codigo_barras'] ?? null,
            'cantidad'        => $n['cantidad'],
            'precio_unitario' => $n['precio_unitario'],
            'costo_total'     => $n['costo_total'],
        ]));
    }

    return [
        'success' => ($estadoLog === 'enviado'),
        'estado'  => $estadoLog,
        'mensaje' => $responseContent,
        'logId'   => $doc->id,
    ];
}




/** Exporta XLSX con 3 hojas */
public function actionExport()
{
    $rows = Yii::$app->session->get('vmi_preview_rows');
    $meta = Yii::$app->session->get('vmi_preview_meta', []);
    if (empty($rows) || empty($meta)) {
        Yii::$app->session->setFlash('error', 'No hay datos para exportar. Ejecuta la consulta primero.');
        return $this->redirect(['index']);
    }

    $req = Yii::$app->request;

    // Campos del usuario (H2 y H3)
    $terceroProv   = trim($req->post('tercero_proveedor', $meta['nit'] ?? '')); // 👈 corregido
    $prefijoProv   = trim($req->post('prefijo_doc_prov', ''));
    $consecProv    = trim($req->post('consec_doc_prov', ''));
    $tipoProveedor = '0210'; // fijo

    $fecDoc        = preg_replace('/[^0-9]/', '', (string)$req->post('fecha_doc'));
    $fecDocProv    = preg_replace('/[^0-9]/', '', (string)$req->post('fecha_doc_prov'));

    // Fechas de cuotas: intenta tomar las que vienen del front; si no, calcula con base a fecha_doc
    $fecVencAAAAMMDD   = preg_replace('/[^0-9]/', '', (string)$req->post('fecha_vencimiento'));
    $fecProntoAAAAMMDD = preg_replace('/[^0-9]/', '', (string)$req->post('fecha_pronto_pago'));

    if ((!$fecVencAAAAMMDD || !$fecProntoAAAAMMDD) && $fecDoc && strlen($fecDoc) === 8) {
        try {
            $base = \DateTime::createFromFormat('Ymd', $fecDoc);
            if ($base !== false) {
                $base->modify('+15 days');
                $calc = $base->format('Ymd');
                $fecVencAAAAMMDD   = $fecVencAAAAMMDD   ?: $calc;
                $fecProntoAAAAMMDD = $fecProntoAAAAMMDD ?: $calc;
            }
        } catch (\Throwable $e) {}
    }

    // Totales
    $totCosto = (int)($meta['totC'] ?? 0);

    // === Workbook
    $spread = new Spreadsheet();

    // Hoja 1
    $sh1 = $spread->getActiveSheet();
    $sh1->setTitle('Relación saldos por ítem V.3');
    $sh1->fromArray([
        'Centro de operación','Tipo de documento','Consecutivo de documento','Item',
        'Extension 1','Extension 2','Unidad de medida','Bodega','Motivo','Cantidad base','Precio unitario'
    ], null, 'A1');

    $row = 2;
    foreach ($rows as $r) {
        $sh1->fromArray([ self::FIX_CO, self::FIX_TIPO_DOC, self::FIX_CONSEC_DOC,
            $r['Item'] ?? '', $r['Ext1'] ?? '', $r['Ext2'] ?? '', $r['unidad_medida'] ?? 'UND',
            $r['BodegaId'] ?? '', self::FIX_MOTIVO,
            (int)($r['cantidad_base'] ?? 0),
            (int)($r['precio_unitario'] ?? 0),
        ], null, 'A'.$row);
        $row++;
    }

    // Hoja 2 (Documentos)
    $sh2 = $spread->createSheet();
    $sh2->setTitle('Documentos');
    $sh2->fromArray([
        'Centro de operación','Tipo de documento','Consecutivo de documento',
        'Fecha del documento AAAAMMDD','Tercero proveedor','Sucursal proveedor',
        'Prefijo documento proveedor','Consecutivo documento proveedor',
        'Fecha documento proveedor AAAAMMDD','Condición de pago','Valor del documento','Tipo de proveedor'
    ], null, 'A1');

    $sh2->fromArray([[
        self::FIX_CO, self::FIX_TIPO_DOC, self::FIX_CONSEC_DOC,
        $fecDoc, $terceroProv, self::FIX_SUC_PROV,
        $prefijoProv, $consecProv, $fecDocProv, self::FIX_COND_PAGO, round($totCosto, 0), $tipoProveedor
    ]], null, 'A2');

    // Hoja 3 (Cuotas CxP)
    $sh3 = $spread->createSheet();
    $sh3->setTitle('Cuotas CxP');
    $sh3->fromArray([
        'Centro de operación del documento','Tipo de documento','Numero de documento',
        'Porcentaje de la cuota respecto al total del documento.',
        'Fecha de vencimiento de la cuota AAAAMMDD','fecha pronto pago'
    ], null, 'A1');

    $sh3->fromArray([[
        self::FIX_CO, self::FIX_TIPO_DOC, self::FIX_CONSEC_DOC,
        self::FIX_PORC_CUOTA, $fecVencAAAAMMDD, $fecProntoAAAAMMDD
    ]], null, 'A2');

    foreach ([$sh1,$sh2,$sh3] as $sheet) {
        foreach (range('A','M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    $filename = 'VMI_'.($meta['idProv'] ?? 'prov').'_'.date('Ymd_His').'.xlsx';
    $writer = new Xlsx($spread);

    // === Entrega del archivo
    ob_start();
    $writer->save('php://output');
    $content = ob_get_clean();

    return Yii::$app->response->sendContentAsFile(
        $content,
        $filename,
        [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline'   => false,
        ]
    );
}



public function actionViewLog($id)
{
    $doc = LogFactVmi::find()
        ->with('items') // 👈 carga relación directo de BD
        ->where(['id' => $id])
        ->one();

    if (!$doc) {
        throw new \yii\web\NotFoundHttpException("No existe el documento $id");
    }

    $model = new \yii\base\DynamicModel(['consec_doc_prov','prefijo_doc_prov','fecha_doc']);
    $model->addRule(['consec_doc_prov'], 'integer')
          ->addRule(['prefijo_doc_prov'], 'string')
          ->addRule(['fecha_doc'], 'safe');

    $model->consec_doc_prov  = $doc->consec_doc_prov;
    $model->prefijo_doc_prov = $doc->prefijo_doc_prov;
    $model->fecha_doc        = $doc->fecha_doc;

    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        $doc->consec_doc_prov  = $model->consec_doc_prov;
        $doc->prefijo_doc_prov = $model->prefijo_doc_prov;
        $doc->fecha_doc        = $model->fecha_doc;
        $doc->save(false);

        // Reenviar con los ítems que ya están en BD
        return $this->redirect(['reenviar', 'id' => $doc->id]);
    }

    return $this->render('view-log', [
        'cab'   => $doc,
        'items' => $doc->items, // 👈 siempre desde BD
        'model' => $model,
    ]);
}







public function actionConciliacion($id = null)
{
    $rowsPreview = [];
    $meta        = [];

    if ($id) {
        // === Cargar desde BD (documento ya guardado) ===
        $doc = \frontend\models\LogFactVmi::find()->with('items')->where(['id' => $id])->one();
        if (!$doc) {
            Yii::$app->session->setFlash('error', "No existe el documento con ID $id");
            return $this->redirect(['index-log']);
        }

        foreach ($doc->items as $it) {
            $rowsPreview[] = [
                'Item'            => trim($it->item),
                'Ext1'            => trim($it->extension1),
                'Ext2'            => trim($it->extension2),
                'CodigoBarras'    => trim($it->codigo_barras ?? ''),
                'BodegaId'        => $this->normalizeBodegaId($it->bodega),
                'cantidad_base'   => $it->cantidad,
                'precio_unitario' => $it->precio_unitario,
                'costo_total'     => $it->costo_total,
                'Bodega'          => $this->normalizeBodegaId($it->bodega),
            ];
        }

        $meta = [
            'idProv'     => $doc->proveedor_id,
            'fini'       => $doc->fecha_inicio,
            'ffin'       => $doc->fecha_fin,
            'totU'       => $doc->total_unidades,
            'totC'       => $doc->total_costo,
            'itemsCount' => count($rowsPreview),
        ];
    } else {
        // === Flujo normal: cargar desde sesión ===
        $rowsPreview = Yii::$app->session->get('vmi_preview_rows', []);
        $meta        = Yii::$app->session->get('vmi_preview_meta', []);

        foreach ($rowsPreview as &$r) {
            $r['BodegaId']     = $this->normalizeBodegaId($r['BodegaId'] ?? '');
            $r['Bodega']       = $this->normalizeBodegaId($r['Bodega'] ?? '');
            $r['CodigoBarras'] = trim($r['CodigoBarras'] ?? '');
        }
        unset($r);
    }

    if (empty($rowsPreview)) {
        Yii::$app->session->setFlash('warning', 'No hay datos cargados para conciliar.');
        return $this->redirect(['index']);
    }

    // === 1. Armar lista de códigos de barras ===
    $codigosBarras = array_filter(array_map('trim', array_column($rowsPreview, 'CodigoBarras')));
    if (empty($codigosBarras)) {
        Yii::$app->session->setFlash('error', 'El preview no contiene códigos de barras válidos.');
        return $this->redirect(['index']);
    }

    // === 2. Traer existencias ===
    $placeholders = [];
    $params = [];
    foreach ($codigosBarras as $i => $codigo) {
        $key = ":cb$i";
        $placeholders[] = $key;
        $params[$key] = $codigo;
    }
    $inClause = implode(',', $placeholders);

    $sql = "
        SELECT 
            LTRIM(RTRIM(cb.f131_id)) AS codbar,
            LTRIM(RTRIM(b.f150_id)) AS bodega,
            LTRIM(RTRIM(b.f150_descripcion)) AS nombre,
            SUM(CASE WHEN m.f477_ind_naturaleza_consig = 2 
                     THEN -1*m.f477_cant_1 ELSE m.f477_cant_1 END) AS existencia,
            SUM(CASE WHEN m.f477_ind_naturaleza_consig = 2 
                     THEN -1*m.f477_costo_prom_tot ELSE m.f477_costo_prom_tot END) AS costo
        FROM t477_cm_movto_consig m
        JOIN t121_mc_items_extensiones ie ON m.f477_rowid_item_ext = ie.f121_rowid AND ie.f121_id_cia = 7
        JOIN t150_mc_bodegas b ON m.f477_rowid_bodega = b.f150_rowid AND b.f150_id_cia = 7
        JOIN t131_mc_items_barras cb ON cb.f131_rowid_item_ext = ie.f121_rowid AND cb.f131_id_cia = 7
        WHERE m.f477_id_cia = 7
          AND m.f477_ind_estado_cm != 2
          AND cb.f131_id IN ($inClause)
        GROUP BY cb.f131_id, b.f150_id, b.f150_descripcion
    ";

    $existencias = Yii::$app->dbSiesa->createCommand($sql, $params)->queryAll();

    // === 3. Reindexar resultados ===
    $mapExist = [];
    foreach ($existencias as $ex) {
        $codbar = trim($ex['codbar']);
        $bodega = $this->normalizeBodegaId($ex['bodega']);
        $existencia = (int)$ex['existencia'];
        $costo      = (int)$ex['costo'];

        if ($existencia <= 0) {
            continue; // descartar bodegas sin stock válido
        }

        $precioUnit = $existencia > 0 ? $costo / $existencia : 0;

        $mapExist[$codbar][$bodega] = [
            'existencia'      => $existencia,
            'costo'           => $costo,
            'precio_unitario' => $precioUnit,
            'nombre'          => trim($ex['nombre']),
        ];
    }

    // === 4. Procesar conciliación ===
    $conciliacion = [];
    $rowsPreviewFiltrado = [];
    $descartados = 0;
    $eliminados = [];

    foreach ($rowsPreview as $r) {
        $item   = trim($r['Item'] ?? '');
        $ext1   = trim($r['Ext1'] ?? '');
        $ext2   = trim($r['Ext2'] ?? '');
        $bodega = $this->normalizeBodegaId($r['BodegaId'] ?? '');
        $codigoBarras = trim($r['CodigoBarras'] ?? '');

        if (!$item || !$bodega || !$codigoBarras) {
            $eliminados[] = [
                'Item'         => $item,
                'CodigoBarras' => $codigoBarras,
                'Bodega'       => $bodega,
                'Motivo'       => 'Faltan datos clave (item/bodega/código de barras)'
            ];
            $descartados++;
            continue;
        }

        $cantFila  = (int)($r['cantidad_base'] ?? 0);
        $lineasGeneradas = [];

        $restante = $cantFila;

        // 1. Revisar bodega original
        $cantBd   = $mapExist[$codigoBarras][$bodega]['existencia'] ?? 0;
        $puBd     = $mapExist[$codigoBarras][$bodega]['precio_unitario'] ?? 0;

        if ($cantBd > 0) {
            $usar = min($cantBd, $restante);
            $lineasGeneradas[] = [
                'item'            => $item,
                'ext1'            => $ext1,
                'ext2'            => $ext2,
                'codigo_barras'   => $codigoBarras,
                'bodega_original' => $bodega,
                'bodega_final'    => $bodega,
                'cant_preview'    => $usar,
                'precio_unitario' => $puBd,
                'costo_preview'   => $usar * $puBd,
                'reemplazo'       => null,
            ];
            $mapExist[$codigoBarras][$bodega]['existencia'] -= $usar;
            $restante -= $usar;
        }

        // 2. Buscar en otras bodegas
        if ($restante > 0) {
            $otras = $mapExist[$codigoBarras] ?? [];
            unset($otras[$bodega]);
            uasort($otras, fn($a, $b) => $b['existencia'] <=> $a['existencia']);

            foreach ($otras as $bodId => $info) {
                if ($restante <= 0) break;
                if ($info['existencia'] <= 0) continue;

                $usar = min($info['existencia'], $restante);
                $lineasGeneradas[] = [
                    'item'            => $item,
                    'ext1'            => $ext1,
                    'ext2'            => $ext2,
                    'codigo_barras'   => $codigoBarras,
                    'bodega_original' => $bodega,
                    'bodega_final'    => $bodId,
                    'cant_preview'    => $usar,
                    'precio_unitario' => $info['precio_unitario'],
                    'costo_preview'   => $usar * $info['precio_unitario'],
                    'reemplazo'       => $info['nombre'],
                ];
                $mapExist[$codigoBarras][$bodId]['existencia'] -= $usar;
                $restante -= $usar;
            }
        }

        // 🚨 Validación: si no se cubrió toda la cantidad, descartar todo el ítem
        if ($restante > 0) {
            $eliminados[] = [
                'Item'         => $item,
                'CodigoBarras' => $codigoBarras,
                'Bodega'       => $bodega,
                'Motivo'       => "No se encontró stock suficiente (faltaban $restante unidades)"
            ];
            $descartados++;
            continue; // no pasa nada de este ítem a conciliación
        }

        // ✅ Solo si se cubrió toda la cantidad
        foreach ($lineasGeneradas as $ln) {
            if ($ln['cant_preview'] <= 0) continue;
            $precioUnit = (float)$ln['precio_unitario'] ?: (float)($r['precio_unitario'] ?? 0);

            $conciliacion[] = $ln;
            $rowsPreviewFiltrado[] = [
                'Item'            => $ln['item'],
                'Ext1'            => $ln['ext1'],
                'Ext2'            => $ln['ext2'],
                'CodigoBarras'    => $ln['codigo_barras'],
                'BodegaId'        => $ln['bodega_final'],
                'Bodega'          => $ln['reemplazo'] ?: $r['Bodega'],
                'cantidad_base'   => $ln['cant_preview'],
                'precio_unitario' => $precioUnit,
                'costo_total'     => $ln['cant_preview'] * $precioUnit,
            ];
        }
    }

    // === 5. Totales ===
    [$totUnidades, $totCosto] = $this->calcTotals($rowsPreviewFiltrado);

    Yii::$app->session->set('vmi_preview_rows', $rowsPreviewFiltrado);
    Yii::$app->session->set('vmi_preview_meta', array_merge($meta, [
        'totU'       => $totUnidades,
        'totC'       => $totCosto,
        'itemsCount' => count($rowsPreviewFiltrado),
    ]));

    return $this->render('conciliacion', [
        'conciliacion' => $conciliacion,
        'descartados'  => $descartados,
        'eliminados'   => $eliminados,
    ]);
}




/**
 * Normaliza IDs de bodega a 3 dígitos, ej: "33" → "033".
 */
private function normalizeBodegaId($val): string {
    $val = trim((string)$val);
    return ctype_digit($val) ? str_pad($val, 3, '0', STR_PAD_LEFT) : $val;
}



/**
 * Normaliza un item para que siempre devuelva un array estándar
 * y la bodega en formato de 3 dígitos (ej: '033').
 */
private function normalizeItem($it): array {
    if (is_array($it)) {
        // Intentar obtener el código de barras desde varios nombres
        $cb = $it['CodigoBarras'] 
              ?? $it['codigo_barras'] 
              ?? (isset($it['Codigo']) && strpos($it['Codigo'], '-') !== false
                    ? explode('-', $it['Codigo'])[1]
                    : '');

        return [
            'item'            => $it['item']            ?? $it['Item']          ?? '',
            'extension1'      => $it['extension1']      ?? $it['Ext1']          ?? '',
            'extension2'      => $it['extension2']      ?? $it['Ext2']          ?? '',
            'bodega'          => $this->normalizeBodegaId($it['bodega'] ?? $it['BodegaId'] ?? ''),
            'cantidad'        => (int)($it['cantidad']  ?? $it['cantidad_base'] ?? 0),
            'precio_unitario' => (float)($it['precio_unitario'] ?? $it['PrecioUnitario'] ?? $it['precio_unitario'] ?? 0),
            'costo_total'     => (int)($it['costo_total']     ?? $it['CostoTotal']    ?? $it['costo_total'] ?? 0),
            'codigo_barras'   => trim((string)$cb),  // 👈 IMPORTANTE
        ];
    }

    // ActiveRecord
    return [
        'item'            => $it->item            ?? '',
        'extension1'      => $it->extension1      ?? '',
        'extension2'      => $it->extension2      ?? '',
        'bodega'          => $this->normalizeBodegaId($it->bodega ?? ''),
        'cantidad'        => (int)($it->cantidad        ?? 0),
        'precio_unitario' => (float)($it->precio_unitario ?? 0),
        'costo_total'     => (int)($it->costo_total     ?? 0),
        'codigo_barras'   => trim((string)($it->codigo_barras ?? '')), // 👈
    ];
}



public function actionIndexLog()
{
    $query = (new \yii\db\Query())
        ->from('dbo.LogFactVmi')
        ->orderBy(['fecha_realizado' => SORT_DESC]);

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'pagination' => ['pageSize' => 20],
    ]);

    return $this->render('index-log', [
        'dataProvider' => $dataProvider,
    ]);
}


public function actionEditarReenviar($id)
{
    $log = (new \yii\db\Query())
        ->from('dbo.LogFactVmi')
        ->where(['id' => $id])
        ->one();

    if (!$log || empty($log['json_enviado'])) {
        Yii::$app->session->setFlash('error', 'No se encontró JSON guardado para editar/reenviar.');
        return $this->redirect(['index']);
    }

    $json = json_decode($log['json_enviado'], true);

    $model = new \yii\base\DynamicModel([
        'consec_doc_prov','prefijo_doc_prov','fecha_doc_prov'
    ]);
    $model->addRule(['consec_doc_prov'], 'integer')
          ->addRule(['prefijo_doc_prov'], 'string')
          ->addRule(['fecha_doc_prov'], 'safe');

    // Pre-cargar valores guardados
    $documento = $json['Documentos'][0] ?? [];
    $model->consec_doc_prov  = $documento['Consecutivo documento proveedor'] ?? '';
    $model->prefijo_doc_prov = $documento['Prefijo documento proveedor'] ?? '';
    $model->fecha_doc_prov   = $documento['Fecha documento proveedor AAAAMMDD'] ?? '';

    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        // Actualizar JSON con los cambios
        $json['Documentos'][0]['Consecutivo documento proveedor']    = $model->consec_doc_prov;
        $json['Documentos'][0]['Prefijo documento proveedor']        = $model->prefijo_doc_prov;
        $json['Documentos'][0]['Fecha documento proveedor AAAAMMDD'] = $model->fecha_doc_prov;

        // Guardar en sesión y reutilizar el mismo flujo de envío
       Yii::$app->session->set('vmi_preview_rows', $json["Relación saldos por ítem V.3"] ?? []);
Yii::$app->session->set('vmi_preview_meta', [
    'idProv'        => $log['proveedor_id'],      // código interno
    'nit'           => $log['proveedor_nit'] ?? '', // 👈 NIT real
    'sucursal'      => self::FIX_SUC_PROV,        // 👈 siempre '001'
    'fini'          => $log['fecha_inicio'],
    'ffin'          => $log['fecha_fin'],
    'totU'          => $log['total_unidades'],
    'itemsCount'    => $log['total_items_unicos'],
    'totC'          => $log['total_costo'],
    'consulta_id'   => $log['consulta_id'],
]);


        // Pasamos el JSON modificado al envío
        return $this->runAction('enviar-siesa-vmi');
    }

    return $this->render('reenviar-form', [
        'model' => $model,
        'log'   => $log,
    ]);
}




public function actionEnviarJob($id)
{
    $doc = \frontend\models\LogFactVmi::find()
        ->with('items')
        ->where(['id' => $id])
        ->one();

    if (!$doc) {
        echo "No existe el documento con ID $id\n";
        return 1;
    }

    // 🚀 Procesar en background
    $this->enviarASiesa($doc, $doc->items, true);

    echo "Documento $id procesado\n";
    return 0;
}




}
