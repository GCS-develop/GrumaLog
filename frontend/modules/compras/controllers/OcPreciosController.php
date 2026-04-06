<?php

namespace frontend\modules\compras\controllers;

use Yii;
use yii\web\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * OcPreciosController — Consulta de Órdenes de Compra con Precios Vigentes
 * Módulo: compras
 * Propósito: Mostrar OC con el último precio vigente del ítem (lista 001),
 *            en lugar del precio histórico grabado en la OC.
 */
class OcPreciosController extends Controller
{
    /**
     * Acción principal — muestra el formulario de filtros y la grilla de resultados
     */
    public function actionIndex()
    {
        $get = Yii::$app->request->get();

        $filtros = [
            'nro_orden'     => $get['nro_orden']     ?? null,
            'tipo_docto'    => $get['tipo_docto']    ?? null,
            'fecha_ord_ini' => $get['fecha_ord_ini'] ?? null,
            'fecha_ord_fin' => $get['fecha_ord_fin'] ?? null,
            'proveedor'     => $get['proveedor']     ?? null,
        ];

        // Solo ejecutar consulta si hay al menos un filtro aplicado
        $hayFiltro = array_filter($filtros, fn($v) => $v !== null && $v !== '');
        $datos = $hayFiltro ? $this->ejecutarConsulta($filtros) : [];

        return $this->render('index', [
            'datos'      => $datos,
            'filtros'    => $filtros,
            'hayFiltro'  => !empty($hayFiltro),
            'tiposDocto' => $this->getTiposDocto(),
            'estados'    => $this->getEstados(),
        ]);
    }

    /**
     * Acción de exportación a Excel
     */
    public function actionExportar()
    {
        $get = Yii::$app->request->get();

        $filtros = [
            'nro_orden'     => $get['nro_orden']     ?? null,
            'tipo_docto'    => $get['tipo_docto']    ?? null,
            'fecha_ord_ini' => $get['fecha_ord_ini'] ?? null,
            'fecha_ord_fin' => $get['fecha_ord_fin'] ?? null,
            'proveedor'     => $get['proveedor']     ?? null,
        ];

        $datos = $this->ejecutarConsulta($filtros);

        // Crear Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('OC Precios Vigentes');

        // --- Encabezados ---
        $headers = [
            'A' => 'Nro Orden',
            'B' => 'Tipo Docto',
            'C' => 'Razón Social Proveedor',
            'D' => 'Ítem',
            'E' => 'Referencia',
            'F' => 'Color',
            'G' => 'Talla',
            'H' => 'Código Barras',
            'I' => 'Descripción',
            'J' => 'Cant. Ordenada',
            'K' => 'Fecha Entrega',
            'L' => 'Precio Vigente',
        ];

        foreach ($headers as $col => $titulo) {
            $sheet->setCellValue($col . '1', $titulo);
        }

        // Estilo encabezado
        $rangoHeader = 'A1:L1';
        $sheet->getStyle($rangoHeader)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Autofilter
        $sheet->setAutoFilter('A1:L1');

        // --- Datos ---
        $row = 2;
        foreach ($datos as $d) {
            $sheet->setCellValue('A' . $row, $d['nro_orden']);
            $sheet->setCellValue('B' . $row, $d['tipo_docto']);
            $sheet->setCellValue('C' . $row, $d['razon_social_proveedor']);
            $sheet->setCellValue('D' . $row, $d['item']);
            $sheet->setCellValue('E' . $row, $d['referencia']);
            $sheet->setCellValue('F' . $row, $d['color']);
            $sheet->setCellValue('G' . $row, $d['talla']);
            // Código de barras como texto para evitar notación científica
            $sheet->setCellValueExplicit('H' . $row, (string)$d['codigo_barras'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('I' . $row, $d['desc_item']);
            $sheet->setCellValue('J' . $row, $d['cant_ordenada']);
            $sheet->setCellValue('K' . $row, $d['fecha_entrega']);

            if ($d['precio_vigente'] !== null) {
                $sheet->setCellValue('L' . $row, round((float)$d['precio_vigente'], 0));
                // Mostrar sin decimales
                $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('0');
            } else {
                $sheet->setCellValue('L' . $row, '');
            }

            // Color filas alternas
            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']],
                ]);
            }

            $row++;
        }

        // Ancho de columnas
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Congelar primera fila
        $sheet->freezePane('A2');

        // Descargar
        $filename = 'OC_Precios_Vigentes_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // =========================================================================
    // MÉTODOS PRIVADOS
    // =========================================================================

    /**
     * Ejecuta el query principal con los filtros recibidos
     */
    private function ejecutarConsulta(array $filtros): array
    {
        $db = Yii::$app->dbSiesa;

        $sql = "
            SELECT
                oc.f420_consec_docto                             AS nro_orden,
                oc.f420_id_tipo_docto                            AS tipo_docto,
                prov.f202_descripcion_sucursal                   AS razon_social_proveedor,
                item.f120_id                                     AS item,
                RTRIM(item.f120_referencia)                      AS referencia,
                RTRIM(ext.f121_id_ext1_detalle)                  AS color,
                RTRIM(ext.f121_id_ext2_detalle)                  AS talla,
                RTRIM(ext.f121_id_barras_principal)              AS codigo_barras,
                RTRIM(item.f120_descripcion)                     AS desc_item,
                mov.f421_cant_pedida                             AS cant_ordenada,
                CONVERT(DATE, mov.f421_fecha_entrega)            AS fecha_entrega,
                precio_vigente.f126_precio                       AS precio_vigente,
                mov.f421_vlr_neto                                AS vlr_neto

            FROM t420_cm_oc_docto oc

            JOIN t421_cm_oc_movto mov
                ON  mov.f421_rowid_oc_docto = oc.f420_rowid
                AND mov.f421_id_cia         = 7

            JOIN t121_mc_items_extensiones ext
                ON  ext.f121_rowid  = mov.f421_rowid_item_ext
                AND ext.f121_id_cia = 7

            JOIN t120_mc_items item
                ON  item.f120_rowid  = ext.f121_rowid_item
                AND item.f120_id_cia = 7

            JOIN t202_mm_proveedores prov
                ON  prov.f202_rowid_tercero = oc.f420_rowid_tercero_prov
                AND prov.f202_id_cia        = 7
                AND prov.f202_id_sucursal   = '000'

            JOIN t200_mm_terceros ter_prov
                ON  ter_prov.f200_rowid  = oc.f420_rowid_tercero_prov
                AND ter_prov.f200_id_cia = 7

            OUTER APPLY (
                SELECT TOP 1
                    f126_precio,
                    f126_fecha_activacion,
                    f126_id_unidad_medida
                FROM t126_mc_items_precios
                WHERE f126_id_cia         = 7
                  AND f126_id_lista_precio = '001'
                  AND f126_rowid_item      = ext.f121_rowid_item
                ORDER BY
                    CASE WHEN RTRIM(f126_id_unidad_medida) LIKE 'PX%' THEN 0 ELSE 1 END ASC,
                    f126_fecha_activacion DESC
            ) precio_vigente

            WHERE oc.f420_id_cia = 7
        ";

        $params = [];

        if (!empty($filtros['nro_orden'])) {
            $sql .= " AND oc.f420_consec_docto = :nro_orden";
            $params[':nro_orden'] = $filtros['nro_orden'];
        }
        if (!empty($filtros['tipo_docto'])) {
            $sql .= " AND oc.f420_id_tipo_docto = :tipo_docto";
            $params[':tipo_docto'] = $filtros['tipo_docto'];
        }
        if (!empty($filtros['fecha_ord_ini'])) {
            $sql .= " AND CONVERT(DATE, oc.f420_fecha) >= :fecha_ord_ini";
            $params[':fecha_ord_ini'] = $filtros['fecha_ord_ini'];
        }
        if (!empty($filtros['fecha_ord_fin'])) {
            $sql .= " AND CONVERT(DATE, oc.f420_fecha) <= :fecha_ord_fin";
            $params[':fecha_ord_fin'] = $filtros['fecha_ord_fin'];
        }
        if (!empty($filtros['proveedor'])) {
            $sql .= " AND (RTRIM(ter_prov.f200_nit) LIKE :proveedor OR prov.f202_descripcion_sucursal LIKE :proveedor)";
            $params[':proveedor'] = '%' . $filtros['proveedor'] . '%';
        }

        $sql .= " ORDER BY oc.f420_fecha DESC, oc.f420_consec_docto, mov.f421_rowid";

        return $db->createCommand($sql, $params)->queryAll();
    }

    /**
     * Obtiene lista de tipos de documento distintos para el dropdown
     */
    private function getTiposDocto(): array
    {
        $rows = Yii::$app->dbSiesa->createCommand("
            SELECT DISTINCT f420_id_tipo_docto
            FROM t420_cm_oc_docto
            WHERE f420_id_cia = 7
            ORDER BY f420_id_tipo_docto
        ")->queryAll();

        $result = ['' => '-- Todos --'];
        foreach ($rows as $row) {
            $v = trim($row['f420_id_tipo_docto']);
            $result[$v] = $v;
        }
        return $result;
    }

    /**
     * Obtiene lista de estados del grupo 404 para el dropdown
     */
    private function getEstados(): array
    {
        return [
            ''  => '-- Todos --',
            '0' => 'En elaboración',
            '1' => 'Aprobado',
            '2' => 'Rechazado',
            '3' => 'En CDP',
            '9' => 'Anulado',
        ];
    }

    /**
     * Acción para generar PDF de Orden de Compra
     */
    public function actionPdf()
    {
        $nro_orden  = Yii::$app->request->get('nro_orden');
        $tipo_docto = Yii::$app->request->get('tipo_docto');

        if (!$nro_orden) {
            Yii::$app->session->setFlash('error', 'Debe seleccionar una OC para generar el PDF.');
            return $this->redirect(['index']);
        }

        $db = Yii::$app->dbSiesa;

        // --- Query 1: Cabecera ---
        $cabecera = $db->createCommand("
            SELECT TOP 1
                oc.f420_consec_docto                        AS nro_orden,
                oc.f420_id_tipo_docto                       AS tipo_docto,
                CONVERT(DATE, oc.f420_fecha)                AS fecha_orden,
                prov.f202_descripcion_sucursal              AS razon_social,
                RTRIM(ter.f200_nit)                         AS nit_proveedor,
                RTRIM(cont.f015_direccion1)                 AS direccion_proveedor,
                RTRIM(ci.f013_descripcion)                  AS ciudad_proveedor,
                RTRIM(cont.f015_telefono)                   AS telefono_proveedor,
                RTRIM(oc.f420_id_cond_pago)                 AS id_cond_pago,
                RTRIM(cond.f208_descripcion)                AS forma_pago,
                RTRIM(oc.f420_id_co)                        AS bodega,
                RTRIM(oc.f420_id_moneda_docto)              AS moneda,
                ''                                          AS terminos_entrega,
                (SELECT MAX(CONVERT(DATE, m2.f421_fecha_entrega))
                 FROM t421_cm_oc_movto m2
                 WHERE m2.f421_rowid_oc_docto = oc.f420_rowid
                   AND m2.f421_id_cia = 7)                  AS fecha_max_entrega
            FROM t420_cm_oc_docto oc
            JOIN t202_mm_proveedores prov
                ON  prov.f202_rowid_tercero = oc.f420_rowid_tercero_prov
                AND prov.f202_id_cia        = 7
                AND prov.f202_id_sucursal   = '000'
            JOIN t200_mm_terceros ter
                ON  ter.f200_rowid  = oc.f420_rowid_tercero_prov
                AND ter.f200_id_cia = 7
            LEFT JOIN t015_mm_contactos cont
                ON  ter.f200_rowid_contacto = cont.f015_rowid
            LEFT JOIN t013_mm_ciudades ci
                ON  cont.f015_id_ciudad = ci.f013_id
                AND cont.f015_id_depto = ci.f013_id_depto
                AND cont.f015_id_pais  = ci.f013_id_pais
            LEFT JOIN t208_mm_condiciones_pago cond
                ON  cond.f208_id     = oc.f420_id_cond_pago
                AND cond.f208_id_cia = 7
            WHERE oc.f420_id_cia        = 7
              AND oc.f420_consec_docto  = :nro_orden
              AND oc.f420_id_tipo_docto = :tipo_docto
        ", [':nro_orden' => $nro_orden, ':tipo_docto' => $tipo_docto])->queryOne();

        if (!$cabecera) {
            Yii::$app->session->setFlash('error', 'OC no encontrada.');
            return $this->redirect(['index']);
        }

        // --- Query 2: Detalle ---
        $detalles = $db->createCommand("
            SELECT
                item.f120_id                                AS item_id,
                RTRIM(item.f120_referencia)                 AS referencia,
                RTRIM(item.f120_descripcion)                AS descripcion,
                RTRIM(ext.f121_id_ext1_detalle)             AS color,
                RTRIM(ext.f121_id_ext2_detalle)             AS talla,
                mov.f421_cant_pedida                        AS cant_pedida,
                RTRIM(mov.f421_id_unidad_medida)            AS unidad_medida,
                CONVERT(DATE, mov.f421_fecha_entrega)       AS fecha_entrega,
                precio_vigente.f126_precio                  AS precio_vigente,
                mov.f421_vlr_bruto                          AS vlr_bruto_oc,
                mov.f421_vlr_dscto_linea                    AS vlr_dscto_linea_oc,
                mov.f421_vlr_dscto_global                   AS vlr_dscto_global_oc,
                mov.f421_vlr_neto                           AS vlr_neto_oc,
                foto.f580_foto                              AS foto_blob
            FROM t421_cm_oc_movto mov
            JOIN t420_cm_oc_docto oc
                ON  oc.f420_rowid  = mov.f421_rowid_oc_docto
                AND oc.f420_id_cia = 7
            JOIN t121_mc_items_extensiones ext
                ON  ext.f121_rowid  = mov.f421_rowid_item_ext
                AND ext.f121_id_cia = 7
            JOIN t120_mc_items item
                ON  item.f120_rowid  = ext.f121_rowid_item
                AND item.f120_id_cia = 7
            OUTER APPLY (
                SELECT TOP 1
                    f126_precio,
                    f126_id_unidad_medida
                FROM t126_mc_items_precios
                WHERE f126_id_cia         = 7
                  AND f126_id_lista_precio = '001'
                  AND f126_rowid_item      = ext.f121_rowid_item
                ORDER BY
                    CASE WHEN RTRIM(f126_id_unidad_medida) LIKE 'PX%' THEN 0 ELSE 1 END ASC,
                    f126_fecha_activacion DESC
            ) precio_vigente
            LEFT JOIN t580_ff_fotos foto
                ON foto.f580_rowid = item.f120_rowid_foto
            WHERE oc.f420_id_cia        = 7
              AND oc.f420_consec_docto  = :nro_orden
              AND oc.f420_id_tipo_docto = :tipo_docto
            ORDER BY item.f120_id, ext.f121_id_ext1_detalle, ext.f121_id_ext2_detalle
        ", [':nro_orden' => $nro_orden, ':tipo_docto' => $tipo_docto])->queryAll();

        // --- PIVOT PHP ---
        $pivot  = [];
        $tallas = [];
        $fotos  = [];

        foreach ($detalles as $row) {
            $key = $row['item_id'] . '||' . $row['color'];
            if (!isset($pivot[$key])) {
                $pivot[$key] = [
                    'item_id'        => $row['item_id'],
                    'referencia'     => $row['referencia'],
                    'descripcion'    => $row['descripcion'],
                    'color'          => $row['color'],
                    'unidad_medida'  => $row['unidad_medida'],
                    'fecha_entrega'  => $row['fecha_entrega'],
                    'precio_vigente' => floatval($row['precio_vigente'] ?? 0),
                    'cant_total'     => 0,
                    'vlr_bruto'      => 0,
                    'vlr_descuento'  => 0,
                    'vlr_neto'       => 0,
                    'tallas'         => [],
                ];
            }
            $pivot[$key]['tallas'][$row['talla']]  = floatval($row['cant_pedida']);
            $pivot[$key]['cant_total']            += floatval($row['cant_pedida']);
            $pivot[$key]['vlr_bruto']             += floatval($row['vlr_bruto_oc']       ?? 0);
            $pivot[$key]['vlr_descuento']         += floatval($row['vlr_dscto_linea_oc'] ?? 0)
                                                   + floatval($row['vlr_dscto_global_oc'] ?? 0);
            $pivot[$key]['vlr_neto']              += floatval($row['vlr_neto_oc']         ?? 0);
            $tallas[$row['talla']]                 = true;

            if (!isset($fotos[$row['item_id']]) && !empty($row['foto_blob'])) {
                $fotos[$row['item_id']] = base64_encode($row['foto_blob']);
            }
        }

        // Ordenar tallas
        uksort($tallas, function($a, $b) {
            $an = is_numeric($a); $bn = is_numeric($b);
            if ($an && $bn) return (int)$a - (int)$b;
            if ($an) return -1; if ($bn) return 1;
            $order = ['XXS'=>0,'XS'=>1,'S'=>2,'M'=>3,'L'=>4,'XL'=>5,'XXL'=>6,'XXXL'=>7];
            return ($order[$a] ?? 99) - ($order[$b] ?? 99);
        });
        $tallasOrdenadas = array_keys($tallas);

        // Totales
        $totalBruto     = 0;
        $totalDescuento = 0;
        $totalNeto      = 0;
        foreach ($pivot as $p) {
            $totalBruto     += $p['vlr_bruto'];
            $totalDescuento += $p['vlr_descuento'];
            $totalNeto      += $p['vlr_neto'];
        }

        // --- Generar HTML para mPDF ---
        $moneda = $cabecera['moneda'] ?? 'COP';

        // Columnas de talla para el encabezado: dividir en numéricas y alfas
        $tallasNumericas = array_filter($tallasOrdenadas, 'is_numeric');
        $tallasAlfas     = array_filter($tallasOrdenadas, fn($t) => !is_numeric($t));
        $hayFilaTallas   = !empty($tallasNumericas) && !empty($tallasAlfas);

        // Función helper para formato de moneda
        $fmt = fn($n) => $moneda . number_format($n, 0, ',', '.');

        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body        { font-family: DejaVu Sans, sans-serif; font-size: 7.5pt; }
    table       { border-collapse: collapse; width: 100%; }
    td, th      { border: 1px solid #555; padding: 2px 3px; vertical-align: middle; }
    .no-border  { border: none; }
    .center     { text-align: center; }
    .right      { text-align: right; }
    .bold       { font-weight: bold; }
    .empresa    { text-align: center; font-size: 8.5pt; }
    .box-oc     { border: 1px solid #000; padding: 6px 10px; font-size: 9pt; line-height: 1.8; }
    .header-sec { background: #f0f0f0; font-weight: bold; font-size: 7pt; }
    img.foto    { max-width: 60px; max-height: 70px; }
    .talla-col  { text-align: center; width: 18px; font-size: 6.5pt; }
    .total-row  { background: #e8e8e8; font-weight: bold; }
</style>
</head>
<body>

<!-- ENCABEZADO -->
<table style="margin-bottom:6px;">
    <tr>
        <td class="no-border empresa" style="width:70%;">
            <strong>GRUPO MAYORISTA S.A.</strong><br>
            Nit: 900.091.175-4<br>
            CR 32 14 25<br>
            Cali - Valle del Cauca - Colombia<br>
            Teléfono / Phone Number: 3229221 &nbsp; Fax Number: 6850007
        </td>
        <td class="no-border" style="width:30%; vertical-align:top;">
            <div class="box-oc">
                <div class="center bold" style="font-size:10pt; margin-bottom:4px;">
                    *ORDEN DE COMPRA*<br>*PURCHASE ORDER*
                </div>
                <table style="border:none; width:100%; font-size:8.5pt;">
                    <tr>
                        <td style="border:none; padding:1px 0;"><strong>Número/Number:</strong></td>
                        <td style="border:none; padding:1px 0;"><?= htmlspecialchars($cabecera['tipo_docto'] . '-' . $cabecera['nro_orden']) ?></td>
                    </tr>
                    <tr>
                        <td style="border:none; padding:1px 0;"><strong>Fecha/Date:</strong></td>
                        <td style="border:none; padding:1px 0;"><?= htmlspecialchars($cabecera['fecha_orden']) ?></td>
                    </tr>
                    <tr>
                        <td style="border:none; padding:1px 0;"><strong>Página/Page:</strong></td>
                        <td style="border:none; padding:1px 0;">{PAGENO} / {nbpg}</td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

<!-- DATOS PROVEEDOR -->
<table style="margin-bottom:6px; font-size:7.5pt;">
    <tr>
        <td style="width:50%; border:1px solid #555; padding:4px;">
            <strong>Proveedor / Supplier:</strong>
                <?= htmlspecialchars($cabecera['razon_social']) ?><br>
            <strong>País / Country:</strong> Colombia<br>
            <strong>Dirección / Address:</strong>
                <?= htmlspecialchars($cabecera['direccion_proveedor'] ?? '') ?><br>
            <strong>Nit No./Tax Id.:</strong>
                <?= htmlspecialchars($cabecera['nit_proveedor'] ?? '') ?><br>
            <strong>Ciudad / City:</strong>
                <?= htmlspecialchars($cabecera['ciudad_proveedor'] ?? '') ?><br>
            <strong>Teléfono / Phone Number:</strong>
                <?= htmlspecialchars($cabecera['telefono_proveedor'] ?? '') ?><br>
            <strong>Moneda / Currency:</strong>
                <?= htmlspecialchars($cabecera['moneda'] ?? 'COP') ?><br>
            <strong>Forma de pago / Payment Terms:</strong>
                <?= htmlspecialchars(trim(($cabecera['id_cond_pago'] ?? '') . ' ' . ($cabecera['forma_pago'] ?? ''))) ?><br>
            <strong>Fecha máxima de Embarque / Delivery date:</strong>
                <?= htmlspecialchars($cabecera['fecha_max_entrega'] ?? '') ?>
        </td>
        <td style="width:50%; border:1px solid #555; padding:4px;">
            <strong>Nit No./Tax Id.:</strong>
                <?= htmlspecialchars($cabecera['nit_proveedor'] ?? '') ?><br>
            <strong>Ciudad / City:</strong>
                <?= htmlspecialchars($cabecera['ciudad_proveedor'] ?? '') ?><br>
            <strong>Teléfono / Phone Number:</strong>
                <?= htmlspecialchars($cabecera['telefono_proveedor'] ?? '') ?><br>
            <strong>Fax Number:</strong><br>
            <br>
            <strong>Términos de Negociación / Negotiation Terms:</strong>
                <?= htmlspecialchars($cabecera['terminos_entrega'] ?? '') ?><br>
            <strong>Descuento Financiero / Financial Discount:</strong> 0.00 %<br>
            <strong>Bodega que recibe / Warehouse:</strong>
                <?= htmlspecialchars($cabecera['bodega'] ?? '') ?>
        </td>
    </tr>
</table>

<!-- TABLA DE ÍTEMS -->
<?php
$colspanTallas = count($tallasOrdenadas);
$numCols = 6 + $colspanTallas + 5; // item,ref/foto,color,obs + tallas + cant,um,precio,dscto,total,fec
?>
<table>
    <!-- FILA 1 ENCABEZADO: columnas fijas + bloque tallas -->
    <tr class="header-sec" style="font-size:6.5pt;">
        <th rowspan="3" style="width:20px;">Item</th>
        <th rowspan="3" style="width:70px;">Referencia<br>Proveedor/<br>Suplier<br>Reference<br>* Note 1</th>
        <th rowspan="3" style="width:65px;">Fotografía /<br>Picture<br>* Note 1</th>
        <th rowspan="3" style="width:35px;">Color</th>
        <th rowspan="3" style="width:50px;">Observaciones/<br>Observation</th>
        <th rowspan="1" style="writing-mode:vertical-rl; transform:rotate(180deg); width:12px;">O<br>N<br>E<br>S<br>I<br>Z<br>E</th>
        <?php
        // Sub-encabezado: tallas numéricas en fila 1, alfas en fila 2
        // Simplificado: todas las tallas en filas 1 y 2 separadas
        foreach ($tallasNumericas as $t): ?>
            <th class="talla-col"><?= htmlspecialchars($t) ?></th>
        <?php endforeach; ?>
        <?php foreach ($tallasAlfas as $t): ?>
            <th class="talla-col"><?= htmlspecialchars($t) ?></th>
        <?php endforeach; ?>
        <th rowspan="3" class="right" style="width:35px;">Cantidad<br>/QTY</th>
        <th rowspan="3" style="width:22px;">U.M/<br>UNT</th>
        <th rowspan="3" class="right" style="width:55px;">Precio unit.<br>/Unit Price</th>
        <th rowspan="3" class="right" style="width:45px;">Descuentos/<br>Discount</th>
        <th rowspan="3" class="right" style="width:60px;">Valor Total/<br>Total Value</th>
        <th rowspan="3" style="width:45px;">Fecha de<br>Entrega /<br>Delivery<br>Date</th>
    </tr>
    <!-- FILA 2: etiquetas de talla (XXS,XS,S,M,L,XL,XXL) -->
    <tr class="header-sec" style="font-size:6.5pt;">
        <th class="talla-col"></th><!-- ONE SIZE -->
        <?php foreach ($tallasNumericas as $t): ?><th class="talla-col"><?= $t ?></th><?php endforeach; ?>
        <?php foreach ($tallasAlfas as $t): ?><th class="talla-col"><?= $t ?></th><?php endforeach; ?>
    </tr>
    <!-- FILA 3: equivalencias numéricas de talla (4,6,8...) — solo si existen alfas con num equiv -->
    <tr class="header-sec" style="font-size:6.5pt;">
        <th class="talla-col"></th>
        <?php foreach ($tallasNumericas as $t): ?><th class="talla-col"></th><?php endforeach; ?>
        <?php foreach ($tallasAlfas as $t): ?><th class="talla-col"></th><?php endforeach; ?>
    </tr>

    <!-- FILAS DE DATOS -->
    <?php $itemNum = 1; foreach ($pivot as $key => $p): ?>
    <?php
        $base64 = $fotos[$p['item_id']] ?? null;
        $imgTag = $base64
            ? '<img class="foto" src="data:image/jpeg;base64,' . $base64 . '">'
            : '';
    ?>
    <tr>
        <td class="center"><?= $itemNum++ ?></td>
        <td class="center"><?= htmlspecialchars($p['referencia']) ?></td>
        <td class="center"><?= $imgTag ?></td>
        <td class="center"><?= htmlspecialchars($p['color']) ?></td>
        <td></td><!-- Observaciones -->
        <td class="talla-col"></td><!-- ONE SIZE -->
        <?php foreach ($tallasNumericas as $t): ?>
            <td class="talla-col"><?= isset($p['tallas'][$t]) ? number_format($p['tallas'][$t], 0) : '' ?></td>
        <?php endforeach; ?>
        <?php foreach ($tallasAlfas as $t): ?>
            <td class="talla-col"><?= isset($p['tallas'][$t]) ? number_format($p['tallas'][$t], 0) : '' ?></td>
        <?php endforeach; ?>
        <td class="right"><?= number_format($p['cant_total'], 0) ?></td>
        <td class="center"><?= htmlspecialchars($p['unidad_medida']) ?></td>
        <td class="right"><?= $fmt($p['precio_vigente']) ?></td>
        <td class="right"><?= $fmt($p['vlr_descuento']) ?></td>
        <td class="right"><?= $fmt($p['vlr_neto']) ?></td>
        <td class="center"><?= htmlspecialchars($p['fecha_entrega']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- TOTALES -->
<table style="margin-top:4px;">
    <tr class="total-row">
        <td class="right" style="width:33%;">
            <strong>Total bruto / Subtotal</strong><br>
            <?= $fmt($totalBruto) ?>
        </td>
        <td class="right" style="width:33%;">
            <strong>Total Descuento / Total Discount</strong><br>
            <?= $fmt($totalDescuento) ?>
        </td>
        <td class="right" style="width:34%;">
            <strong>Valor Total / Total Value</strong><br>
            <?= $fmt($totalNeto) ?>
        </td>
    </tr>
</table>

</body>
</html>
    <?php
    $html = ob_get_clean();

    // Generar PDF con mPDF
    $mpdf = new \Mpdf\Mpdf([
        'mode'          => 'utf-8',
        'format'        => 'A4-L',   // Landscape para que quepan las tallas
        'margin_top'    => 8,
        'margin_bottom' => 8,
        'margin_left'   => 8,
        'margin_right'  => 8,
    ]);
    $mpdf->SetTitle('OC ' . $nro_orden);
    $mpdf->WriteHTML($html);

    $filename = 'OC_' . $nro_orden . '_' . date('Ymd') . '.pdf';
    $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
    exit;
}
}
