<?php

/** @var \common\models\User $user */
$nombre = $user->username ?: $user->email;
?>
Hola <?= $nombre ?>,

Tu contraseña en GRUMALOG fue restablecida exitosamente.
Si no solicitaste este cambio, restablece la contraseña de inmediato y contacta al administrador.