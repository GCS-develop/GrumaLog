<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'name' => 'GRUMALOG',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'timeZone' => 'America/Bogota',

    'modules' => [
        'agenda' => [
            'class' => 'frontend\modules\agenda\Module',
        ],
        'siesa' => [
            'class' => 'frontend\modules\siesa\Module',
        ],
        'catalogos' => [
            'class' => 'frontend\modules\catalogos\Module',
        ],
        'nomina' => [
            'class' => 'frontend\modules\nomina\Module',
        ],
        'traspaso' => [
            'class' => 'frontend\modules\traspaso\Module',
        ],
        'programacion' => [
            'class' => 'frontend\modules\programacion\Module',
        ],
        'crossdocking' => [
            'class' => 'frontend\modules\crossdocking\Module',
        ],
        'despacho' => [
            'class' => 'frontend\modules\despacho\Module',
        ],
        'ventas' => [
            'class' => 'frontend\modules\ventas\Module',
        ],
        'transporte' => [
            'class' => 'frontend\modules\transporte\Module',
        ],
        'ordencompra' => [
            'class' => 'frontend\modules\ordencompra\Module',
        ],
        'devolucion' => [
            'class' => 'frontend\modules\devolucion\Module',
        ],
        'compras' => [
            'class' => 'frontend\modules\compras\Module',
        ],

        'productostiquetesprecio' => [
            'class' => 'frontend\modules\productostiquetesprecio\Module',
        ],

        'auditoriamanual' => [
            'class' => 'frontend\modules\auditoriamanual\Module',
        ],
        'contabilidad' => [
            'class' => 'frontend\modules\contabilidad\Module',
        ],

        'distribucion' => [
            'class' => 'frontend\modules\distribucion\Module',
        ],
        'hunter' => [
            'class' => 'frontend\modules\hunter\Module',
        ],
        'grumascanmarcacion' => [
            'class' => 'frontend\modules\grumascanmarcacion\Module',
        ],
        'calificacion' => [
            'class' => 'frontend\modules\calificacion\Module',
        ],
        'auditoriaentrada' => [
            'class' => 'frontend\modules\auditoriaentrada\Module',
        ],
        'api' => [
            'class' => 'frontend\modules\api\Module',
        ],
    ],

    'components' => [
        'siesaPrecio' => [
            'class' => \common\components\SiesaPrecioService::class,
        ],
        'user' => [
            'identityClass' => \common\models\User::class,
            'enableAutoLogin' => true,
            'loginUrl' => ['site/login'],     // 👈 fuerza login de Site
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@mdm/admin/views' => '@frontend/views/admin',
                    '@mdm/admin/mail'  => '@common/mail',
                ],
            ],
        ],

        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'locale' => 'es-CO', // Ajusta a tu localización (español - Colombia)
            'currencyCode' => 'COP', // Código de moneda (Peso Colombiano)
        ],
        // 'request' => [
        //     'csrfParam' => '_csrf-frontend',
        // ],
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json'        => 'yii\web\JsonParser',
            ],
        ],

        // 'user' => [
        //     'identityClass' => 'common\models\User',
        //     'enableAutoLogin' => true,
        //     'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        // ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [

                // 🔴 Log general (errores y warnings)
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],

                // 📦 Log específico de inventario / SIESA
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['info', 'warning', 'error'],
                    'categories' => ['inventario'],
                    'logFile' => '@runtime/logs/inventario-' . date('Y-m-d') . '.log',
                    'logVars' => [], // evita ruido de $_GET $_POST
                ],

            ],
        ],

        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        /*'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],*/

    ],
    'params' => $params,
];
