<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Ordendecompradetalle $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ordendecompradetalle-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idOrdenCompra')->textInput() ?>

    <?= $form->field($model, 'idItem')->textInput() ?>

    <?= $form->field($model, 'cantidadPedida')->textInput() ?>

    <?= $form->field($model, 'cantidadEntrada')->textInput() ?>

    <?= $form->field($model, 'cantidadPendiente')->textInput() ?>

    <?= $form->field($model, 'fechaEntrega')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
