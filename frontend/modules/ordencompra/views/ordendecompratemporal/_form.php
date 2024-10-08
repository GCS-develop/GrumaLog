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
use frontend\models\Tipodocumento;
use frontend\models\Proveedor;
use frontend\models\Comprador;
use frontend\models\Condicionpago;

/** @var yii\web\View $this */
/** @var frontend\models\Ordendecompratemporal $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ordendecompratemporal-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-ordencompra',
                    'enableAjaxValidation' => false,
                ]); 
    ?>

    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'idCO')->widget(Select2::classname(), [
                    'data' => Centrooperacion::getListaDataCodigo(),
                    'options' => [
                        'placeholder' => 'Centro Operación ...', 
                        'multiple' => false,
                        'id' => 'mySelect2',  
                        'autofocus' => 'autofocus',  
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]);    
            ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'idTipoDocumento')->widget(Select2::classname(), [
                    'data' => Tipodocumento::getListaDataCodigo(),
                    'options' => [
                        'placeholder' => 'Tipo Documento ...', 
                        'multiple' => false,
                        'id' => 'id-tipo-documento',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>
        </div>

        <div class="col-lg-4">
            <?= 
                $form->field($model, 'fechaDocumento')->widget(DatePicker::className(),[
                    'name' => 'fecha-documento', 
                    'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                    'language'=>'es',
                    'options' => [  'placeholder' => 'Fecha Documento ...',
                                    'id' => 'fecha-documento',
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
        <div class="col-lg-9">
            <?= $form->field($model, 'idProveedor')->widget(Select2::classname(), [
                    'data' => Proveedor::getListaData(),
                    'options' => [
                        'placeholder' => 'Selecionar Proveedor ...', 
                        'multiple' => false,
                        'id' => 'idproveedor',  
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]);    
            ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'sucursalProveedor')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-9">
            <?= $form->field($model, 'idComprador')->widget(Select2::classname(), [
                    'data' => Comprador::getListaData(),
                    'options' => [
                        'placeholder' => 'Selecionar Comprador ...', 
                        'multiple' => false,
                        'id' => 'idcomprador',  
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]);    
            ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'idCondicionPago')->dropDownList(Condicionpago::getListaData(), 
                                                        ['prompt' => ' Seleccionar Condición Pago ... ',
                                                        'id' => 'id-condicionpago',
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
