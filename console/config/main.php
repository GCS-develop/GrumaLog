<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => \yii\console\controllers\FixtureController::class,
            'namespace' => 'common\fixtures',
          ],
    ],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],

        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'common\models\User', // Cambia esto a tu modelo User
            'enableAutoLogin' => false,
            'enableSession' => false, // No hay sesiones en consola
        ],

        'errorHandler' => [
            'class' => 'yii\console\ErrorHandler', // Configuración para la consola
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager', // Si estás usando RBAC a través de base de datos
            // o 'class' => 'yii\rbac\PhpManager', si lo manejas con archivos PHP
        ],
    ],
    'params' => $params,
];
