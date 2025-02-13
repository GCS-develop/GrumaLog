<?php

use frontend\models\Tipodocumento;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\models\Estadotraspaso;
use frontend\models\Usertraspaso;
use kartik\date\DatePicker;
use frontend\models\Bodegas;


/** @var yii\web\View $this */
/** @var frontend\models\search\TraspasodetalleSearch $model */
/** @var yii\widgets\ActiveForm $form */

?>




<div class="traspasodetalle-search">

    <link rel="stylesheet" href="css/shared.css">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


    <div class="row">

        <div class="col-lg-3">
            <?= $form->field($model, 'bodegaorigen')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => ' Seleccionar bodega origen ... ',
                    'id' => 'bodegaorigen',
                ]
            ) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'bodegadestino')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => ' Seleccionar bodega destino ... ',
                    'id' => 'bodegadestino',
                ]
            ) ?>
        </div>

        <div class="col-lg-3">
            <?php
            echo $form->field($model, 'tipomovimiento')->label('Tipo de movimiento')->dropDownList(
                [
                    '1' => 'Traspaso',
                    '2' => 'Entradas',
                ],
                [
                    'prompt' => 'Seleccionar tipo de movimiento...',
                    'id' => 'tipoMovimiento',
                ]
            );
            ?>
        </div>

        <div class="col-lg-2">
            <?=
                $form->field($model, 'fechaDesde')->widget(DatePicker::className(), [
                    'name' => 'fecha inicio',
                    'language' => 'es',
                    'options' => ['placeholder' => 'Fecha Desde ...', 'disabled' => false],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => false
                    ]
                ])
                ?>
        </div>

        <div class="col-lg-2">
            <?=
                $form->field($model, 'fechaHasta')->widget(DatePicker::className(), [
                    'name' => 'fecha fin',
                    'language' => 'es',
                    'options' => ['placeholder' => 'Fecha Hasta ...', 'disabled' => false],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ])
                ?>
        </div>

        <div class="col-lg-2">
            <?= $form->field($model, 'consecutivosiesa')->label('Consecutivo siesa') ?>
        </div>

    </div>

    <div class="row">

        <div class="col-lg-2">
            <?= $form->field($model, 'idTraspaso')->label('Id traspaso') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'consecutivointerno')->label('Consecutivo interno') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'cantidad') ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'codigoitem')->label('Item') ?>
        </div>

    </div>

    <div class="row">

        <div class="col-lg-2">
            <?= $form->field($model, 'color') ?>
        </div>

        <div class="col-lg-2">
            <?= $form->field($model, 'talla') ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'proveedor') ?>
        </div>

        <div class="col-lg-2">
            <?php
            echo $form->field($model, 'estado')->dropDownList(
                Estadotraspaso::getListaData(),
                [
                    'prompt' => ' Seleccionar estado ... ',
                    'id' => 'idEstado',
                ]
            );
            ?>
        </div>

        <div class="col-lg-2">
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






    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>

        <!-- <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary btn-lg btn-create']) ?> -->

    </div>

    <?php ActiveForm::end(); ?>

</div>