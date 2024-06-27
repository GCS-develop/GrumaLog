<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Traspasodetalle $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="traspasodetalle-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idTraspaso')->textInput() ?>

    <?= $form->field($model, 'idItem')->textInput() ?>

    <?= $form->field($model, 'cantidad')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
