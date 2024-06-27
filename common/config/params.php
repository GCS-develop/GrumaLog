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
];
