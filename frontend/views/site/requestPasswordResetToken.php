<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

$this->title = '¿Olvidaste tu contraseña? — Cuenta HERPO';
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <!-- Branding -->
                    <div class="d-flex align-items-center mb-3 flex-column">
                        <img src="imagenes/hlogo.png" alt="Login image" class="login-image" height="32" class="me-2" onerror="this.style.display='none'">
                        <small class="text-muted">¡Nos viste bien!</small>
                    </div>

                    <div class="text-center mb-2">
                        <h1 class="h5 mb-0 text-primary">Recuperar acceso</h1>
                    </div>

                    <p class="mb-4 text-muted">
                        Escribe el correo con el que te registraste. Te enviaremos un enlace para restablecer tu contraseña.
                    </p>

                    <?php $form = ActiveForm::begin([
                        'id' => 'request-password-reset-form',
                        'enableAjaxValidation' => true,
                        'enableClientValidation' => true,
                        'fieldConfig' => [
                            'inputOptions' => ['class' => 'form-control form-control-lg'],
                            'labelOptions' => ['class' => 'form-label'],
                            'errorOptions' => ['class' => 'invalid-feedback'],
                        ],
                    ]); ?>

                    <?= $form->field($model, 'email')->textInput([
                        'type' => 'email',
                        'placeholder' => 'tucorreo@ejemplo.com',
                        'maxlength' => true,
                        'autofocus' => true,
                        'autocomplete' => 'email',
                        'aria-describedby' => 'emailHelp',
                    ])->label('Correo electrónico'); ?>

                    <div id="emailHelp" class="form-text">Debe ser un correo válido. Si tienes varias cuentas, usa la corporativa.</div>

                    <div class="row justify-content-between mt-3">
                        <?= Html::submitButton(
                            '<span class="spinner-border spinner-border-sm me-2 d-none"
                             role="status" aria-hidden="true"></span> Enviar instrucciones',
                            ['class' => 'btn btn-primary btn-lg', 'id' => 'btn-submit']
                        ) ?>
                        <a class="btn btn-outline-secondary btn-lg"
                            href="<?= Url::to(['site/login']) ?>">Volver a iniciar sesión</a>
                    </div>

                    <?php ActiveForm::end(); ?>

                    <hr class="my-4">
                    <div class="small text-muted">
                        <strong>¿No recuerdas qué correo usaste?</strong> Contacta a tu supervisor o a la mesa de ayuda interna para validar tu cuenta.<br>
                        Al continuar aceptas nuestras políticas de tratamiento de datos personales.
                    </div>

                    <div id="actionStatus" class="visually-hidden" aria-live="polite"></div>
                </div>
            </div>

            <div class="text-center mt-3">
                <small class="text-muted">Plataforma interna de Herpo — Retail de moda con presencia regional.</small>
            </div>
        </div>
    </div>
</div>

<?php
// UX: deshabilitar botón + spinner durante el submit
$js = <<<JS
(function() {
  const form = document.getElementById('request-password-reset-form');
  const btn = document.getElementById('btn-submit');
  if (!form || !btn) return;
  form.addEventListener('submit', function() {
    const spinner = btn.querySelector('.spinner-border');
    btn.setAttribute('disabled','disabled');
    if (spinner) spinner.classList.remove('d-none');
    const status = document.getElementById('actionStatus');
    if (status) status.textContent = 'Enviando instrucciones…';
  });
})();
JS;
$this->registerJs($js);
?>