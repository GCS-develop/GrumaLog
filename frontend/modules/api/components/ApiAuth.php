<?php

namespace frontend\modules\api\components;

use Yii;
use yii\base\ActionFilter;
use yii\web\UnauthorizedHttpException;
use frontend\modules\api\models\ApiCredencial;

/**
 * Filtro de autenticación por Bearer Token para el módulo API.
 * Lee el header "Authorization: Bearer {token}" y carga el NIT en params.
 */
class ApiAuth extends ActionFilter
{
    /** Acciones que NO requieren autenticación */
    public $except = ['token'];

    public function beforeAction($action): bool
    {
        // Siempre responder JSON
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (in_array($action->id, $this->except, true)) {
            return parent::beforeAction($action);
        }

        $authHeader = Yii::$app->request->headers->get('Authorization', '');
        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m)) {
            throw new UnauthorizedHttpException('Token no proporcionado.');
        }

        $credencial = ApiCredencial::findByToken($m[1]);
        if (!$credencial) {
            throw new UnauthorizedHttpException('Token inválido o expirado.');
        }

        Yii::$app->params['api_nit']         = $credencial->nit;
        Yii::$app->params['api_razonSocial'] = $credencial->razonSocial;

        return parent::beforeAction($action);
    }
}
