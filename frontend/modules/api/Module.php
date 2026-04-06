<?php

namespace frontend\modules\api;

use Yii;
use yii\filters\Cors;

/**
 * Módulo API para portal de proveedores (GRUMAProv)
 * Expone endpoints REST para agendamiento de entregas
 * Todos los endpoints son públicos — la autenticación se hace por Bearer Token
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'frontend\modules\api\controllers';

    public function init()
    {
        parent::init();
    }

    public function beforeAction($action)
    {
        // Desactivar AccessControl global para todo el módulo API
        // La autenticación se maneja por Bearer Token en ApiAuth
        Yii::$app->user->enableSession = false;

        return parent::beforeAction($action);
    }
}
