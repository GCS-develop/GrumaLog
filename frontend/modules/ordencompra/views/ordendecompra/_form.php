<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

use frontend\models\Centrooperacion;
use frontend\models\Tipodocumento;


/** @var yii\web\View $this */
/** @var frontend\models\Ordendecompra $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ordendecompra-form">

<?php $form = ActiveForm::begin([
                    'id' => 'modal-form-ordendecompra',
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

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>
                
    <?php ActiveForm::end(); ?>

</div>
