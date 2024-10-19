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

use frontend\models\Conductor;
use frontend\models\Estadodespacho;
use frontend\models\Transportadora;
use frontend\models\Vehiculo;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Planillaembarque $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="planillaembarque-form">

    <?php $form = ActiveForm::begin([
        'id' => 'modal-form-despacho',
        'enableAjaxValidation' => true,
    ]);
    ?>

    <div class="row">
        <div class="col-4">
            <?= $form->field($model, 'idTransportadora')->dropDownList(
                Transportadora::getListaData(),
                [
                    'prompt' => ' Transportadora ... ',
                    'id' => 'id-transportadora',
                    'required' => true
                ]
            )
                ?>

        </div>
        <div class="col-4">
            <?= $form->field($model, 'idVehiculo')->dropDownList(
                Vehiculo::getListaData(),
                [
                    'prompt' => ' Vehiculo ... ',
                    'id' => 'id-vehiculo',
                    'required' => true
                ]
            )
                ?>
        </div>
        <div class="col-4">
            <?= $form->field($model, 'placa')->textInput(['maxlength' => true]) ?>
        </div>



        <div class="col-4">
            <?= $form->field($model, 'idConductor')->dropDownList(
                Conductor::getListaData(),
                [
                    'prompt' => ' Conductor ... ',
                    'id' => 'id-conductor',
                    'required' => true
                ]
            )
                ?>
        </div>
        <!-- <div class="col-4">
            <?= $form->field($model, 'nombreConductor')->textInput(['maxlength' => true]) ?>
        </div> -->
        <div class="col-4">
            <?= $form->field($model, 'sello')->textInput(['maxlength' => true]) ?>

        </div>
        <div class="col-4">
            <?= $form->field($model, 'idEstado')->dropDownList(
                Estadodespacho::getListaData(),
                [
                    'prompt' => ' Estado ... ',
                    'id' => 'id-estado',
                    'required' => true
                ]
            )
                ?>
        </div>

    </div>

    <!-- 
    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?> -->

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-success btn-lg']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>