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

use frontend\models\Tipodocumento;
use frontend\models\Centrooperacion;

/** @var yii\web\View $this */
/** @var frontend\models\Conteoentregamercancia $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="entradadocumento-form">

    <?php $form = ActiveForm::begin([
        'id' => 'modal-form-entradadocumento',
        'enableAjaxValidation' => true,
    ]);
    ?>

    <div class="row">

        <div class="col-lg-4">
            <?= $form->field($model, 'idCO')->widget(Select2::classname(), [
                'data' => Centrooperacion::getListaDataCodigo(),
                'options' => [
                    'placeholder' => 'Tipo Documento ...',
                    'multiple' => false,
                    'id' => 'id-co',
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
            <?= $form->field($model, 'consecutivo')->textInput(['id' => 'numero-documento']) ?>
        </div>

    </div>

    <div class="row">
        <div class="col-lg-6">
            <?=
                $form->field($model, 'fechaDocumento')->widget(DatePicker::className(), [
                    'name' => 'fecha-contacto',
                    'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                    'language' => 'es',
                    'options' => [
                        'placeholder' => 'Fecha Contacto ...',
                        'id' => 'fecha-contacto',
                        'required' => true
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ])
                ?>
        </div>

        <div class="col-lg-4">
            <!-- <?= $form->field($model, 'consignacion')->textInput(['type' => 'number', 'min' => 0, 'step' => 1, 'max' => 1, 'id' => 'consignacion', 'required' => true]) ?> -->
            <?= $form->field($model, 'consignacion')->textInput(['type' => 'number', 'min' => 0, 'step' => 1, 'max' => 1, 'id' => 'consignacion', 'required' => true, 'value' => $model->consignacion ?? 0]) ?>
        </div>
    </div>


    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>