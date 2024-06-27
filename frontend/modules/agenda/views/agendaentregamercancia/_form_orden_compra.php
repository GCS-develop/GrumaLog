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
        font-size: 12px; /* Cambia el tamaño de la letra a 12px */
    }
    
    /* Cambiar el tamaño de la letra para los botones */
    button {
        font-size: 12px; /* Cambia el tamaño de la letra a 16px */
    }
    
');

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/dataOrdenCompra.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\datetime\DateTimePicker;

use frontend\models\Centrooperacion;
use frontend\models\Tipodocumento;
use frontend\models\Transportadora;

/** @var yii\web\View $this */
/** @var frontend\models\Agendaentregamercancia $model */
/** @var yii\widgets\ActiveForm $form */

?>

<div class="agendaentregamercancia-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-agendaentregamercancia',
                    'enableAjaxValidation' => true,
                ]); ?>

    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'idCentroOperacion')->widget(Select2::classname(), [
                    'data' => Centrooperacion::getListaDataCodigo(),
                    'options' => [
                        'placeholder' => 'Centro Operación ...', 
                        'multiple' => false,
                        'id' => 'id-centro-operacion',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
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
            <?= $form->field($model, 'numeroOrdenCompra')->textInput(['id' => 'numero-orden-compra']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'fechaOrden')->textInput(['readonly' => true, 'id' => 'fecha-orden']) ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'totalCantidadPedida')->textInput(['readonly' => true, 'type' => 'number', 'id' => 'total-cantidad-pedida']) ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'totalCantidadEntrada')->textInput(['readonly' => true, 'type' => 'number', 'id' => 'total-cantidad-entrada']) ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'totalCantidadPendiente')->textInput(['readonly' => true, 'type' => 'number', 'id' => 'total-cantidad-pendiente']) ?>
        </div>
    </div>
        
    <div class="row">
        <div class="col-lg-9">
            <?= $form->field($model, 'dataProveedor')->textInput(['readonly' => true, 'id' => 'data-proveedor']) ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'idOrdenCompra')->textInput(['readonly' => true, 'id' => 'id-orden-compra']) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>
                
    <?php ActiveForm::end(); ?>

</div>
