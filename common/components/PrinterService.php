<?php

namespace common\components;

class PrinterService
{
    public static function send($impresora, $labelContent)
    {
        try {
            if ($impresora->tipo === 'ip') {
                $socket = fsockopen($impresora->ip, 9100, $errno, $errstr, 5);
                if (!$socket) {
                    return ['codigo' => -1, 'mensaje' => "Error de conexión: $errstr ($errno)"];
                }

                fwrite($socket, $labelContent);
                fclose($socket);

                return ['codigo' => 0, 'mensaje' => 'Enviado correctamente por IP'];
            } elseif ($impresora->tipo === 'recurso') {
                $ruta = '\\\\' . $impresora->recurso; // Doble backslash
                $archivo = 'C:\temp\etiqueta.zpl';
                file_put_contents($archivo, $labelContent);
                exec("copy /Y \"$archivo\" \"$ruta\"", $output, $return_var);
                
                if ($return_var !== 0) {
                    return ['codigo' => -1, 'mensaje' => 'Error enviando al recurso compartido'];
                }

                return ['codigo' => 0, 'mensaje' => 'Enviado correctamente a recurso compartido'];
            } else {
                return ['codigo' => -2, 'mensaje' => 'Tipo de impresora no soportado'];
            }
        } catch (\Exception $e) {
            return ['codigo' => -3, 'mensaje' => $e->getMessage()];
        }
    }
}
