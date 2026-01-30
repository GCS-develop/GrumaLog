<?php
return [
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm' => '@vendor/npm-asset',
	],
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',

	'modules' => [
		'admin' => [
			'class' => 'mdm\admin\Module',
		],

		'gridview' => [
			'class' => '\kartik\grid\Module'
			// enter optional module parameters below - only if you need to  
			// use your own export download action or custom translation 
			// message source
			// 'downloadAction' => 'gridview/export/download',
			// 'i18n' => []
		]
	],

	'components' => [
		'mailer' => [
			'class' => \yii\symfonymailer\Mailer::class,
			'viewPath' => '@common/mail',
			'useFileTransport' => false,
			'transport' => [
				'dsn' => 'smtp://victorburbanoherpo@gmail.com:xytoljmrivzfltjy@smtp.gmail.com:587',
			],
		],
		'mailService' => [
			'class' => 'common\components\MailService',
		],


		'cache' => [
			'class' => \yii\caching\FileCache::class,
		],

		'authManager' => [
			'class' => 'yii\rbac\DbManager', // or use 'yii\rbac\PhpManager'
		],

		// 'user' => [
		// 	//'class' => 'mdm\admin\models\User',
		// 	'identityClass' => 'mdm\admin\models\User',
		// 	'loginUrl' => ['admin/user/login'],
		// ],

		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],   // 👈 qué tipos de mensajes se guardan
					'logFile' => '@runtime/logs/app.log',
				],
			],
		],
		'mailService' => [
			'class' => 'common\components\MailService',
		],
	],

	'as access' => [
		'class' => 'mdm\admin\components\AccessControl',
		'allowActions' => [
			'site/*',
			'hunter/hunter-api/*',
			//'admin/*',
			'gii/*',
			//'catalogos/*',
		]
	],

	'charset' => 'UTF-8',
	'timeZone' => 'America/Bogota',
];
