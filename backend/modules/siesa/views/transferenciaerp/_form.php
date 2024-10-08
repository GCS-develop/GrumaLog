<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use frontend\models\Conectoresdinamicos;

/** @var yii\web\View $this */
/** @var frontend\models\Transferenciaerp $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transferenciaerp-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-transferenciaerp',
                    'enableAjaxValidation' => true,
    ]); ?>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'idConectorDinamico')->dropDownList(Conectoresdinamicos::getListaData(), ['prompt' => ' Conector SIESA ... ']) ?>                    
        </div>

        <div class="col-lg-6">
            <?= $form->field($model, 'documento')->textInput() ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'descripcion')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'notas')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
