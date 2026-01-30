<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\models\Bodegas;
use common\widgets\Alert;

/** @var $model frontend\modules\grumascan\models\ExportFisicoForm */

$this->title = 'Exportar Inventario Físico';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
    </div>
    <div id="download-msg" class="alert alert-success d-none"></div>
    <?= Alert::widget() ?>

    <div class="card-body">
        <?php $form = ActiveForm::begin([
            'method' => 'post',
        ]); ?>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'fecha')->input('date') ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'idbodega')->dropDownList(
                    Bodegas::getListaData(),
                    ['prompt' => 'Seleccione...']
                ) ?>
            </div>

            <div class="col-md-5 d-flex align-items-end">
                <?= Html::submitButton('Descargar Excel', ['class' => 'btn btn-success']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$js = <<<JS
(function () {
  const form = document.querySelector('form');
  if (!form) return;

  form.addEventListener('submit', function () {
    const box = document.getElementById('download-msg');
    if (box) {
      box.classList.remove('d-none');
      box.innerHTML = 'Descarga iniciada. Si no se descarga, verifique permisos del navegador.';
    }
  });
})();
JS;
$this->registerJs($js);
?>