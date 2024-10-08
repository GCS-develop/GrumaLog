<?php
return [
	'bsVersion' => '4.x',
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,

    'endpoints' => [
        'service' => [
           
            //Produccion
           
            'url' => 'https://serviciosconnekta.siesacloud.com/api/v3/ejecutarconsulta',
            'urlConector' => 'https://serviciosconnekta.siesacloud.com/api/v3/conectoresimportar',
           
            //Pruebas

            //'url' => 'https://connektaqa.siesacloud.com/api/v3/ejecutarconsulta',
            //'urlConector' => 'https://connektaqa.siesacloud.com/api/v3/conectoresimportar',
            
            'conniKey' => 'Connikey-grupomayorista-QJBYOFU3',
            'conniToken' => 'QJBYOFU3RTFVNKMWRDFRNUEWSDJSNVQ2SJNJMLU3RZJAOESZVJDLMW',
            'idCompania' => '8203',
        ],
    ],

    //trapasos
    'proyectoNombre' => 'GRUMALOG traspaso',
    'tipodocumento_traspaso' => '2TB',
    'tipodocumento_crossdocking' => '2TA',
    'tituloTraspaso' => 'TRASPASO DE MERCANCIA',
    'grupo' => 'Grupo mayorista S.A',
    'nit' => '900.091.175',
    'direccion' => 'Cr 32 14-25',
    'tel' => '3229200',
];
