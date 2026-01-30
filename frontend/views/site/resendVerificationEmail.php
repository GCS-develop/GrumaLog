<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Reenviar correo de verificación — Cuenta HERPO';
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h1 class="h5 mb-3">Reenviar verificación</h1>
                    <p class="text-muted">Ingresa tu correo. Te enviaremos nuevamente el enlace de verificación.</p>

                    <?php $form = ActiveForm::begin(['id' => 'resend-verification-email-form']); ?>

                    <?= $form->field($model, 'email')->textInput([
                        'type' => 'email',
                        'autofocus' => true,
                        'autocomplete' => 'email',
                        'placeholder' => 'tucorreo@ejemplo.com',
                    ])->label('Correo electrónico'); ?>

                    <div class="d-grid">
                        <?= Html::submitButton(
                            '<span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span> Reenviar',
                            ['class' => 'btn btn-primary btn-lg', 'id' => 'btn-resend']
                        ) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                    <div id="actionStatusResend" class="visually-hidden" aria-live="polite"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
(function() {
  const form = document.getElementById('resend-verification-email-form');
  const btn = document.getElementById('btn-resend');
  if (!form || !btn) return;
  form.addEventListener('submit', function() {
    const spinner = btn.querySelector('.spinner-border');
    btn.setAttribute('disabled','disabled');
    if (spinner) spinner.classList.remove('d-none');
    const status = document.getElementById('actionStatusResend');
    if (status) status.textContent = 'Reenviando…';
  });
})();
JS;
$this->registerJs($js);
?>