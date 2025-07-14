<?php

use frontend\models\SiesaConector;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\SiesaConectorDocumentoCampo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="siesa-conector-documento-campo-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-4">
            <?= $form->field($model, 'conector_id')->dropDownList(
                SiesaConector::getListaData(),
                [
                    'prompt' => 'Seleccionar conector dinamico ... ',
                    'id' => 'conector_id ',
                ]
            )
                ?>

        </div>
        <div class="col-4">
            <?= $form->field($model, 'nombre_campo')->textInput(['maxlength' => true]) ?>

        </div>
        <div class="col-4">
            <?= $form->field($model, 'alias')->textInput(['maxlength' => true]) ?>

        </div>
        <div class="col-4">
            <?= $form->field($model, 'tipo_dato')->textInput(['maxlength' => true]) ?>

        </div>
        <div class="col-4">
            <?= $form->field($model, 'obligatorio')->textInput() ?>

        </div>
    </div>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>