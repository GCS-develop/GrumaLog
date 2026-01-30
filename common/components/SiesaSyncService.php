<?php

namespace common\components;

use Yii;

class SiesaSyncService
{
    public string $php;
    public string $rutaProyecto;

    public function __construct(string $php, string $rutaProyecto)
    {
        $this->php = $php;
        $this->rutaProyecto = $rutaProyecto;
    }

    public function syncInventarioBodega(int $codigoBodega, bool $syncItems = false): array
    {
        $out = [];
        $ok = true;

        $user = Yii::$app->user->identity ?? null;
        $username = $user?->username ?? 'guest';
        $userId = $user?->id ?? null;

        Yii::info(
            "INICIO sync inventario",
            'inventario'
        );

        Yii::info([
            'bodega' => $codigoBodega,
            'syncItems' => $syncItems,
            'userId' => $userId,
            'username' => $username,
            'hora' => date('Y-m-d H:i:s'),
        ], 'inventario');

        if ($syncItems) {
            $cmdItems = sprintf('"%s" yii siesa/sync-items', $this->php);
            $resItems = $this->run($cmdItems);

            $out[] = "== Sync Items (SIESA) ==";
            $out[] = $resItems;

            Yii::info("SALIDA Sync Items:\n" . $resItems, 'inventario');

            if ($this->hasError($resItems)) {
                Yii::error("ERROR Sync Items", 'inventario');
                $ok = false;
            }
        }

        $cmdInv = sprintf(
            '"%s" yii siesa/actualizar-inventario-bodega %d',
            $this->php,
            $codigoBodega
        );

        $resInv = $this->run($cmdInv);

        $out[] = "== Sync Inventario Bodega {$codigoBodega} ==";
        $out[] = $resInv;

        Yii::info(
            "SALIDA Inventario Bodega {$codigoBodega}:\n" . $resInv,
            'inventario'
        );

        if ($this->hasError($resInv)) {
            Yii::error(
                "ERROR Sync Inventario Bodega {$codigoBodega}",
                'inventario'
            );
            $ok = false;
        }

        Yii::info(
            "FIN sync inventario => " . ($ok ? 'OK' : 'ERROR'),
            'inventario'
        );

        return [
            'ok' => $ok,
            'codigoBodega' => $codigoBodega,
            'log' => implode("\n\n", $out),
        ];
    }

    private function run(string $cmd): string
    {
        $full = 'cd /d ' . escapeshellarg($this->rutaProyecto) . ' && ' . $cmd . ' 2>&1';
        $res = shell_exec($full);
        return $res !== null && $res !== '' ? trim($res) : '(sin salida)';
    }

    private function hasError(string $text): bool
    {
        $t = mb_strtolower($text);
        return str_contains($t, 'error')
            || str_contains($t, 'exception')
            || str_contains($t, 'fatal');
    }
}
