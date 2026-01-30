<?php

use yii\helpers\Html;

/** @var $user app\models\User */
?>

<div style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 30px;">
    <div style="max-width: 600px; background: #ffffff; padding: 30px; border-radius: 8px; margin: auto;">
        <h2 style="color: #333;">Confirmación de cambio de contraseña - GrumaLog</h2>

        <p>Hola <strong><?= Html::encode($user->username) ?></strong>,</p>

        <p>Te informamos que tu contraseña fue restablecida exitosamente.</p>

        <p>Usuario: <strong><?= Html::encode($user->email) ?></strong></p>

        <p>Si no solicitaste este cambio, por favor contacta de inmediato con el administrador del sistema.</p>

        <p style="margin-top: 40px; font-size: 12px; color: #888;">
            Este mensaje fue generado automáticamente por el sistema GrumaLog.
        </p>
    </div>
</div>