<?php

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

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\number\NumberControl;

use frontend\models\Userconteo;

/** @var yii\web\View $this */
/** @var frontend\models\Programacionentregamercancia $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="programacionentregamercancia-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-programacionentregamercancia',
                    'enableAjaxValidation' => true,
                ]); 
    ?>

    <div class="row">
        <div class="col-lg-12">

            <?= $form->field($model, 'idUserConteo')->widget(Select2::classname(), [
                    'data' => Userconteo::getListaDataHabil(),
                    'options' => [
                        'placeholder' => 'Seleccionar Usuario ...', 
                        'multiple' => false,
                        'id' => 'iduserconteo',
                        'required' => 'required'
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'unidadesAsignadas')->widget(NumberControl::classname(), [
                    'maskedInputOptions' => [
                        //'prefix' => '$ ',
                        //'suffix' => ' ¢',
                        'min' => 0,
                        'digits' => 0,
                        'allowMinus' => false
                    ],

                ]);
            ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'puedeModificarEntrada')->dropDownList(['1' => 'SI', '0' => 'NO'], 
                    [   'prompt' => ' Seleccionar Opción ... ', 
                        'id' => 'puedemodificarentrada',
                        'required'=>true]);
            ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'unidadxPaquete')->textInput(['disabled' => false, 'type' => 'number', 'min' => 1, 'step' => 1 , 'id' => 'unidadxpaquete']) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
