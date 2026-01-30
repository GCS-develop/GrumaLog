<?php
use frontend\models\Estadodespacho;
use frontend\models\Vehiculo;
use frontend\models\Bodegas;

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
use kartik\time\TimePicker;
use frontend\models\Transportadora;

/** @var yii\web\View $this */
/** @var frontend\models\search\PlanillaembarqueSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="planillaembarque-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


    <div class="row">

        <div class="col-lg-2">
            <?=
                $form->field($model, 'fechaDesde')->widget(DatePicker::className(), [
                    'name' => 'fechadesde',
                    'language' => 'es',
                    'options' => ['placeholder' => 'Fecha Cita Desde ...', 'disabled' => false],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ])
                ?>
        </div>

        <div class="col-lg-2">
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


        <div class="col-lg-2">
            <?=
                $form->field($model, 'horaDespacho')->widget(TimePicker::className(), [
                    'name' => 'horaDespacho',
                    'language' => 'es',
                    'options' => ['placeholder' => 'Hora despacho ...'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'showSeconds' => false, // Muestra segundos si es necesario
                        'minuteStep' => 1, // Incremento de minutos
                        'defaultTime' => false,
                        // 'defaultTime' => 'current', // Para seleccionar la hora actual por defecto
                    ]
                ]) ?>
        </div>

        <div class="col-lg-2">
            <?php echo $form->field($model, 'idTransportadora')->dropDownList(
                Transportadora::getListaData(),
                [
                    'prompt' => ' Seleccionar trasportadora ... ',
                    'id' => 'idTransportadora',
                ]
            );
            ?>
        </div>

        <div class="col-lg-2">
            <?php echo $form->field($model, 'idEstado')->dropDownList(
                Estadodespacho::getListaData(),
                [
                    'prompt' => ' Seleccionar estado ... ',
                    'id' => 'idEstado',
                ]
            );
            ?>
        </div>

        <div class="col-lg-2">
            <?php echo $form->field($model, 'Codigobodegaorigen')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => 'Seleccionar bodega',
                    'id' => 'Codigo bodega origen',
                ]
            );
            ?>
        </div>

        <div class="col-lg-2">
            <?php echo $form->field($model, 'nombreConductor') ?>
        </div>

    </div>

    <div class="row">
        <div class="col-lg-2">
            <?php echo $form->field($model, 'idVehiculo')->dropDownList(
                Vehiculo::getListaData(),
                [
                    'prompt' => ' Seleccionar estado ... ',
                    'id' => 'idVehiculo',
                ]
            );
            ?>
        </div>

        <div class="col-lg-2">
            <?php echo $form->field($model, 'sello') ?>
        </div>
        <div class="col-lg-2">
            <?php echo $form->field($model, 'id') ?>
        </div>
        <div class="col-lg-2">
            <?php echo $form->field($model, 'numeroDocumento') ?>
        </div>
        <div class="col-lg-2">
            <?php echo $form->field($model, 'numeroDocumentoInterno') ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
        <!-- <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary btn-lg btn-create']) ?> -->
    </div>

    <?php ActiveForm::end(); ?>

</div>