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

use frontend\models\Estadoagenda;

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendaentregamercanciaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="agendaentregamercancia-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index', 'id' => $idagenda],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'id') ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'codigoCentroOperacion') ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'codigoTipoDocumento') ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'numeroOrdenCompra') ?>
        </div>

    </div>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'idEstado')->dropDownList(Estadoagenda::getListaData(), 
                                    ['prompt' => ' Seleccionar Estado ... ',
                                    'id' => 'idestado',
                                ]) 
            ?>                    
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'razonSocial') ?>
        </div>

        <div class="col-lg-3">
            <?= 
                $form->field($model, 'fechaDesde')->widget(DatePicker::className(),[
                    'name' => 'fechadesde', 
                    'language'=>'es',
                    'options' => ['placeholder' => 'Fecha Cita Desde ...'],
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
                    'options' => ['placeholder' => 'Fecha Cita Hasta ...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]) 
            ?>
        </div>

    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
