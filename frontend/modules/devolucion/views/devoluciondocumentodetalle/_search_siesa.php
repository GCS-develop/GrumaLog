<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/** @var yii\web\View $this */
/** @var frontend\models\search\DevoluciondocumentodetalleSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="devoluciondocumentodetalle-search">

    <?php $form = ActiveForm::begin([
        'action' => ['indexenviosiesa'],
        'method' => 'get',
    ]); ?>


    <div class="row">

        <div class="col-lg-2">
            <?= $form->field($model, 'codigoBodegaSalida') ?>
        </div>
        <div class="col-lg-2">
            <?= $form->field($model, 'item') ?>
        </div>
        <div class="col-lg-2">
            <?= $form->field($model, 'referencia') ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'numeroDocumento') ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'codigoBarras') ?>
        </div>


    </div>

    <div class="row">
        <div class="col-lg-3">
            <?=
                $form->field($model, 'fechaDesde')->widget(DatePicker::className(), [
                    'name' => 'fechadesde',
                    'language' => 'es',
                    'options' => ['placeholder' => 'Fecha Cita Desde ...', 'disabled' => false],
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

        <div class="col-lg-1">
            <?= $form->field($model, 'registrada')->dropDownList([
                '' => 'Todos',
                '1' => 'SI',
                '0' => 'NO',
            ]) ?>
        </div>
        <div class="col-lg-3">

            <?php echo $form->field($model, 'nombreProveedor') ?>

        </div>

    </div>




    <?php // echo $form->field($model, 'talla') ?>

    <?php // echo $form->field($model, 'color') ?>

    <?php // echo $form->field($model, 'referencia') ?>

    <?php // echo $form->field($model, 'itemResumen') ?>

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