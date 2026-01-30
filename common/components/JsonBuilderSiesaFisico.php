<?php

namespace common\components;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\RuntimeException;

// Ajusta los namespaces si tus modelos están en frontend\models o common\models
use frontend\models\SiesaConectorDocumento;
use frontend\models\SiesaEnvioMovimiento;
use frontend\models\SiesaEnvioMovimientoValor;
use frontend\models\SiesaConectorMovimientoCampo;
use RuntimeException as GlobalRuntimeException;

class JsonBuilderSiesaFisico
{
    /**
     * Construye el JSON del conector INV FISICO (conector_id = 4) con formato:
     * { "Físico": [ {Consecutivo,Bodega,Cantidad,Item,Color,Talla}, ... ] }
     *
     * @param int $documentoId
     * @param int $conectorId (default 4)
     * @param string $rootKey (default 'Físico')
     * @return string JSON
     */
    public static function build(int $documentoId, int $conectorId = 4, string $rootKey = 'Físico'): string
    {
        if ($documentoId <= 0) {
            throw new InvalidArgumentException('documentoId inválido.');
        }

        // 1) Documento
        $documento = SiesaConectorDocumento::find()
            ->where(['id' => $documentoId])
            ->one();

        if (!$documento) {
            throw new GlobalRuntimeException("No existe siesa_conector_documento id={$documentoId}.");
        }

        if ((int)$documento->conector_id !== (int)$conectorId) {
            throw new GlobalRuntimeException("El documento id={$documentoId} no pertenece al conector_id={$conectorId}.");
        }

        // 2) Campos configurados del conector (dinámico por nombre_campo)
        $campos = SiesaConectorMovimientoCampo::find()
            ->select(['id', 'nombre_campo'])
            ->where(['conector_id' => $conectorId])
            ->all();

        if (empty($campos)) {
            throw new GlobalRuntimeException("No hay configuración en siesa_conector_movimiento_campo para conector_id={$conectorId}.");
        }

        $campoIdToNombre = [];
        foreach ($campos as $c) {
            $campoIdToNombre[(int)$c->id] = trim((string)$c->nombre_campo);
        }

        // Campos requeridos para el JSON final
        $required = ['Consecutivo', 'Bodega', 'Cantidad', 'Item', 'Color', 'Talla'];
        $nombresDisponibles = array_values($campoIdToNombre);
        foreach ($required as $req) {
            if (!in_array($req, $nombresDisponibles, true)) {
                throw new GlobalRuntimeException("Falta el campo requerido '{$req}' en siesa_conector_movimiento_campo (conector_id={$conectorId}).");
            }
        }

        // 3) Movimientos del documento (orden determinista)
        $movimientos = SiesaEnvioMovimiento::find()
            ->select(['id', 'documento_id'])
            ->where(['documento_id' => $documentoId])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        if (empty($movimientos)) {
            // Si quieres retornar vacío en vez de error, cambia esto por return json_encode([$rootKey => []], ...);
            throw new GlobalRuntimeException("El documento id={$documentoId} no tiene movimientos (siesa_envio_movimiento).");
        }

        $movIds = array_map(static fn($m) => (int)$m->id, $movimientos);

        // 4) Valores (en bloque, evitando N+1)
        $valores = SiesaEnvioMovimientoValor::find()
            ->select(['movimiento_id', 'campo_id', 'valor'])
            ->where(['movimiento_id' => $movIds])
            ->orderBy(['movimiento_id' => SORT_ASC, 'campo_id' => SORT_ASC])
            ->all();

        // 5) Reconstrucción: 1 objeto por movimiento_id
        $rowsByMov = [];
        foreach ($movIds as $mid) {
            // inicializa con llaves requeridas vacías (contrato JSON estable)
            $rowsByMov[$mid] = [
                'Consecutivo' => '',
                'Bodega'      => '',
                'Cantidad'    => '',
                'Item'        => '',
                'Color'       => '',
                'Talla'       => '',
            ];
        }

        foreach ($valores as $v) {
            $mid = (int)$v->movimiento_id;
            $cid = (int)$v->campo_id;

            if (!isset($rowsByMov[$mid])) {
                // movimiento extraño/no esperado: lo ignoramos para no romper
                continue;
            }

            $nombre = $campoIdToNombre[$cid] ?? null;
            if ($nombre === null) {
                continue;
            }

            // Solo llenamos los que nos interesan (los demás quedan ignorados)
            if (array_key_exists($nombre, $rowsByMov[$mid])) {
                $rowsByMov[$mid][$nombre] = trim((string)$v->valor);
            }
        }

        $final = array_values($rowsByMov);

        return json_encode(
            [$rootKey => $final],
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }
}
