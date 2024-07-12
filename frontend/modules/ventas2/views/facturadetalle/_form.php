<?php

$this->registerCss('

    .btn-create {
        width: 300px;
    }
    
    .centrar {
        text-align: center;
    }

');

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Facturadetalle $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="facturadetalle-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'codigoBarra')->textInput(['maxlength' => true, 'disabled' => true]) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'item')->textInput(['maxlength' => true, 'disabled' => true]) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'color')->textInput(['disabled' => true]) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'talla')->textInput(['disabled' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'referencia')->textInput(['maxlength' => true, 'disabled' => true]) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'descripcion')->textInput(['maxlength' => true, 'disabled' => true]) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'cantidadBase')->textInput(['disabled' => true]) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'precioUnitario')->textInput(['disabled' => false]) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
