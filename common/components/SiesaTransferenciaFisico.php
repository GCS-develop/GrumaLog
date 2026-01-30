<?php

namespace common\components;

use Yii;
use frontend\models\SiesaConectorDocumento;

class SiesaTransferenciaFisico
{
    /**
     * Orquestador!
     * Genera JSON del documento (staging) y lo envía a Siesa.
     * Guarda la respuesta en siesa_conector_documento.respuesta.
     *
     * @return array ['ok'=>bool, 'httpCode'=>int|null, 'respuesta'=>string, 'json'=>string]
     */
    public static function enviarDocumento(int $documentoId, int $conectorId = 4, string $rootKey = 'Físico'): array
    {
        $documento = SiesaConectorDocumento::findOne($documentoId);
        if (!$documento) {
            throw new \RuntimeException("No existe SiesaConectorDocumento id={$documentoId}.");
        }
        if ((int)$documento->conector_id !== (int)$conectorId) {
            throw new \RuntimeException("El documento {$documentoId} no pertenece al conector {$conectorId}.");
        }

        // 1) Generar JSON desde staging
        $json = \common\components\JsonBuilderSiesaFisico::build($documentoId, $conectorId, $rootKey);

        // 2) Enviar a Siesa con la configuración del conector del documento
        $resp = \common\components\SiesaConectorServicio::enviarJsonDocumento($documentoId, $json);

        // 3) Guardar respuesta en documento (trazabilidad)
        $documento->respuesta = (string)$resp['respuesta'];

        if (!$documento->save()) {
            Yii::error("No se pudo guardar respuesta en documento {$documentoId}: " . json_encode($documento->getErrors()), __METHOD__);
        }

        return [
            'ok' => (bool)$resp['ok'],
            'httpCode' => $resp['httpCode'] ?? null,
            'respuesta' => (string)$resp['respuesta'],
            'json' => $json,
        ];
    }
}
