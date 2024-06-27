<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Conteoentregamercancia $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conteoentregamercancia-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idProgramacionEntregaMercancia')->textInput() ?>

    <?= $form->field($model, 'idItem')->textInput() ?>

    <?= $form->field($model, 'unidadesConteo')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
