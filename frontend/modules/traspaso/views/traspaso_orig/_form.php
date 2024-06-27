<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Traspaso $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="traspaso-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idBodegaOrigen')->textInput() ?>

    <?= $form->field($model, 'idBodegaDestino')->textInput() ?>

    <?= $form->field($model, 'numeroCajas')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
