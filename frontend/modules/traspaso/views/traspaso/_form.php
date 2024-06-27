<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Traspaso $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="traspaso-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idCentroOperacion')->textInput() ?>

    <?= $form->field($model, 'idBodegaOrigen')->textInput() ?>

    <?= $form->field($model, 'idBodegaDestino')->textInput() ?>

    <?= $form->field($model, 'numeroCajas')->textInput() ?>

    <?= $form->field($model, 'idTipoDocumento')->textInput() ?>

    <?= $form->field($model, 'consecutivo')->textInput() ?>

    <?= $form->field($model, 'idEstado')->textInput() ?>

    <?= $form->field($model, 'idUltimoItem')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
