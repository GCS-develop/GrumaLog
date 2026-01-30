<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\MovimientoGasto $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="movimiento-gasto-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'F_CIA')->textInput() ?>

    <?= $form->field($model, 'F350_ID_CO')->textInput(['readonly' => true]) ?>

    <?= $form->field($model, 'F350_ID_TIPO_DOCTO')->textInput(['maxlength' => true, 'readonly' => true]) ?>

    <?= $form->field($model, 'F350_CONSEC_DOCTO')->textInput(['maxlength' => true, 'readonly' => true]) ?>

    <?= $form->field($model, 'F351_ID_AUXILIAR')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'F351_ID_TERCERO')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'F351_ID_CO_MOV')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'F351_ID_UN')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'F351_ID_CCOSTO')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'F351_ID_FE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'F351_VALOR_DB')->textInput() ?>

    <?= $form->field($model, 'F351_VALOR_CR')->textInput() ?>

    <?= $form->field($model, 'F351_BASE_GRAVABLE')->textInput() ?>

    <?= $form->field($model, 'F351_DOCTO_BANCO')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'F351_NRO_DOCTO_BANCO')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'F351_NOTAS')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
