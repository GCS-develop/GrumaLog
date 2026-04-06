<?php

namespace common\models;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Parser del archivo Monacho (pedido a proveedor) y generador EAN13.
 *
 * Estructura EAN13 empresa:
 *   99 + IIIIII (código item, 6 dígitos) + CCCC (contador global, 4 dígitos) + DC (dígito control GS1)
 *
 * Estructura Monacho (Excel Hoja2):
 *   - 4 productos por bloque de filas (cols B, J, R, Z → 1-idx: 2, 10, 18, 26)
 *   - Bloques detectados por etiqueta 'DESCRIPCION:' en col B
 *   - Offsets fijos desde la fila del bloque
 */
class MonachoParser
{
    /** @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet */
    private $sheet;

    // Columnas base de cada producto dentro de un bloque (1-indexado)
    const PRODUCT_COLS = [2, 10, 18, 26];

    // Offsets de fila desde la fila de inicio del bloque ('DESCRIPCION:')
    const OFF_DESC         = 1;   // Descripción del artículo
    const OFF_ESTILO12_VAL = 3;   // Valores Estilo 1 (base) y Estilo 2 (base+3)
    const OFF_ESTILO34_VAL = 5;   // Valores Estilo 3 (base) y Estilo 4 (base+3)
    const OFF_ESTILO5_VAL  = 7;   // Valor Estilo 5 (base) y Concepto (base+3)
    const OFF_CONSUMIDOR   = 9;   // Consumidor (base) y Universo (base+3)
    const OFF_PRENDA       = 11;  // Prenda (base) y Tendencia (base+3)
    const OFF_CODIGO_VAL   = 13;  // Código SIESA (base) y Referencia proveedor (base+3)
    const OFF_COSTO_VAL    = 17;  // Costo (base) y Precio venta (base+3)
    const OFF_RANGO_VAL    = 19;  // Margen (base) y Rango (base+3)
    const OFF_PROVEEDOR    = 21;  // Nombre proveedor (base)
    const OFF_PVP          = 22;  // PVP Mayorista (base+1)
    const OFF_OC_SIESA     = 26;  // Número OC SIESA (base)
    const OFF_OC_ICG       = 28;  // Número OC ICG (base)
    const OFF_FECHA        = 30;  // Fecha despacho (base)
    const OFF_TALLA_HDR    = 34;  // Fila cabecera tallas (COLOR | S | M | L | XL | - | TOTAL)
    const OFF_COLOR_START  = 35;  // Primera fila de datos de color+cantidades

    /**
     * Parsea el archivo Monacho y retorna array de productos.
     *
     * @param string $filePath Ruta al archivo .xlsx
     * @return array
     */
    public function parse($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $this->sheet = $spreadsheet->getSheet(0);

        $products    = [];
        $maxRow      = $this->sheet->getHighestRow();
        $blockRows   = [];

        // Detectar inicio de cada bloque por etiqueta 'DESCRIPCION:' en col B (=2)
        for ($row = 1; $row <= $maxRow; $row++) {
            $val = $this->cell(2, $row);
            if ($val === 'DESCRIPCION:') {
                $blockRows[] = $row;
            }
        }

        foreach ($blockRows as $blockRow) {
            foreach (self::PRODUCT_COLS as $baseCol) {
                $product = $this->extractProduct($blockRow, $baseCol);
                if ($product !== null) {
                    $products[] = $product;
                }
            }
        }

        return $products;
    }

    /**
     * Extrae un producto de un bloque dado y columna base.
     */
    private function extractProduct($blockRow, $baseCol)
    {
        $codigo = $this->cell($baseCol, $blockRow + self::OFF_CODIGO_VAL);

        if (empty($codigo) || !is_numeric($codigo)) {
            return null;
        }

        $tallaHeaders = $this->extractTallaHeaders($blockRow, $baseCol);
        $colores      = $this->extractColores($blockRow, $baseCol, $tallaHeaders);

        if (empty($colores)) {
            return null;
        }

        return [
            'codigo'       => (int) $codigo,
            'descripcion'  => trim((string)($this->cell($baseCol, $blockRow + self::OFF_DESC) ?? '')),
            'referencia'   => trim((string)($this->cell($baseCol + 3, $blockRow + self::OFF_CODIGO_VAL) ?? '')),
            'estilo1'      => trim((string)($this->cell($baseCol,     $blockRow + self::OFF_ESTILO12_VAL) ?? '')),
            'estilo2'      => trim((string)($this->cell($baseCol + 3, $blockRow + self::OFF_ESTILO12_VAL) ?? '')),
            'estilo3'      => trim((string)($this->cell($baseCol,     $blockRow + self::OFF_ESTILO34_VAL) ?? '')),
            'estilo4'      => trim((string)($this->cell($baseCol + 3, $blockRow + self::OFF_ESTILO34_VAL) ?? '')),
            'estilo5'      => trim((string)($this->cell($baseCol,     $blockRow + self::OFF_ESTILO5_VAL)  ?? '')),
            'concepto'     => trim((string)($this->cell($baseCol + 3, $blockRow + self::OFF_ESTILO5_VAL)  ?? '')),
            'consumidor'   => trim((string)($this->cell($baseCol,     $blockRow + self::OFF_CONSUMIDOR)   ?? '')),
            'universo'     => trim((string)($this->cell($baseCol + 3, $blockRow + self::OFF_CONSUMIDOR)   ?? '')),
            'prenda'       => trim((string)($this->cell($baseCol,     $blockRow + self::OFF_PRENDA)       ?? '')),
            'tendencia'    => trim((string)($this->cell($baseCol + 3, $blockRow + self::OFF_PRENDA)       ?? '')),
            'costo'        => $this->cell($baseCol,     $blockRow + self::OFF_COSTO_VAL) ?? 0,
            'precio_venta' => $this->cell($baseCol + 3, $blockRow + self::OFF_COSTO_VAL) ?? 0,
            'margen'       => $this->cell($baseCol,     $blockRow + self::OFF_RANGO_VAL) ?? 0,
            'rango'        => $this->cell($baseCol + 3, $blockRow + self::OFF_RANGO_VAL) ?? 0,
            'proveedor'    => trim((string)($this->cell($baseCol, $blockRow + self::OFF_PROVEEDOR) ?? '')),
            'pvp_mayorista'=> $this->cell($baseCol + 1, $blockRow + self::OFF_PVP) ?? 0,
            'oc_siesa'     => $this->cell($baseCol, $blockRow + self::OFF_OC_SIESA) ?? '',
            'oc_icg'       => $this->cell($baseCol, $blockRow + self::OFF_OC_ICG)   ?? '',
            'fecha_despacho' => $this->cell($baseCol, $blockRow + self::OFF_FECHA)  ?? '',
            'tallaHeaders' => $tallaHeaders,
            'colores'      => $colores,
            'ean_codes'    => [], // se llena en el controller
        ];
    }

    /**
     * Lee los encabezados de talla desde la fila de cabecera.
     * Empieza en base+1 y para en null o 'TOTAL'.
     */
    private function extractTallaHeaders($blockRow, $baseCol)
    {
        $headerRow = $blockRow + self::OFF_TALLA_HDR;
        $headers   = [];

        for ($offset = 1; $offset <= 8; $offset++) {
            $val = $this->cell($baseCol + $offset, $headerRow);
            if ($val === null || strtoupper(trim((string)$val)) === 'TOTAL') {
                break;
            }
            $headers[] = trim((string)$val);
        }

        return $headers;
    }

    /**
     * Lee los colores y cantidades por talla.
     */
    private function extractColores($blockRow, $baseCol, $tallaHeaders)
    {
        $colores  = [];
        $startRow = $blockRow + self::OFF_COLOR_START;

        for ($i = 0; $i < 15; $i++) {
            $colorVal = $this->cell($baseCol, $startRow + $i);

            if ($colorVal === null) {
                continue; // puede haber filas vacías entre colores
            }

            $colorStr = trim((string)$colorVal);

            if (strtoupper($colorStr) === 'TOTAL' || $colorStr === '') {
                break;
            }

            $cantidades = [];
            $total      = 0;

            foreach ($tallaHeaders as $j => $talla) {
                $qty = (int)($this->cell($baseCol + 1 + $j, $startRow + $i) ?? 0);
                $cantidades[$talla] = $qty;
                $total += $qty;
            }

            $colores[] = [
                'nombre'    => $colorStr,
                'cantidades'=> $cantidades,
                'total'     => $total,
            ];
        }

        return $colores;
    }

    /**
     * Lee el valor de una celda (col, row) en coordenadas 1-indexadas.
     */
    private function cell($col, $row)
    {
        return $this->sheet->getCell([$col, $row])->getValue();
    }

    // ─── EAN13 ───────────────────────────────────────────────────────────────

    /**
     * Genera un código EAN13 para un subartículo.
     *
     * Estructura: 99 + IIIIII (item, 6 dígitos) + CCCC (contador, 4 dígitos) + DC
     *
     * @param int $codigoItem  Código interno SIESA del artículo
     * @param int $contador    Contador global de subartículos (se incrementa externamente)
     * @return string          13 dígitos
     */
    public static function generarEan13($codigoItem, $contador)
    {
        $item    = str_pad((string)$codigoItem, 6, '0', STR_PAD_LEFT);
        $counter = str_pad((string)$contador,   4, '0', STR_PAD_LEFT);
        $base12  = '99' . $item . $counter;
        $dc      = self::calcularDigitoControl($base12);

        return $base12 . $dc;
    }

    /**
     * Calcula el dígito de control GS1 para 12 dígitos.
     */
    public static function calcularDigitoControl($digits12)
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int)$digits12[$i];
            $sum  += ($i % 2 === 0) ? $digit : $digit * 3;
        }
        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Verifica que un EAN13 sea válido.
     */
    public static function validarEan13($ean)
    {
        if (strlen($ean) !== 13 || !ctype_digit($ean)) {
            return false;
        }
        $dc = self::calcularDigitoControl(substr($ean, 0, 12));
        return (int)$ean[12] === $dc;
    }

    // ─── Exportar Excel para SIESA ───────────────────────────────────────────

    /**
     * Genera el Excel de importación para SIESA (formato Hoja2 del XLSM).
     *
     * @param array $products  Resultado de parse() con ean_codes generados
     * @return Spreadsheet
     */
    public static function generarExcelSiesa(array $products)
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Articulos');

        $headers = [
            'CODIGO', 'COLOR', 'TALLA', 'REFERENCIA', 'DESCRIPADIC', 'DESCRIPCION',
            'CODBARRAS1', 'CODBARRAS2', 'CODBARRAS3', 'CODPROVEEDOR',
            'CODDEPARTAMENTO', 'DEPARTAMENTO', 'CODSECCION', 'SECCION',
            'CODFAMILIA', 'FAMILIA', 'CODSUBFAMILIA', 'SUBFAMILIA',
            'CARACTERISTICA2MARCA', 'CODMARCA', 'MARCA', 'CODLINEA', 'LINEA',
            'CODESTILO1', 'ESTILO1', 'CODESTILO2', 'ESTILO2',
            'CODESTILO3', 'ESTILO3', 'CODESTILO4', 'ESTILO4',
            'CODESTILO5', 'ESTILO5', 'RANGO', 'CLPAIS',
            'CANTIDAD', 'MEDIDA', 'TIPOARTICULO', 'TipoInv', 'USA_STOCK',
            'IMPCOMPRA', 'IMPVENTA', 'REGRETIVA', 'BASEIMPUESTO',
            'PV SUGERIDO', 'PVP3', 'PVP2', 'PVP1',
            'CCOMPRAS', 'CCVENTAS', 'CCCOSTO', 'CCDECOMPRAS', 'CCDEVVENTAS',
            'CCDECOSTOVENTAS', 'CCFALTANTES', 'CCSOBRANTES', 'UNIDVENTA',
            'CCCOMPRASRS', 'CCDEVCOMPRASRS', 'CCCOSTOVTACONTRAP',
            'UNIDADMEDIDAPUM', 'FACTORCONVERSIONPUM',
        ];

        // Encabezados con estilo
        foreach ($headers as $col => $header) {
            $cell = $sheet->getCell([$col + 1, 1]);
            $cell->setValue($header);
            $cell->getStyle()->getFont()->setBold(true);
            $cell->getStyle()->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2C5F9E');
            $cell->getStyle()->getFont()->getColor()->setRGB('FFFFFF');
            $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $row = 2;
        foreach ($products as $product) {
            foreach ($product['colores'] as $colorData) {
                foreach ($colorData['cantidades'] as $talla => $cantidad) {
                    $ean = $product['ean_codes'][$colorData['nombre']][$talla] ?? '';

                    $rowData = [
                        $product['codigo'],          // CODIGO
                        $colorData['nombre'],         // COLOR
                        $talla,                       // TALLA
                        $product['referencia'],       // REFERENCIA
                        '',                           // DESCRIPADIC
                        $product['descripcion'],      // DESCRIPCION
                        $ean,                         // CODBARRAS1
                        '',                           // CODBARRAS2
                        '',                           // CODBARRAS3
                        '',                           // CODPROVEEDOR (requiere mapping)
                        '',                           // CODDEPARTAMENTO
                        '',                           // DEPARTAMENTO
                        '',                           // CODSECCION
                        '',                           // SECCION
                        '',                           // CODFAMILIA
                        $product['consumidor'],       // FAMILIA (Consumidor)
                        '',                           // CODSUBFAMILIA
                        $product['universo'],         // SUBFAMILIA (Universo)
                        '',                           // CARACTERISTICA2MARCA
                        '',                           // CODMARCA
                        '',                           // MARCA
                        '',                           // CODLINEA
                        $product['prenda'],           // LINEA (Prenda)
                        '',                           // CODESTILO1
                        $product['estilo1'],          // ESTILO1
                        '',                           // CODESTILO2
                        $product['estilo2'],          // ESTILO2
                        '',                           // CODESTILO3
                        $product['estilo3'],          // ESTILO3
                        '',                           // CODESTILO4
                        $product['estilo4'],          // ESTILO4
                        '',                           // CODESTILO5
                        $product['estilo5'],          // ESTILO5
                        $product['rango'],            // RANGO
                        '',                           // CLPAIS
                        1,                            // CANTIDAD
                        'UND',                        // MEDIDA
                        'PRENDA',                     // TIPOARTICULO
                        '0',                          // TipoInv
                        '',                           // USA_STOCK
                        '',                           // IMPCOMPRA
                        '',                           // IMPVENTA
                        '',                           // REGRETIVA
                        '',                           // BASEIMPUESTO
                        $product['precio_venta'],     // PV SUGERIDO
                        '',                           // PVP3
                        '',                           // PVP2
                        $product['pvp_mayorista'],    // PVP1 (mayorista)
                        '',                           // CCOMPRAS
                        '',                           // CCVENTAS
                        '',                           // CCCOSTO
                        '',                           // CCDECOMPRAS
                        '',                           // CCDEVVENTAS
                        '',                           // CCDECOSTOVENTAS
                        '',                           // CCFALTANTES
                        '',                           // CCSOBRANTES
                        '',                           // UNIDVENTA
                        '',                           // CCCOMPRASRS
                        '',                           // CCDEVCOMPRASRS
                        '',                           // CCCOSTOVTACONTRAP
                        'UND',                        // UNIDADMEDIDAPUM
                        1,                            // FACTORCONVERSIONPUM
                    ];

                    foreach ($rowData as $col => $value) {
                        $sheet->getCell([$col + 1, $row])->setValue($value);
                    }

                    // Filas alternadas
                    if ($row % 2 === 0) {
                        $sheet->getStyle([1, $row, count($headers), $row])
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F0F4FA');
                    }

                    $row++;
                }
            }
        }

        // Autofit columnas principales
        foreach (range(1, min(10, count($headers))) as $col) {
            $sheet->getColumnDimension(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col)
            )->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
