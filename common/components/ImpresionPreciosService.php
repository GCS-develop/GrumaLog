<?php

namespace common\components;

use Yii;
use common\models\User;

class ImpresionPreciosService
{
    public function imprimirPorExistencia(object $modelo): array
    {
        $cantidad = (int)($modelo->existencia ?? 0);
        if ($cantidad <= 0) {
            return ['status' => 'error', 'message' => 'Cantidad inválida para imprimir.'];
        }
        return $this->imprimirCantidad($cantidad, $modelo->precio ?? 0, $modelo->codigoBarra ?? null);
    }

    public function imprimirCantidad(int $cantidad, $precio, $codigoBarra = null): array
    {
        $userId = Yii::$app->user->id ?? null;
        $user = $userId ? User::findOne(['id' => $userId]) : null;
        if (!$user || !$user->bodegarecibir || !$user->bodegarecibir->impresorapaxar) {
            return ['status' => 'error', 'message' => 'No se encontró impresora asociada al usuario.'];
        }

        $imp = $user->bodegarecibir->impresorapaxar;
        $epl = ($imp->tipo === 'epl');

        $cfg = [
            'tipo'    => $imp->tipo,
            'ip'      => $imp->ip,
            'puerto'  => $imp->puerto ?: 9100,
            'recurso' => $imp->recurso,
        ];

        // Layout común
        $x = 30;
        $y = 80;
        $dx = 270;
        $dy = 100;
        $lineas = 1;
        $bloque = 3;
        $x0 = $x;
        $y0 = $y;

        $payload = '';
        $zpl = "^XA\n";
        $ok = ['status' => 'error', 'message' => 'Sin envío'];

        for ($i = 0; $i < $cantidad; $i++) {
            if ($epl) {
                $payload .= "A{$x},{$y},0,7,1,1,N,\"" . number_format($precio ?? 0, 0, ',', '.') . "\"\n";
                $y += $dy;
                if (($i + 1) % $lineas == 0) {
                    $y = $y0;
                    $x += $dx;
                }
                if (($i + 1) % $bloque == 0 || ($i + 1) == $cantidad) {
                    $payload = "N\n" . $payload . "P1\n";
                    $ok = $this->enviar($payload, array_merge($cfg, ['tipo' => 'recurso']));
                    $payload = '';
                    $x = $x0;
                    $y = $y0;
                }
            } else {
                $zpl .= "^FO{$x},{$y}^A0N,50,50^FD" . number_format($precio ?? 0, 0, ',', '.') . "^FS\n";
                // si quieres, activa el código de barras:
                // if ($codigoBarra) { $zpl .= "^FO{$x},".($y+20)."^BY1,3,55^FT20,100^BCN,,Y,N,,A,^FD{$codigoBarra}^FS\n"; }
                $y += $dy;
                if (($i + 1) % $lineas == 0) {
                    $y = $y0;
                    $x += $dx;
                }
                if (($i + 1) % $bloque == 0 || ($i + 1) == $cantidad) {
                    $zpl .= "^XZ";
                    $ok = $this->enviar($zpl, $cfg);
                    if (($i + 1) < $cantidad) {
                        $zpl = "^XA\n";
                    }
                    $x = $x0;
                    $y = $y0;
                }
            }
        }

        if ($ok['status'] === 'success') {
            Yii::trace("Imprimiendo {$cantidad} etiquetas. Recurso: {$imp->recurso} Tipo: {$imp->tipo}", __METHOD__);
        } else {
            Yii::error("Error al imprimir: {$ok['message']}", __METHOD__);
        }

        return $ok;
    }

    private function enviar(string $raw, array $cfg): array
    {
        $tipo = $cfg['tipo'];
        $ip = $cfg['ip'] ?? null;
        $puerto = $cfg['puerto'] ?? 9100;
        $recurso = $cfg['recurso'] ?? null;
        Yii::$app->session->removeAllFlashes();

        if ($tipo === 'ip') {
            $socket = @fsockopen($ip, $puerto, $errno, $err, 10);
            if (!$socket) {
                $msg = "No se pudo conectar a $ip:$puerto. $err ($errno)";
                Yii::$app->session->setFlash('error', $msg);
                return ['status' => 'error', 'message' => $msg];
            }
            fwrite($socket, $raw);
            fclose($socket);
            Yii::$app->session->setFlash('success', "Impresión enviada a $ip:$puerto.");
            return ['status' => 'success', 'message' => "OK $ip:$puerto"];
        }

        if ($tipo === 'recurso') {
            $tempDir = Yii::getAlias('@frontend') . '/temp';
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0777, true);
            }
            $tmp = $tempDir . '/zpl_' . uniqid() . '.tmp';
            file_put_contents($tmp, $raw);

            $cmd = sprintf('print %s /D:%s "%s"', $ip, '\\\\' . str_replace('\\', '\\\\', ltrim($recurso, '\\')), $tmp);
            exec($cmd, $out, $rc);
            @unlink($tmp);

            if ($rc !== 0) {
                $msg = "Error al imprimir en $recurso: " . implode("\n", $out);
                Yii::$app->session->setFlash('error', $msg);
                return ['status' => 'error', 'message' => $msg];
            }
            return ['status' => 'success', 'message' => "Impresión enviada a $recurso"];
        }

        return ['status' => 'error', 'message' => 'Tipo de impresora no soportado'];
    }
}
