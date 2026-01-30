<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Tipodocumento $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="tipodocumento-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-4">
            <?= $form->field($model, 'codigo')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-4">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-4">
            <?= $form->field($model, 'requierePedido')->dropDownList(
                [1 => 'Sí', 0 => 'No'],
                [
                    'prompt' => 'Seleccione…',
                    'id' => 'requiere-pedido'
                ]
            ) ?>
        </div>
        <div class="col-4">
            <?= $form->field($model, 'permite_cantidad_manual')->dropDownList(
                [1 => 'Sí', 0 => 'No'],
                [
                    'prompt' => 'Seleccione…',
                    'id' => 'permite_cantidad_manual'
                ]
            ) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <div class="col-12">
            <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>