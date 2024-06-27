<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

use common\models\ProcedimientosGenerales;

/** @var yii\web\View $this */
/** @var frontend\models\Agendapresupuesto $model */
/** @var yii\widgets\ActiveForm $form */

$numero = 0;
$currentMonth = date('m');
if ($currentMonth == 12){
    $numero = 1;
}

$currentYear = date('Y');
$yearsList = range($currentYear, $currentYear + $numero);
$yearsList = array_combine($yearsList, $yearsList);

?>

<div class="agendapresupuesto-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-horasextras',
                    'enableAjaxValidation' => true,
                ]); ?>

    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'periodoAnio')->widget(Select2::classname(), [
                'data' => $yearsList,
                'options' => ['placeholder' => 'Selecciona un año...'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]); ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'periodoMes')->dropDownList(ProcedimientosGenerales::meses(), ['prompt' => 'Selecciona un mes...']) ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'idEstado')->dropDownList(['1' => 'Abierto', '0' => 'Cerrado'], 
                    [   'prompt' => ' Seleccionar Opción ... ', 
                        'id' => 'idestado',
                        'required'=>true]);
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'observacion')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
