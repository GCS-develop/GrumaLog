<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Restablecer contraseña — Cuenta HERPO';
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h1 class="h5 text-center mb-3">Crea una nueva contraseña</h1>
                    <div class="d-flex align-items-center mb-3 flex-column">
                        <img src="imagenes/hlogo.png" alt="Login image" class="login-image" height="32" class="me-2" onerror="this.style.display='none'">
                    </div>
                    <p class="text-muted mb-4">Por seguridad, el enlace tiene un tiempo de vigencia limitado.</p>

                    <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

                    <?= $form->field($model, 'password')->passwordInput([
                        'autofocus' => true,
                        'minlength' => 8,
                        'autocomplete' => 'new-password',
                        'placeholder' => 'Mínimo 8 caracteres',
                        'id' => 'newpass',
                    ])->label('Nueva contraseña'); ?>

                    <?php if ($model->hasProperty('password_repeat') || property_exists($model, 'password_repeat')): ?>
                        <?= $form->field($model, 'password_repeat')->passwordInput([
                            'autocomplete' => 'new-password',
                            'placeholder' => 'Repite tu contraseña',
                            'id' => 'newpass2',
                        ])->label('Confirmar contraseña'); ?>
                    <?php endif; ?>

                    <div class="form-text mb-3" id="passHelp">Usa mayúsculas, minúsculas y números. Evita datos obvios.</div>

                    <div class="row">

                        <div class="col-6  text-center">
                            <?= Html::submitButton(
                                '<span class="spinner-border spinner-border-sm me-2 d-none"
                             role="status" aria-hidden="true"></span> Guardar nueva contraseña',
                                ['class' => 'btn btn-primary btn-lg', 'id' => 'btn-save']
                            ) ?>
                        </div>

                        <div class="col-6 text-center align-items-center mb-3">
                            <input type="checkbox" id="togglePass" class="form-check-input">
                            <label for="togglePass" class="form-check-label">Mostrar contraseñas</label>
                        </div>

                    </div>


                    <?php ActiveForm::end(); ?>

                    <div id="actionStatus" class="visually-hidden" aria-live="polite"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
(function() {
  const form = document.getElementById('reset-password-form');
  const btn = document.getElementById('btn-save');
  const pass1 = document.getElementById('newpass');
  const pass2 = document.getElementById('newpass2');
  const toggle = document.getElementById('togglePass');

  if (toggle) {
    toggle.addEventListener('change', function() {
      const type = this.checked ? 'text' : 'password';
      if (pass1) pass1.type = type;
      if (pass2) pass2.type = type;
    });
  }

  if (form && btn) {
    form.addEventListener('submit', function() {
      // Validación rápida de coincidencia (además de la del servidor)
      if (pass1 && pass2 && pass2.value.length && pass1.value !== pass2.value) {
        pass2.setCustomValidity('Las contraseñas no coinciden');
        pass2.reportValidity();
        return false;
      } else if (pass2) {
        pass2.setCustomValidity('');
      }
      const spinner = btn.querySelector('.spinner-border');
      btn.setAttribute('disabled','disabled');
      if (spinner) spinner.classList.remove('d-none');
      const status = document.getElementById('actionStatus');
      if (status) status.textContent = 'Guardando…';
    });
  }
})();
JS;
$this->registerJs($js);
?>