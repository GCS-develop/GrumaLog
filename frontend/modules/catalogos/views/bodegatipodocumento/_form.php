<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Bodegatipodocumento $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="bodegatipodocumento-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idBodega')->textInput() ?>

    <?= $form->field($model, 'idTipoDocumento')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
