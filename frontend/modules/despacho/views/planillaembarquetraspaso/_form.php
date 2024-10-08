<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Planillaembarquetraspaso $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="planillaembarquetraspaso-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idPlanillaEmbarque')->textInput() ?>

    <?= $form->field($model, 'idTraspaso')->textInput() ?>

    <?= $form->field($model, 'idBodegaOrigen')->textInput() ?>

    <?= $form->field($model, 'idBodegaDestino')->textInput() ?>

    <?= $form->field($model, 'unidades')->textInput() ?>

    <?= $form->field($model, 'unidadesEmp')->textInput() ?>

    <?= $form->field($model, 'sello')->textInput() ?>

    <?= $form->field($model, 'fechaRecibido')->textInput() ?>

    <?= $form->field($model, 'idUsuarioRecibido')->textInput() ?>

    <?= $form->field($model, 'idEstado')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
