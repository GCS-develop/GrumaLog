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
        <div class="col-lg-6">
            <?= $form->field($model, 'fechaAgenda')->widget(Select2::classname(), [
                    'data' => Agendapresupuestosubcategoria::getListaData($model->idAgenda, $categoria),
                    'options' => [
                        'placeholder' => 'Seleccionar Fecha ...', 
                        'multiple' => false,
                        'id' => 'fecha-agenda',
                        'required' => true
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>
        </div>

        <div class="col-lg-6">
            <?= 
                $form->field($model, 'horaAgenda')->widget(TimePicker::classname(), [
                    'pluginOptions' => [
                        'showSeconds' => false,
                        'showMeridian' => false,
                    ],
                    'options' => [
                        'required' => true
                    ]
                ]);
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'unidades')->textInput(['type' => 'number', 'min' => 1, 'step' => 1, 'id' => 'unidades', 'required' => true, 'readonly' => true]) ?>
        </div>

        <div class="col-lg-6">
            <?= $form->field($model, 'numeroCajas')->textInput(['type' => 'number', 'min' => 1, 'step' => 1, 'id' => 'numero-cajas', 'required' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <?= $form->field($model, 'idTransportadora')->dropDownList(Transportadora::getListaData(), 
                                                ['prompt' => ' Seleccionar Transportadora ... ',
                                                'id' => 'id-transportadora',
                                                'required' => true
                                                ])
            ?>                    
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'numeroGuia')->textInput(['maxlength' => true, 'id' => 'numero-guia']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <?= 
                $form->field($model, 'fechaContacto')->widget(DatePicker::className(),[
                    'name' => 'fecha-contacto', 
                    'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                    'language'=>'es',
                    'options' => [  'placeholder' => 'Fecha Contacto ...',
                                    'id' => 'fecha-contacto',
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

        <div class="col-lg-8">
            <?= $form->field($model, 'contacto')->textInput(['maxlength' => true, 'id' => 'contacto', 'required' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'observacion')->textInput(['maxlength' => true, 'id' => 'observacion']) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>
                
    <?php ActiveForm::end(); ?>

</div>
