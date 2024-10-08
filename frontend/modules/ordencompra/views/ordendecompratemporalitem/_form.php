<?php


$this->registerCss('

    .btn-create {
        width: 300px;
    }

    .centrar {
        text-align: center;
    }

    /* styles.css */

    /* Cambiar el tamaño de la letra para todo el formulario */
    form {
        font-size: 12px; /* Cambia el tamaño de la letra a 16px */
    }
    
    /* Cambiar el tamaño de la letra para etiquetas de campo */
    label {
        font-size: 12px; /* Cambia el tamaño de la letra a 14px */
    }
    
    /* Cambiar el tamaño de la letra para los inputs de texto */
    input[type="text"] {
        font-size: 14px; /* Cambia el tamaño de la letra a 14px */
    }
    
    /* Cambiar el tamaño de la letra para los botones */
    button {
        font-size: 12px; /* Cambia el tamaño de la letra a 16px */
    }

    /* Cambiar el tamaño de la letra para campos de entrada numérica */
    input[type="number"] {
        font-size: 12px; /* Cambia el tamaño de la letra a 12px */
    }

    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc; /* Color y grosor de la línea */
        margin: 10px 0; /* Espacio alrededor de la línea */
    }

    .title {
        font-weight: bold;
        text-align: center;
    }
    
');

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;

use frontend\models\Centrooperacion;
use frontend\models\Bodegas;
use frontend\models\Item;
use frontend\models\Color;
use frontend\models\Talla;

/** @var yii\web\View $this */
/** @var frontend\models\Ordendecompratemporalitem $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ordendecompratemporalitem-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-ordencompra',
                    'enableAjaxValidation' => false,
                ]); 
    ?>

    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'idBodega')->widget(Select2::classname(), [
                    'data' => Bodegas::getListaData(),
                    'options' => [
                        'placeholder' => 'Bodega ...', 
                        'multiple' => false,
                        'id' => 'bodega',  
                        'autofocus' => 'autofocus',  
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]);    
            ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'idCOMovimiento')->widget(Select2::classname(), [
                    'data' => Centrooperacion::getListaDataCodigo(),
                    'options' => [
                        'placeholder' => 'Centro Operación ...', 
                        'multiple' => false,
                        'id' => 'mySelect2',  
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]);    
            ?>
        </div>

        <div class="col-lg-4">
            <?= 
                $form->field($model, 'fechaEntrega')->widget(DatePicker::className(),[
                    'name' => 'fecha-entrega', 
                    'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                    'language'=>'es',
                    'options' => [  'placeholder' => 'Fecha Entrega ...',
                                    'id' => 'fecha-entrega',
                                    'required' => true
                    ],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]) 
            ?>
        </div>
    </div>

    <div class="row">

        <div class="col-lg-4">
            <?= $form->field($model, 'codigoMotivo')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'cantidadPedida')->textInput([
                                                'type' => 'number', 
                                                'id'=>'cantidad',
                                                'required' =>true, 
                                                'min'=>"1", 'step'=>"1",
                                            ])
            ?>
        </div>

        <div class="col-lg-4">   
            <?=
                $form->field($model, 'precioUnitario')->widget(\yii\widgets\MaskedInput::className(), [
                    'options' => [
                        'id' => 'precioUnitario',
                        'class' => 'form-control',
                        'readonly' => false,
                        'title' => 'Precio Unitario'
                    ],
                    'clientOptions' => [
                        'alias' => 'decimal',
                        'digits' => 0,
                        'allowMinus' => false,
                        'groupSeparator' => ',',
                        'autoGroup' => true,
                        'removeMaskOnSubmit' => true
                    ],
                ])
            ?>                        
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'item')->textInput([
                                                'type' => 'number', 
                                                'id'=>'item',
                                                'required' =>true, 
                                                'min'=>"1", 'step'=>"1",
                                            ])
            ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'idColor')->dropDownList(Color::getListaData(), 
                                                        ['prompt' => ' Seleccionar Color ... ',
                                                        'id' => 'id-color',
                                                        'required' => true
                                                        ])
            ?> 
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'idTalla')->dropDownList(Talla::getListaData(), 
                                                        ['prompt' => ' Seleccionar Talla ... ',
                                                        'id' => 'id-talla',
                                                        'required' => true
                                                        ])
            ?> 
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
