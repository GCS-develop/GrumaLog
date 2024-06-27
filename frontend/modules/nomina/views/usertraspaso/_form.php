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
use kartik\select2\Select2;

use frontend\models\Empleado;
use frontend\models\Empleadologistica;

/** @var yii\web\View $this */
/** @var frontend\models\Empleadologistica $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="empleadologistica-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-empleadologistica',
                    'enableAjaxValidation' => true,
                ]); ?>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'idEmpleadoLogistica')->widget(Select2::classname(), [
                    'data' => EmpleadoLogistica::getListaDataNoUsuarioConteo(),
                    'options' => [
                        'placeholder' => 'Seleccionar Colaborador ...', 
                        'multiple' => false,
                        'id' => 'id-empleado',
                        'disabled' => false
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>

        </div>
    </div>

    <div class="row">
        <?php 
        $disable = true;
        if ($model->username == null) { 
                $disable = false;
         }  
         ?>

        <div class="col-lg-6"> 
            <?= $form->field($model, 'username')->textInput([ 'disabled' => $disable])?>
        </div>

        <div class="col-lg-6">
            <?= $form->field($model, 'email') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6"> 
            <?= $form->field($model, 'password')->passwordInput() ?>
        </div>

        <div class="col-lg-6">
            <?= $form->field($model, 'retypePassword')->passwordInput() ?>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-12">
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
