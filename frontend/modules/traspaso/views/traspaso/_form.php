<?php

// Definir el estilo CSS directamente en la vista
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
use frontend\models\Bodegas;
use frontend\models\Estadotraspaso;


/** @var yii\web\View $this */
/** @var frontend\models\Traspaso $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="traspaso-form">

    <?php $form = ActiveForm::begin([
        'id' => 'modal-form-traspaso',
        'enableAjaxValidation' => true,
    ]);
    ?>

    <div class="row">

        <!-- <div class="col-6">
            <?= $form->field($model, 'idCentroOperacion')->textInput() ?>
        </div> -->

        <div class="col-6">
            <?= $form->field($model, 'idBodegaOrigen')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => ' Bodega Origen ... ',
                    'id' => 'id-bodega-origen',
                    'required' => true
                ]
            )
                ?>
        </div>

        <div class="col-6">
            <?= $form->field($model, 'idBodegaDestino')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => ' Bodega Destino ... ',
                    'id' => 'id-bodega-destino',
                    'required' => true
                ]
            )
                ?>
        </div>

        <div class="col-6">
            <?= $form->field($model, 'numeroCajas')->textInput(
                ['maxlength' => true, 'id' => 'numero-cajas', 'type' => 'number']
            )
                ?>
        </div>

        <!-- <div class="col-6">
            <?= $form->field($model, 'idTipoDocumento')->textInput() ?>
        </div> -->

        <!-- <div class="col-6">
            <?= $form->field($model, 'consecutivo')->textInput() ?>
        </div> -->

        <div class="col-6">
            <?= $form->field($model, 'idEstado')->dropDownList(
                 Estadotraspaso::getListaData(),
                [
                    'prompt' => ' Estado ... ',
                    'id' => 'id-estado',
                    'required' => true,
                    'disabled' => true,
                ]
            )
                ?>
        </div>

        <!-- <div class="col-6">
            <?= $form->field($model, 'idUltimoItem')->textInput() ?>
        </div> -->
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>