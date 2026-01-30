<?php

namespace common\components;

class MarcacionStickerPrinter
{
    // Ajusta si tienes 300dpi
    private const DPI = 203;

    // Cada sticker: 3.5cm x 6cm (ancho x alto físico)
    private const STICKER_W_MM = 35;
    private const STICKER_H_MM = 60;

    // Rotación aplicada a CADA campo (NO global)
    private const ROT = 'R'; // 'R' o 'B' según sentido

    /**
     * 1) SEPARACIÓN ENTRE "Usuario" y "Unidades" (en dots)
     * Esto es el parámetro "line spacing" del ^FB.
     * Mantiene la primera línea en la misma posición y baja SOLO la segunda.
     *
     * Antes lo tenías en 18. Sube este valor para bajar "Unidades".
     */
    private const FB_LINE_SPACING_DOTS = 45; // prueba 30, 45, 60

    /**
     * 2) AJUSTE DE PRESENTACIÓN (tear-off / cut stop) EN ZPL (dots)
     * Si el corte queda “pisando”, aumenta (020, 040, 060, 080...).
     *
     * Nota: En TSC, según modo (ZPL emulación vs TSPL), este comando puede
     * no ser el único factor. Ver nota al final.
     */
    private const TEAR_OFF_ADJUST_DOTS = 60;

    private static function mmToDots(float $mm): int
    {
        return (int) round($mm * (self::DPI / 25.4));
    }

    /**
     * 1 sticker en una celda.
     * Se rota barcode y texto con self::ROT (por campo), sin ^FW global.
     */
    private static function buildZplOneInCell(
        int $id,
        string $usuario,
        int $x0,
        int $y0,
        int $cellW,
        int $cellH
    ): string {
        // Márgenes internos de la celda
        $padX = 12;
        $padY = 0;

        // --- BLOQUE BARCODE (a la derecha) ---
        $barcodeH = 90;
        $byModule = 3;
        $byWide   = 2;

        // Caja reservada al barcode
        $barcodeBoxW = 155;

        // Ubicación barcode (lo dejas donde ya te gusta)
        $xBarcode = $x0 + $cellW - $padX - $barcodeBoxW;
        $yBarcode = $y0 + $padY - 10;

        // --- BLOQUE TEXTO (a la izquierda) ---
        $fontH = 24;
        $fontW = 24;

        $xText = $x0 + $padX;

        // (lo conservas tal cual venía)
        $textBlock = self::mmToDots(45);

        // Ubicación texto (la dejas igual a como venía)
        $yText = $y0 + $padY + 18;

        // Texto en blanco para que lo llenen a mano (igual que antes)
        $txt = "Usuario: ____________\\&Unidades: ________";

        return
            // BARCODE rotado (por campo) + interpretation line (Y)
            "^FO{$xBarcode},{$yBarcode}"
            . "^BY{$byModule},{$byWide},{$barcodeH}"
            . "^BC" . self::ROT . ",{$barcodeH},Y,N,N"
            . "^FD{$id}^FS\n"

            // TEXTO rotado (por campo), 2 líneas en un solo campo (igual que antes),
            // pero con MAYOR line spacing para que "Unidades" quede más abajo.
            . "^FO{$xText},{$yText}"
            . "^A0" . self::ROT . ",{$fontH},{$fontW}"
            . "^FB{$textBlock},2," . self::FB_LINE_SPACING_DOTS . ",L,0"
            . "^FD{$txt}^FS\n";
    }

    /**
     * 3 stickers (1 fila x 3 columnas) en una sola página.
     * NO rotamos globalmente; rotamos por campo (más compatible).
     */
    public static function buildPayloadZpl3Up(array $ids, string $usuario): string
    {
        $ids = array_values($ids);
        $ids = array_slice($ids, 0, 3);

        $cellW = self::mmToDots(self::STICKER_W_MM);
        $cellH = self::mmToDots(self::STICKER_H_MM);

        $marginLeft = self::mmToDots(2);
        $marginTop  = self::mmToDots(2);

        $pw = ($marginLeft * 2) + ($cellW * 3);
        $ll = ($marginTop * 2) + ($cellH * 1);

        $zpl  = "^XA\n";

        // Ajuste de reposo/posición final para “presentar” mejor el corte (ZPL ~TA###).
        // Si aún queda pisando, aumenta TEAR_OFF_ADJUST_DOTS: 080, 100, 120...
        $ta = str_pad((string) self::TEAR_OFF_ADJUST_DOTS, 3, '0', STR_PAD_LEFT);
        $zpl .= "~TA{$ta}\n";

        $zpl .= "^PW{$pw}\n";
        $zpl .= "^LL{$ll}\n";
        $zpl .= "^LH0,0\n";
        $zpl .= "^FWN\n"; // sin rotación global

        foreach ($ids as $i => $id) {
            $x0 = $marginLeft + ($i * $cellW);
            $y0 = $marginTop;
            $zpl .= self::buildZplOneInCell((int)$id, $usuario, $x0, $y0, $cellW, $cellH);
        }

        $zpl .= "^XZ\n";
        return $zpl;
    }

    /**
     * EPL simple (sin 3-up).
     */
    public static function buildPayloadEplSimple(array $ids, string $usuario): string
    {
        $out = '';
        foreach ($ids as $id) {
            $out .= "N\n";
            $out .= "B20,20,0,1,2,4,80,B,\"" . (int)$id . "\"\n";
            $out .= "A20,110,0,3,1,1,N,\"Usuario: ____________\"\n";
            $out .= "A20,135,0,3,1,1,N,\"Unidades: ________\"\n";
            $out .= "P1\n";
        }
        return $out;
    }

    public static function buildPayload(array $ids, string $usuario, string $tipoImpresora): string
    {
        if ($tipoImpresora === 'epl') {
            return self::buildPayloadEplSimple($ids, $usuario);
        }

        // Para TSC en emulación ZPL/ZGL, esto aplica.
        return self::buildPayloadZpl3Up($ids, $usuario);
    }
}
