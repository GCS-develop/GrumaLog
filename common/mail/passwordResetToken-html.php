<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\User $user */
/** @var int $expire */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
<div class="password-reset">
    <p>Hola <?= Html::encode($user->username) ?>,</p>

    <p>Para restablecer tu contraseña en GrumaLog, haz clic aquí</p>

    <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
    <p>Este enlace vence en <?= (int)($expire / 60) ?> minutos.</p>

</div>