<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Registro de Usuario';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="login-box">
    <div class="login-logo">
        <b><?= Html::encode($this->title) ?></b>
    </div>
    <div class="login-box-body">
        <p class="login-box-msg">Completa el formulario para crear una cuenta</p>

        <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

        <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'Usuario'])->label(false) ?>

        <?= $form->field($model, 'email')->textInput(['placeholder' => 'Correo'])->label(false) ?>

        <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Contraseña'])->label(false) ?>

        <div class="row">
            <div class="col-12 centrar">
                <?= Html::submitButton('Registrarse', ['class' => 'btn btn-success btn-block']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

        <hr>
        <p class="text-center">
            ¿Ya tienes cuenta? <?= Html::a('Inicia sesión aquí', ['site/login']) ?>
        </p>
    </div>
</div>
