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

use frontend\models\Centrooperacion;
use frontend\models\Tipodocumento;

/** @var yii\web\View $this */
/** @var frontend\models\Pedido $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="pedido-form">

<?php $form = ActiveForm::begin([
                    'id' => 'modal-form-pedido',
                    'enableAjaxValidation' => false,
                ]); ?>

    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'idCentroOperacion')->widget(Select2::classname(), [
                    'data' => Centrooperacion::getListaDataCodigo(),
                    'options' => [
                        'placeholder' => 'Centro Operación ...', 
                        'multiple' => false,
                        'id' => 'id-centro-operacion',
                        'required' => true
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'idTipoDocumento')->widget(Select2::classname(), [
                    'data' => Tipodocumento::getListaDataCodigoAgendamiento(),
                    'options' => [
                        'placeholder' => 'Tipo Documento ...', 
                        'multiple' => false,
                        'id' => 'id-tipo-documento',
                        'required' => true
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'consecutivo')->textInput(['id' => 'numero-orden-compra']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'observaciones')->textInput(['id' => 'observaciones']) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>
                
    <?php ActiveForm::end(); ?>

</div>
