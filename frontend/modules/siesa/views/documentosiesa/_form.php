<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Documentosiesa $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="documentosiesa-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'tipoDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'numeroDocumento')->textInput() ?>

    <?= $form->field($model, 'f350_id_cia')->textInput() ?>

    <?= $form->field($model, 'f350_rowid')->textInput() ?>

    <?= $form->field($model, 'f350_id_co')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'f350_id_tipo_docto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'f350_consec_docto')->textInput() ?>

    <?= $form->field($model, 'idGruma')->textInput() ?>

    <?= $form->field($model, 'origen')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
