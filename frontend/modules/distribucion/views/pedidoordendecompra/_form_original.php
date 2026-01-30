<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Pedidoordendecompra $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="pedidoordendecompra-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idPedido')->textInput() ?>

    <?= $form->field($model, 'idOrdenCompra')->textInput() ?>

    <?= $form->field($model, 'nombreArchivo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nroItems')->textInput() ?>

    <?= $form->field($model, 'totalUnidades')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
