<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Devolucionimportacion $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="devolucionimportacion-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'numeroRegistros')->textInput() ?>

    <?= $form->field($model, 'totalCantidad')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
