<?php

use frontend\models\Tipodocumento;
use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\TraspasodetalletiendaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="traspasodetalletienda-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
   <div class="row">
        <div class="col-lg-3">
            <?php echo $form->field($model, 'serie')->dropDownList(
                Tipodocumento::getListaDataCodigoTraspaso(),
                [
                    'prompt' => ' Seleccionar tipo de documento ... ',
                    'id' => 'idTipoDocumento',
                ]
            )->label('serie');
            ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'consecutivoSiesa')->label('Consecutivo siesa'); ?>
        </div>
        <div class="col-lg-3">
            <?=
                $form->field($model, 'fechaDesde')->widget(DatePicker::className(), [
                    'name' => 'fechadesde',
                    'language' => 'es',
                    'options' => [
                        'placeholder' => 'Fecha Cita Desde ...',
                        'value' => $model->fechaDesde ?? date('Y-m-01'), // <-- aquí se establece por defecto
                        'disabled' => false
                    ],
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

    <!-- <?= $form->field($model, 'id') ?> -->

    <!-- <?= $form->field($model, 'idTraspaso') ?> -->

    <!-- <?= $form->field($model, 'idItem') ?> -->

    <!-- <?= $form->field($model, 'cantidad') ?> -->

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
        <!-- <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary btn-lg btn-create']) ?> -->
    </div>

    <?php ActiveForm::end(); ?>

</div>