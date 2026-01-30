<?php

use frontend\models\SiesaConector;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->registerCss('

    .btn-create {
        width: 300px;
    }
    
    .centrar {
        text-align: center;
    }

');

/** @var yii\web\View $this */
/** @var frontend\models\search\SiesaconectordocumentoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="siesa-conector-documento-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="row">
        <div class="col-3">
            <?= $form->field($model, 'id') ?>
        </div>
        <div class="col-3">
            <?= $form->field($model, 'conector_id')->dropDownList(
                SiesaConector::getListaData(),
                [
                    'prompt' => ' Seleccionar conector ... ',
                    'id' => 'idSiesaconector',
                ]
            ) ?>
        </div>
        <div class="col-3">
            <?= $form->field($model, 'nombre') ?>
        </div>
        <div class="col-3">
            <?= $form->field($model, 'descripcion') ?>
        </div>
        <div class="col-3">
            <?= $form->field($model, 'id_traspaso') ?>
        </div>

        <div class="col-3">
            <?= $form->field($model, 'consecutivoSiesa')  ?>
        </div>

        <div class="col-3">
            <?= $form->field($model, 'usuarioTransferencia') ?>
        </div>
        <div class="col-3">
            <?= $form->field($model, 'tieneAen')->dropDownList(
                [
                    1 => 'Sí',
                    0 => 'No',
                ],
                [
                    'prompt' => ' ¿Tiene AEN? ... ',
                    'id' => 'idtieneAen',
                ]
            ) ?>

        </div>
        <div class="col-3">
            <?= $form->field($model, 'created_at')  ?>
        </div>


    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
        <!-- <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary btn-lg btn-create']) ?> -->
    </div>

    <?php ActiveForm::end(); ?>

</div>