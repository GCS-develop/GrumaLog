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
use kartik\date\DatePicker;
use frontend\models\TipoDocumento;
use frontend\models\Bodegas;
use frontend\models\Estadotraspaso;
use frontend\models\Usertraspaso;


/** @var yii\web\View $this */
/** @var frontend\models\searchTraspasoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="traspaso-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <!-- <?= $form->field($model, 'id') ?> -->

    <!-- <?= $form->field($model, 'idCentroOperacion') ?> -->
    <div class="row">
        <div class="col-lg-3">
            <?php echo $form->field($model, 'idTipoDocumento')->dropDownList(
                TipoDocumento::getListaDataCodigo(),
                [
                    'prompt' => ' Seleccionar tipo de documento ... ',
                    'id' => 'idTipoDocumento',
                ]
            )->label('Serie');
            ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'consecutivo')->label('Consecutivo'); ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'idBodegaOrigen')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => ' Seleccionar bodega origen ... ',
                    'id' => 'idBodegaOrigen',
                ]
            ) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'idBodegaDestino')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => ' Seleccionar bodega destino ... ',
                    'id' => 'idBodegaDestino',
                ]
            ) ?>
        </div>

    </div>


    <div class="row">
        <div class="col-lg-3">
            <?=
                $form->field($model, 'numeroCajas')->textInput(['type' => 'number', 'min' => 0, 'step' => 1, 'id' => 'numero-cajas'])
                ?>
        </div>
        <div class="col-lg-3">
            <?php echo $form->field($model, 'idEstado')->dropDownList(
                Estadotraspaso::getListaData(),
                [
                    'prompt' => ' Seleccionar estado ... ',
                    'id' => 'idEstado',
                ]
            );
            ?>

        </div>
        <div class="col-lg-3">
            <?=
                $form->field($model, 'fechaDesde')->widget(DatePicker::className(), [
                    'name' => 'fechadesde',
                    'language' => 'es',
                    'options' => ['placeholder' => 'Fecha Cita Desde ...' , 'disabled' => false],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        // 'format' =>'yyyy-mm-dd hh:ii:ss',
                        'todayHighlight' => false
                    ]
                ])
                ?>
        </div>

        <div class="col-lg-3">
            <?=
                $form->field($model, 'fechaHasta')->widget(DatePicker::className(), [
                    'name' => 'fechahasta',
                    'language' => 'es',
                    'options' => ['placeholder' => 'Fecha Cita Hasta ...', 'disabled' => false],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ])
                ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <?php
            echo $form->field($model, 'created_by')->label('Usuario Creador')
                ->dropDownList(
                    Usertraspaso::getListaDataUsertraspaso(),
                    [
                        'prompt' => ' Seleccionar usuario ... ',
                        'id' => 'created_by',
                    ]
                );
            ?>
        </div>
        <div class="col-lg-3">
            <?php
            echo $form->field($model, 'updated_by')->label('Ultimo usuario')->dropDownList(
                Usertraspaso::getListaDataUsertraspaso(),
                [
                    'prompt' => ' Seleccionar usuario ... ',
                    'id' => 'updated_by',
                ]
            );
            ?>
        </div>

    </div>
    <?php // echo $form->field($model, 'consecutivo') ?>


    <?php // echo $form->field($model, 'idUltimoItem') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
        <!-- <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary btn-lg btn-create']) ?> -->
    </div>

    <?php ActiveForm::end(); ?>

</div>