<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Pedidodetalle $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="pedidodetalle-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idPedido')->textInput() ?>

    <?= $form->field($model, 'idBodega')->textInput() ?>

    <?= $form->field($model, 'idItem')->textInput() ?>

    <?= $form->field($model, 'unidades')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
