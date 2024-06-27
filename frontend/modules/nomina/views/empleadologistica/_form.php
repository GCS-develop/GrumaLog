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

            <?php
                if ($model->isNewRecord) {
            ?>

            <?= $form->field($model, 'idEmpleado')->widget(Select2::classname(), [
                    'data' => Empleado::getListaDataNoLogistica(),
                    'options' => [
                        'placeholder' => 'Seleccionar Colaborador ...', 
                        'multiple' => false,
                        'id' => 'id-empleado',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>

            <?php } else { ?>

                <?= $form->field($model, 'idEmpleado')->widget(Select2::classname(), [
                    'data' => EmpleadoLogistica::getListaDataEmpleado(),
                    'options' => [
                        'placeholder' => 'Seleccionar Colaborador ...', 
                        'multiple' => false,
                        'id' => 'id-empleado',
                        'disabled' => true
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>

            <?php } ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'idEstado')->dropDownList(['1' => 'Activo', '0' => 'Inactivo'], 
                    [   'prompt' => ' Seleccionar Opción ... ', 
                        'id' => 'idestado',
                        'required'=>true]);
            ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
