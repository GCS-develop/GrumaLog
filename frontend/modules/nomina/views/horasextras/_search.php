<?php

$this->registerCss('
    .mi-gridview {
        font-size: 12px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }
	
	.btn-search {
        width: 300px;
    }
');

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/** @var yii\web\View $this */
/** @var frontend\models\search\HorasextrasSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="horasextras-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3">
                    <?= 
                        $form->field($model, 'fechaInicio')->widget(DatePicker::className(),[
                            'name' => 'fechainicio', 
                            'language'=>'es',
                            'options' => ['placeholder' => 'Seleccionar Fecha ...'],
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
                        $form->field($model, 'fechaFin')->widget(DatePicker::className(),[
                            'name' => 'fechafin', 
                            'language'=>'es',
                            'options' => ['placeholder' => 'Seleccionar Fecha ...'],
                            'pluginOptions' => [
                                'autoclose'=>true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true
                            ]
                        ]) 
                    ?>
                </div>

                <div class="col-lg-3">
                    <?= $form->field($model, 'codigoCO') ?>
                </div>

                <div class="col-lg-3">
                    <?= $form->field($model, 'nombreCO') ?>
                </div>

            </div>

            <div class="row">
                <div class="col-lg-3">
                    <?= $form->field($model, 'identificacion') ?>
                </div>

                <div class="col-lg-6">
                    <?= $form->field($model, 'nombreEmpleado') ?>
                </div>

                <div class="col-lg-3">    
                    <?= $form->field($model, 'idEstado')->dropDownList(['1' => 'Sin Contabilizar', '2' => 'Contabilizado', '0' => 'Anulado'], 
                                    [   'prompt' => ' Seleccionar Estado ... ', 
                                        'id' => 'idestado']);
                    ?>
                </div>
            </div>

            <div class="row">

                <div class="col-lg-12">
                    <?= $form->field($model, 'observacion') ?>
                </div>
            </div>

			<div class="form-group" align="center">
				<?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-search']) ?>
			</div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
