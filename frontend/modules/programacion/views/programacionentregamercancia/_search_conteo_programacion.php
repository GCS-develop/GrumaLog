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
use kartik\select2\Select2;

use frontend\models\Estadoprogramacion;

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendaentregamercanciaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="agendaentregamercancia-search">

    <?php $form = ActiveForm::begin([
        'action' => ['indexconteoprogramacion', 'idagenda' => $idagenda],
        'method' => 'get',
    ]); ?>

    <div class="row">

        <div class="col-lg-3">
            <?= 
                $form->field($model, 'fechaDesde')->widget(DatePicker::className(),[
                    'name' => 'fechadesde', 
                    'language'=>'es',
                    'options' => ['placeholder' => 'Fecha Programación Desde ...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]) 
            ?>
        </div>

        <div class="col-lg-3">
            <?= 
                $form->field($model, 'fechaHasta')->widget(DatePicker::className(),[
                    'name' => 'fechahasta', 
                    'language'=>'es',
                    'options' => ['placeholder' => 'Fecha Programación Hasta ...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]) 
            ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'numeroOrdenCompra') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'idEstado')->widget(Select2::classname(), [
                    'data' => Estadoprogramacion::getListaData(),
                    'options' => [
                        'placeholder' => 'Seleccionar Estado ...', 
                        'multiple' => false,
                        'id' => 'idestadoprogramacion',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'nombreUsuario') ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
