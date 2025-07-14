<?php

use frontend\models\SiesaConector;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\SiesaConectorDocumento $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="siesa-conector-documento-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'conector_id')->dropDownList(
        SiesaConector::getListaData(),
        [
            'prompt' => ' Seleccionar conector dinamico ... ',
            'id' => 'documento_id',
        ]
    ) ?>


    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descripcion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'id_traspaso')->textInput() ?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>