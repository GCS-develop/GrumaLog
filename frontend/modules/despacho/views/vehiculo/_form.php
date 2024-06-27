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
/** @var frontend\models\Vehiculo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="vehiculo-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-vehiculo',
                    'enableAjaxValidation' => true,
                ]);             
    ?>

    <div class="row">
        <div class="col-lg-9">
            <?= $form->field($model, 'descripcion')->textInput(['maxlength' => true]) ?>
        </div>
        
        <div class="col-lg-3">
            <?= $form->field($model, 'placa')->textInput(['maxlength' => true]) ?>
        </div>

    </div>


    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
