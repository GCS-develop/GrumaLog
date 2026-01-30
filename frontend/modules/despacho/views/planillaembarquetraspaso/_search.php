<?php

use frontend\models\Bodegas;
use frontend\models\Estadodocumentoplanilla;
use frontend\models\Planillaembarquetraspaso;
use frontend\models\Tipodocumento;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\models\Estadodespacho;
use kartik\date\DatePicker;

/** @var yii\web\View $this */
/** @var frontend\models\search\PlanillaembarquetraspasoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<link rel="stylesheet" href="css/shared.css">

<div class="planillaembarquetraspaso-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">

        <div class="col-lg-3">
            <?=
            $form->field($model, 'fechaRecibido')->widget(DatePicker::className(), [
                'name' => 'fecha recibo',
                'language' => 'es',
                'options' => ['placeholder' => 'Fecha recibo ...', 'disabled' => false],
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => false
                ]
            ])
            ?>
        </div>

        <div class="col-lg-3">
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

        <div class="col-lg-3">
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

        <div class="col-3">
            <?= $form->field($model, 'usuarioRecibido') ?>
        </div>




    </div>
    <div class="row">
        <div class="col-3">
            <?php echo $form->field($model, 'codAlmacenOrigen')->dropDownList(
                Bodegas::getListaDataCodigo(),
                [
                    'prompt' => ' Seleccionar codigo ... ',
                    'id' => 'codigo de almacen origen',
                ]
            );
            ?>

        </div>

        <div class="col-3">
            <?php echo $form->field($model, 'codAlmacenDestino')->dropDownList(
                Bodegas::getListaDataCodigo(),
                [
                    'prompt' => ' Seleccionar codigo ... ',
                    'id' => 'codigo de almacen destino',
                ]
            );
            ?>

        </div>
        <div class="col-3">
            <?php echo $form->field($model, 'tipoDocumento')->dropDownList(
                Tipodocumento::getListaDataCodigo2(),
                [
                    'prompt' => ' Seleccionar tipo documento ... ',
                    'id' => 'tipoDocumento',
                ]
            );
            ?>

        </div>

        <div class="col-3">
            <?php echo $form->field($model, 'idEstado')->dropDownList(
                // Estadodespacho::getListaData(),
                Estadodocumentoplanilla::getListaData(),
                [
                    'prompt' => ' Seleccionar estado ... ',
                    'id' => 'idEstado',
                ]
            );
            ?>

        </div>

    </div>

    <div class="row">

        <div class="col-3">
            <?= $form->field($model, 'consecutivoDocumento') ?>
        </div>

        <div class="col-3">
            <?= $form->field($model, 'consecutivoInterno') ?>
        </div>

    </div>


    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
        <!-- <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary btn-lg btn-create']) ?> -->
    </div>

    <?php ActiveForm::end(); ?>

</div>