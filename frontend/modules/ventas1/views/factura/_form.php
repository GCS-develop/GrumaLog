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

use frontend\modules\ventas\models\Proveedor;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Factura $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="factura-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-factura',
                    'enableAjaxValidation' => true,
                ]); ?>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'centroOperacion')->textInput(['maxlength' => true, 'disabled' => true]) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'tipoDocumento')->textInput(['maxlength' => true, 'disabled' => true]) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'consecutivoDocumento')->textInput(['disabled' => true]) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'fechaDocumento')->textInput(['disabled' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-9">
            <?= $form->field($model, 'idProveedor')->widget(Select2::classname(), [
                    'data' => Proveedor::getListaData(),
                    'options' => [
                        'placeholder' => 'Seleccionar proveedor ...', 
                        'multiple' => false,
                        'id' => 'id-proveedor',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'codigoSucursal')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?= 
                $form->field($model, 'fechaDesde')->widget(DatePicker::className(),[
                    'name' => 'fechadesde', 
                    'language'=>'es',
                    'options' => ['placeholder' => 'Fecha Desde ...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]) 
            ?>
        </div>

        <div class="col-lg-6">
            <?= 
                $form->field($model, 'fechaHasta')->widget(DatePicker::className(),[
                    'name' => 'fechahasta', 
                    'language'=>'es',
                    'options' => ['placeholder' => 'Fecha Hasta ...'],
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
            <?= $form->field($model, 'prefijoDocumentoProveedor')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'consecutivoDocumentoProveedor')->textInput() ?>
        </div>

        <div class="col-lg-4">
            <?= 
                $form->field($model, 'fechaDocumentoProveedor')->widget(DatePicker::className(),[
                    'name' => 'fechadocumentoproveedor', 
                    'language'=>'es',
                    'options' => ['placeholder' => 'Fecha Factura ...'],
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
            <?= $form->field($model, 'condicionPago')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'tipoProveedor')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'porcentajeCuota')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <?= 
                $form->field($model, 'fechaVencimientoCuota')->widget(DatePicker::className(),[
                    'name' => 'fechavencimientocuota', 
                    'language'=>'es',
                    'options' => ['placeholder' => 'Fecha Vcto Cuota ...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]) 
            ?>
        </div>

        <div class="col-lg-4">
            <?= 
                $form->field($model, 'fechaProntoPago')->widget(DatePicker::className(),[
                    'name' => 'fechaprontopago', 
                    'language'=>'es',
                    'options' => ['placeholder' => 'Fecha Pronto Pago ...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]) 
            ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'tieneNotaCredito')->dropDownList(
                                            [   '0' => 'No', 
                                                '1' => 'Si'
                                            ], 
                    [   'prompt' => ' Seleccionar Opción ... ', 
                        'id' => 'error',
                        'required'=>false]);
            ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
