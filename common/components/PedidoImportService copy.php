<?php
namespace common\components;

use Yii;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

use frontend\models\Pedido;
use frontend\models\PedidoDetalle; // tu AR del detalle
use frontend\models\Bodegas;
use frontend\models\Color;
use frontend\models\Talla;
use frontend\models\Item;

class PedidoImportService
{
    /**
     * Importa un libro Excel:
     * - ACTUALIZA un Pedido existente con fecha (C1), bodega (A5 y B5),
     *   totalItems (número de pestañas) y totalUnidades (suma de todas las unidades grabadas)
     *
     * @param int    $idpedido    ID del Pedido ya creado
     * @param string $filePath    Ruta absoluta al .xlsx
     * @return array resumen
     * @throws \Throwable
     */
    public function importarData(int $idpedidoordencompra, string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheets      = $spreadsheet->getAllSheets();

        // 1) Tomar cabeceras de la PRIMERA hoja
        $primera = $sheets[0];
        $fecha   = $this->obtenerFecha($primera, 'C1'); // <-- FECHA EN C1

        if (!$fecha || $fecha == '') {
            throw new \RuntimeException('No se puede procesar pedido: Falta Especificar Fecha');
        }

        $bodCod  = trim((string)($primera->getCell('A5')->getCalculatedValue() ?? '')); // A5
        $bodNom  = trim((string)($primera->getCell('B5')->getCalculatedValue() ?? '')); // B5

        // 2) Número de pestañas = total_items
        $totalItems = count($sheets);

        $db = Yii::$app->db;
        $tx = $db->beginTransaction();

        try {
            // 3) Cargar Pedido existente y actualizar campos
            /** @var Pedido $pedido */
            $pedido = Pedido::findOne($idpedido);
            if (!$pedido) {
                throw new \RuntimeException("Pedido {$idpedido} no encontrado");
            }

            $pedido->fecha                    = $fecha;     // string 'Y-m-d' o null
            $bodega = Bodegas::find()->where(['codigo' => $bodCod])->one();

            $pedido->idBodega = $bodega->id;
            $pedido->nombreArchivo = basename($filePath); ; 

            $pedido->totalItems = $totalItems;

            // 4) Recorrer hojas y crear detalles con save()
            $detallesCreados = 0;
            $totalUnidades   = 0;

            foreach ($sheets as $ws) {
                // detectar última col con talla (fila 2, desde E=5)
                $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ws->getHighestColumn());
                $lastCol = 5;
                for ($c = $highestColIndex; $c >= 5; $c--) {

                    $addr = Coordinate::stringFromColumnIndex($c) . 2;   // Ejemplo: si $c=5 → "E2"
                    $v    = trim((string)($ws->getCell($addr)->getCalculatedValue() ?? ''));
                    if ($v !== '') { $lastCol = $c; break; }
                }
                if ($lastCol < 5) { continue; } // Hoja sin tallas => la contamos como item, pero no tendrá detalle

                // ITEM por hoja: C3; si vacío, nombre de la hoja
                $item = trim((string)($ws->getCell('C3')->getCalculatedValue() ?? ''));
                if ($item === '') { $item = (string)$ws->getTitle(); }

                // catálogo de tallas y colores
                $tallas = [];
                for ($c = 5; $c <= $lastCol; $c++) {
                    $addrTalla = Coordinate::stringFromColumnIndex($c) . 2;
                    $tallaTxt  = trim((string)($ws->getCell($addrTalla)->getCalculatedValue() ?? ''));
                    if ($tallaTxt === '') continue;

                    $addrColor = Coordinate::stringFromColumnIndex($c) . 4;
                    $colorTxt  = trim((string)($ws->getCell($addrColor)->getCalculatedValue() ?? ''));
                    $tallas[]  = ['col' => $c, 'talla' => $tallaTxt, 'color' => ($colorTxt !== '' ? $colorTxt : null)];
                }
                if (empty($tallas)) { continue; }

                // recorrer filas de tiendas: 7..highestRow
                $highestRow = $ws->getHighestRow();
                for ($r = 7; $r <= $highestRow; $r++) {

                    // Col A (código tienda)
                    $numero = trim((string)($ws->getCell('A' . $r)->getCalculatedValue() ?? ''));
                    $cod = str_pad($numero, 3, '0', STR_PAD_LEFT);

                    // Col C (nombre tienda)
                    $nom = trim((string)($ws->getCell('C' . $r)->getCalculatedValue() ?? ''));

                    if ($cod === '' && $nom === '') continue;

                    $totRaw = $ws->getCell('D' . $r)->getCalculatedValue();
                    $tot    = (int) (is_numeric($totRaw) ? $totRaw : 0);

                    // ¿tiene pedido?
                    $tiene = $tot > 0;
                    if (!$tiene) {
                        foreach ($tallas as $t) {
                            $addrQty = Coordinate::stringFromColumnIndex($t['col']) . $r;
                            $qtyRaw  = $ws->getCell($addrQty)->getCalculatedValue();
                            $qty = (int) (is_numeric($qtyRaw) ? $qtyRaw : 0);
                            if ($qty > 0) { $tiene = true; break; }
                        }
                    }
                    if (!$tiene) continue;

                    // crear detalles por cada talla con qty > 0
                    foreach ($tallas as $t) {
                        $addrQty = Coordinate::stringFromColumnIndex($t['col']) . $r;
                        $qtyRaw  = $ws->getCell($addrQty)->getCalculatedValue();

                        $qty = (int) (is_numeric($qtyRaw) ? $qtyRaw : 0);
                        if ($qty <= 0) continue;

                        // Validar color
                        if (empty($t['color'])) {
                            throw new \RuntimeException("Falta el código de Color en hoja {$ws->getTitle()}");
                        }
                        $color = Color::find()->where(['codigo' => $t['color']])->one();
                        if (!$color) {
                            throw new \RuntimeException("Color con código {$t['color']} no encontrado en hoja {$ws->getTitle()}");
                        }

                        // Validar talla
                        if (empty($t['talla'])) {
                            throw new \RuntimeException("Falta el código de Talla en hoja {$ws->getTitle()}");
                        }
                        $talla = Talla::find()->where(['codigo' => $t['talla']])->one();
                        if (!$talla) {
                            throw new \RuntimeException("Talla con código {$t['talla']} no encontrada en hoja {$ws->getTitle()}");
                        }

                        $modelitem = Item::find()->where([
                                                    'item' => $item,
                                                    'idColor' => $color->id,
                                                    'idTalla' => $talla->id
                        ])->one();

                        if (!$modelitem) {
                            throw new \RuntimeException("No se encontró Item: {$item}, Color={$t['color']}, Talla={$t['talla']}");
                        }

                        if (empty($cod)) {
                            throw new \RuntimeException("Falta el código de Bodega en hoja {$ws->getTitle()}");
                        }
                        $bodega = Bodegas::find()->where(['codigo' => $cod])->one();
                        if (!$bodega) {
                            throw new \RuntimeException("Bodega con código {$cod} no encontrado en hoja {$ws->getTitle()}");
                        }

                        $det = new Pedidodetalle();
                        $det->idPedido      = $idpedido;
                        $det->idBodega      = $bodega->id;
                        $det->idItem        = $modelitem->id;

                        //$det->hoja          = (string)$ws->getTitle();

                        $det->unidades      = $qty;

                        if (!$det->save()) {
                            throw new \RuntimeException('No se pudo guardar PedidoDetalle: '.json_encode($det->getErrors(), JSON_UNESCAPED_UNICODE));
                        }

                        $detallesCreados++;
                        $totalUnidades += $qty;
                    }
                }
            }

            // 5) Actualizar totales y guardar cabecera
            $pedido->totalUnidades = $totalUnidades;

            if (!$pedido->save()) {
                throw new \RuntimeException('No se pudo actualizar Pedido: '.json_encode($pedido->getErrors(), JSON_UNESCAPED_UNICODE));
            }

            $tx->commit();

            return [
                'pedido_id'        => (int)$idpedido,
                'fecha'            => $pedido->fecha,
                'bodega_codigo'    => $pedido->bodega->codigo,
                'bodega_nombre'    => $pedido->bodega->nombre,
                'total_items'      => (int)$pedido->totalItems,
                'total_unidades'   => (int)$pedido->totalUnidades,
                'detalles_creados' => $detallesCreados,
            ];

        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    /** Obtiene fecha desde una celda (DateTime, serial Excel o texto) y devuelve 'Y-m-d' o null */
    private function obtenerFecha(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws, string $address): ?string
    {
        $cell = $ws->getCell($address);
        $v = $cell->getValue();

        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d');
        }

        $style = $cell->getStyle();
        $formatCode = $style ? $style->getNumberFormat()->getFormatCode() : '';
        if (is_numeric($v) && $formatCode) {
            try {
                $dt = XlsDate::excelToDateTimeObject((float)$v);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        $s = trim((string)($cell->getCalculatedValue() ?? ''));
        if ($s !== '') {
            $ts = strtotime($s);
            if ($ts !== false) { return date('Y-m-d', $ts); }
        }
        return null;
    }
}
