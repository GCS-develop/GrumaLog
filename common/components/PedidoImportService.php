<?php

namespace common\components;

use Yii;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

use frontend\models\Pedidoordendecompra;
use frontend\models\PedidoDetalle; // tu AR del detalle
use frontend\models\Bodegas;
use frontend\models\Color;
use frontend\models\Talla;
use frontend\models\Item;
use frontend\models\Pedidoordendecompraitem;

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
    public function importarData($idpedido, $idordencompra, $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheets      = $spreadsheet->getAllSheets();

        // 1) Tomar cabeceras de la PRIMERA hoja
        $primera = $sheets[0];

        $fecha   = $this->obtenerFecha($primera, 'C1'); // <-- FECHA EN C1

        if (!$fecha || $fecha == '') {
            throw new \RuntimeException('No se puede procesar pedido: Falta Especificar Fecha');
        }


        $modelpedidooc = Pedidoordendecompra::find()
            ->where([
                'idPedido' => $idpedido,
                'idOrdenCompra' => $idordencompra
            ])
            ->one();

        $ocId = $modelpedidooc->id;

        $bodCod  = trim((string)($primera->getCell('A5')->getCalculatedValue() ?? '')); // A5
        $bodNom  = trim((string)($primera->getCell('B5')->getCalculatedValue() ?? '')); // B5

        // 2) Número de pestañas = total_items
        $totalItems = count($sheets);

        $db = Yii::$app->db;
        $tx = $db->beginTransaction();

        try {
            // 4) Recorrer hojas y crear detalles con save()
            $detallesCreados = 0;
            $totalUnidades   = 0;

            foreach ($sheets as $ws) {

                //$bodCod  = trim((string)($ws->getCell('A5')->getCalculatedValue() ?? '')); // A5
                $bodCod  = trim((string)($ws->getCell('A5')->getValue() ?? '')); // A5
                $bodega = Bodegas::find()->where(['codigo' => $bodCod])->one();
                if (!$bodega) {
                    throw new \RuntimeException("Bodega con código {$bodCod} no encontrada (Celdas A5/B5).");
                }
                $idBodegaOrigen = $bodega->id;

                // detectar última col con talla (fila 2, desde E=5)
                $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ws->getHighestColumn());
                $lastCol = 5;
                for ($c = $highestColIndex; $c >= 5; $c--) {

                    $addr = Coordinate::stringFromColumnIndex($c) . 2;   // Ejemplo: si $c=5 → "E2"
                    $v    = trim((string)($ws->getCell($addr)->getCalculatedValue() ?? ''));
                    if ($v !== '') {
                        $lastCol = $c;
                        break;
                    }
                }
                if ($lastCol < 5) {
                    continue;
                } // Hoja sin tallas => la contamos como item, pero no tendrá detalle

                // ITEM por hoja: C3; si vacío, nombre de la hoja
                $item = trim((string)($ws->getCell('C3')->getCalculatedValue() ?? ''));
                if ($item === '') {
                    $item = (string)$ws->getTitle();
                }

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
                if (empty($tallas)) {
                    continue;
                }

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
                            if ($qty > 0) {
                                $tiene = true;
                                break;
                            }
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
                        $det->idOrdenCompra = $idordencompra;
                        $det->idItem        = $modelitem->id;
                        $det->idBodega      = $bodega->id;

                        //$det->hoja          = (string)$ws->getTitle();

                        $det->unidades      = $qty;
                        $det->unidadesRecibidas = 0;


                        if (!$det->save()) {
                            throw new \RuntimeException('No se pudo guardar PedidoDetalle: ' . json_encode($det->getErrors(), JSON_UNESCAPED_UNICODE));
                        } else {
                            $pedidoocitem = Pedidoordendecompraitem::find()
                                ->where([
                                    'idPedido'      => $idpedido,
                                    'idOrdenCompra' => $idordencompra,
                                    'idItem'        => $modelitem->id
                                ])
                                ->one();

                            if (!$pedidoocitem) {
                                $pedidoocitem = new Pedidoordendecompraitem();
                                $pedidoocitem->idPedido         = $idpedido;
                                $pedidoocitem->idOrdenCompra    = $idordencompra;
                                $pedidoocitem->idItem           = $modelitem->id;
                                $pedidoocitem->idBodega         = $idBodegaOrigen;
                                $pedidoocitem->totalUnidades    = 0;

                                if (!$pedidoocitem->save()) {
                                    throw new \RuntimeException('No se pudo guardar Pedido Orden Compra - Item: ' . json_encode($pedidoocitem->getErrors(), JSON_UNESCAPED_UNICODE));
                                }
                            }
                        }

                        $detallesCreados++;
                        $totalUnidades += $qty;
                    }
                }
            }

            PedidoTotalesService::recalcFromOcId($idpedido, $idordencompra);

            $tx->commit();

            return [
                'pedido_id'        => (int)$idpedido,
                /*
                'fecha'            => $pedido->fecha,
                'bodega_codigo'    => $pedido->bodega->codigo,
                'bodega_nombre'    => $pedido->bodega->nombre,
                'total_items'      => (int)$pedido->totalItems,
                */
                'total_unidades'   => (int)$totalUnidades,
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
            } catch (\Throwable $e) {
            }
        }

        $s = trim((string)($cell->getCalculatedValue() ?? ''));
        if ($s !== '') {
            $ts = strtotime($s);
            if ($ts !== false) {
                return date('Y-m-d', $ts);
            }
        }
        return null;
    }
}
