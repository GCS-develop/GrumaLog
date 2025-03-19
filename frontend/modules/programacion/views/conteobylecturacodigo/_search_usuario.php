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
use kartik\datetime\DateTimePicker;

use frontend\models\Estadoagenda;

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendaentregamercanciaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="agendaentregamercancia-search">

    <?php $form = ActiveForm::begin([
        'action' => ['indexusuario'],
        'method' => 'get',
    ]); ?>

    <div class="row">

    </div>

    <div class="row">
        
        <div class="col-lg-3">
            <?= $form->field($model, 'fechaInicio')->widget(DateTimePicker::class, [
                    'options' => ['placeholder' => 'Fecha y hora Desde ...'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'type' => DateTimePicker::TYPE_INLINE,
                        'format' => 'yyyy-mm-dd hh:ii',
                        'todayHighlight' => true,
                        //'minView' => 1, // Permite seleccionar hasta minutos (sin segundos)
                    ]
                ]); 
            ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'fechaFin')->widget(DateTimePicker::class, [
                    'options' => ['placeholder' => 'fecha y hora Hasta ...'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd hh:ii',
                        'todayHighlight' => true,
                        //'minView' => 1, // Permite seleccionar hasta minutos (sin segundos)
                    ]
                ]); 
            ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'nombreEmpleado') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'consecutivo') ?>
        </div>

    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
