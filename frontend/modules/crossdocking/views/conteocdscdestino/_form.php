<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Conteocdscdestino $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conteocdscdestino-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idConteocdscdestinofactura')->textInput() ?>

    <?= $form->field($model, 'idCentroOperacion')->textInput() ?>

    <?= $form->field($model, 'numeroCajas')->textInput() ?>

    <?= $form->field($model, 'idUserConteo')->textInput() ?>

    <?= $form->field($model, 'idItemUltimoConteo')->textInput() ?>

    <?= $form->field($model, 'total')->textInput() ?>

    <?= $form->field($model, 'idEstado')->textInput() ?>

    <?= $form->field($model, 'idLegalizado')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
