<?php

namespace common\components;

use frontend\models\Item;

class StickerGenerator
{

	public static function generar($detalles, $fecha_activacion, $origen, $labelsPerRow = 3, $labelWidth = 220, $labelHeight = 170, $cantidad = null)
	{
		$count = 0;
		$horizontalGap = 50;
		$content = '';

		// Lista completa de stickers a imprimir (según cantidad)
		$stickers = [];

		foreach ($detalles as $detalle) {
			$item = Item::findOne($detalle->idItem);
			if (!$item) continue;

			$precio = Item::obtenerPrecioVenta($item->codigoBarras, $fecha_activacion);

			$CantidadDetalle = $cantidad > 0 ? (int) $cantidad : (int) $detalle->cantidadPedida; 

			// if ($cantidad > 0) {
			// 	$cantidad = (int) $cantidad;
			// } else {
			// 	$cantidad = (int) $detalle->cantidadPedida;
			// }

			$ImprimirCantidad [] = $CantidadDetalle;

			for ($i = 0; $i < $CantidadDetalle; $i++) {
				$stickers[] = ['item' => $item, 'precio' => $precio];
			}
		}

		// Generar etiquetas por filas de 3 (cada fila es un bloque ^XA...^XZ)
		$total = count($stickers);

		for ($i = 0; $i < $total; $i++) {
			// Abrir bloque ^XA si estamos comenzando una fila
			if ($i % $labelsPerRow === 0) {
				$content .= "^XA\n";
			}

			$column = $i % $labelsPerRow;
			$row = floor($i / $labelsPerRow);

			$x = $column * ($labelWidth + $horizontalGap);
			$y = 0;

			$item = $stickers[$i]['item'];
			$precio = $stickers[$i]['precio'];

			$content .= Item::generarContenidoSticker($item, $precio, $x, $y, $labelWidth);

			// Cerrar bloque ^XZ si terminamos una fila o llegamos al final
			$isEndOfRow = ($column === $labelsPerRow - 1);
			$isLast = ($i === $total - 1);

			if ($isEndOfRow || $isLast) {
				$content .= "^XZ\n";
			}
		}

		return $content;
	}

	public static function generar1($detalles, $fecha_activacion, $origen, $labelsPerRow = 3, $labelWidth = 220, $labelHeight = 170)
	{
		$count = 0;
		$horizontalGap = 50;
		$content = "^XA\n"; // apertura única de bloque

		foreach ($detalles as $detalle) {
			$item = Item::findOne($detalle->idItem);
			if (!$item) continue;

			$precio = Item::obtenerPrecioVenta($item->codigoBarras, $fecha_activacion);
			$cantidad = (int) $detalle->cantidadPedida;

			for ($i = 0; $i < $cantidad; $i++) {
				$row = floor($count / $labelsPerRow);
				$column = $count % $labelsPerRow;

				$x = $column * ($labelWidth + $horizontalGap);
				$y = $row * $labelHeight;

				$content .= Item::generarContenidoSticker($item, $precio, $x, $y, $labelWidth);
				$count++;
			}
		}

		$content .= "^XZ\n"; // cierre único de bloque
		return $content;
	}
}
