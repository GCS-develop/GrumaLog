<?php
use frontend\models\Estadodespacho;
use frontend\models\Vehiculo;

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

    <!-- <?= $form->field($model, 'id') ?> -->

    <div class="row">
        <div class="col-lg-3">

            <?=
                $form->field($model, 'fechaDespacho')->widget(DatePicker::className(), [
                    'name' => 'fechaDespacho',
                    'language' => 'es',
                    'options' => ['placeholder' => 'Fecha despacho...'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ])
                ?>
        </div>
        <div class="col-lg-3">
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
        <div class="col-lg-3">
            <?php echo $form->field($model, 'idTransportadora')->dropDownList(
                Transportadora::getListaData(),
                [
                    'prompt' => ' Seleccionar trasportadora ... ',
                    'id' => 'idTransportadora',
                ]
            );
            ?>
        </div>

        <div class="col-lg-3">
            <?php echo $form->field($model, 'idEstado')->dropDownList(
                Estadodespacho::getListaData(),
                [
                    'prompt' => ' Seleccionar estado ... ',
                    'id' => 'idEstado',
                ]
            );
            ?>
        </div>

    </div>

    <div class="row">
        <div class="col-lg-3">
        <?php echo $form->field($model, 'idVehiculo')->dropDownList(
                Vehiculo::getListaData(),
                [
                    'prompt' => ' Seleccionar estado ... ',
                    'id' => 'idVehiculo',
                ]
            );
            ?>        </div>
        <div class="col-lg-3">
            <?php echo $form->field($model, 'placa') ?>
        </div>
        <!-- <div class="col-lg-3">
            <?php echo $form->field($model, 'idConductor') ?>
        </div> -->
        <div class="col-lg-3">
            <?php echo $form->field($model, 'nombreConductor') ?>
        </div>
        <div class="col-lg-3">
            <?php echo $form->field($model, 'sello') ?>
        </div>

    </div>

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