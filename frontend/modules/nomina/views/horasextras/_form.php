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
use kartik\date\DatePicker;

use frontend\models\Empleado;
use frontend\models\Centrooperacion;

/** @var yii\web\View $this */
/** @var frontend\models\Horasextras $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="horasextras-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-horasextras',
                    'enableAjaxValidation' => true,
                ]); ?>

    <div class="row">
        <div class="col-lg-9">
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <?= 
                $form->field($model, 'fecha')->widget(DatePicker::className(),[
                    'name' => 'fecha', 
                    'language'=>'es',
                    'options' => ['placeholder' => 'Seleccionar Fecha ...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]) 
            ?>
        </div>

        <div class="col-lg-8">
            <?= $form->field($model, 'idEmpleado')->widget(Select2::classname(), [
                    'data' => Empleado::getListaData(),
                    'options' => [
                        'placeholder' => 'Seleccionar Empleado ...', 
                        'multiple' => false,
                        'id' => 'idempleado',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                    ]);    
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'idCO')->widget(Select2::classname(), [
                    'data' => Centrooperacion::getListaData(),
                    'options' => [
                        'placeholder' => 'Seleccionar Centro Operación ...', 
                        'multiple' => false,
                        'id' => 'idco',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                    ]);    
            ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'numeroHoras')->textInput(['type' => 'number', 'min' => 0.5, 'max' => 2.0,'step' => 0.5]) ?>
        </div>

        <div class="col-lg-3">    
            <?= $form->field($model, 'idEstado')->dropDownList(['1' => 'Sin Contabilizar', '2' => 'Contabilizado', '0' => 'Anulado'], 
                            [   'prompt' => ' Seleccionar Estado ... ', 
                                'id' => 'idestado',
                                'required'=>true]);
            ?>
        </div>

    </div>

    <div class="row">

    </div>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'observacion')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
