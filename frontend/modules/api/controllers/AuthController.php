<?php

namespace frontend\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use frontend\modules\api\models\ApiCredencial;
use frontend\models\Proveedor;
use frontend\models\Parametroscontrol;

/**
 * Autenticación API — obtención de Bearer token por NIT + API Key
 */
class AuthController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
            ],
        ]);
    }

    /**
     * POST /api/auth/token
     * Body: { "nit": "...", "apiKey": "..." }
     * Retorna: { "success": true, "token": "...", "nit": "...", "razonSocial": "..." }
     */
    public function actionToken()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return $this->error('Método no permitido.', 405);
        }

        $body   = Yii::$app->request->bodyParams;
        $nit    = trim($body['nit']   ?? '');
        $apiKey = trim($body['apiKey'] ?? '');

        if (!$nit || !$apiKey) {
            return $this->error('nit y apiKey son requeridos.');
        }

        // Validar API key compartida
        $apk = Parametroscontrol::getValorparametro('APK');
        if ($apiKey !== $apk) {
            return $this->error('API Key inválida.', 401);
        }

        // Validar que el proveedor exista en el sistema
        $proveedor = Proveedor::findOne(['nit' => $nit]);
        if (!$proveedor) {
            return $this->error('NIT no encontrado en el sistema.', 404);
        }

        // Generar token
        $credencial = ApiCredencial::generarToken($nit, $proveedor->razonSocial);
        if (!$credencial) {
            return $this->error('Error generando token. Intente nuevamente.');
        }

        return [
            'success'    => true,
            'token'      => $credencial->token,
            'nit'        => $credencial->nit,
            'razonSocial' => $credencial->razonSocial,
        ];
    }

    private function error(string $msg, int $status = 400): array
    {
        Yii::$app->response->statusCode = $status;
        return ['success' => false, 'message' => $msg];
    }
}
