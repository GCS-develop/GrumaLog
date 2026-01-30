<?php

namespace common\components;

use Yii;
use yii\httpclient\Client;
use frontend\models\SiesaConectorDocumento;

class SiesaConectorServicio
{
    /**
     * Envía JSON a Siesa usando la configuración del conector asociado al documento.
     *
     * @return array ['ok'=>bool, 'httpCode'=>int|null, 'respuesta'=>string]
     */
    public static function enviarJsonDocumento(int $documentoId, string $json): array
    {
        $documento = SiesaConectorDocumento::findOne($documentoId);
        if (!$documento || !$documento->conector) {
            throw new \RuntimeException("No se encontró el documento o el conector para documentoId={$documentoId}.");
        }

        $conector = $documento->conector;

        // Armar URL con parámetros (como tu servicio actual)
        $queryParams = http_build_query([
            'idCompania' => $conector->id_compania,
            'idSistema' => $conector->id_sistema,
            'idDocumento' => $conector->id_documento,
            'nombreDocumento' => $conector->nombre_documento,
        ]);

        $urlBase = trim((string)$conector->url_base);
        if ($urlBase === '') {
            throw new \RuntimeException("El conector (id={$conector->id}) no tiene url_base configurada.");
        }

        $url = $urlBase . '?' . $queryParams;

        // Headers (como tu servicio actual)
        $headers = [
            'Content-Type' => 'application/json',
            'ConniKey' => (string)$conector->header_key,
            'ConniToken' => (string)$conector->header_token,
        ];

        Yii::info("Enviando JSON a SIESA (documentoId={$documentoId}) URL={$url}", __METHOD__);
        Yii::info("Payload JSON (documentoId={$documentoId}):\n{$json}", __METHOD__);


        // var_dump($url);
        // var_dump($headers);
        // var_dump($json);
        // die('finaliza');
        $client = new Client();
        $response = $client->createRequest()
            ->setUrl($url)
            ->setMethod('POST')
            ->setHeaders($headers)
            ->setContent($json)
            ->send();

        $httpCode = (int)$response->statusCode;
        $body = (string)$response->content;

        if (!$response->isOk) {
            Yii::error("Respuesta NO OK de SIESA (documentoId={$documentoId}) HTTP {$httpCode}: {$body}", __METHOD__);
            return [
                'ok' => false,
                'httpCode' => $httpCode,
                'respuesta' => $body,
            ];
        }

        Yii::info("Respuesta OK de SIESA (documentoId={$documentoId}) HTTP {$httpCode}: {$body}", __METHOD__);

        return [
            'ok' => true,
            'httpCode' => $httpCode,
            'respuesta' => $body,
        ];
    }
}
