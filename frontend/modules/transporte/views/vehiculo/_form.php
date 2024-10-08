<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->registerCss('

    .btn-create {
        width: 300px;
    }

    .centrar {
        text-align: center;
    }
    
    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc; /* Color y grosor de la línea */
        margin: 10px 0; /* Espacio alrededor de la línea */
    }
    
');

/** @var yii\web\View $this */
/** @var frontend\models\Vehiculo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="vehiculo-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="container">
        <?= $form->field($model, 'descripcion')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'placa')->textInput(['maxlength' => true]) ?>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>