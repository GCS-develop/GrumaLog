<?php

/** @var yii\web\View $this */
/** @var common\models\User $user */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
Hola <?= $user->username ?>,
Restablece tu contraseña entrando a:
<?= $resetLink ?>

El enlace vence en <?= (int)($expire / 60) ?> minutos.