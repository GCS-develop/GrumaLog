<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Color $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="color-form">

    <?php $form = ActiveForm::begin([
        'id' => 'modal-form-color',
        'enableAjaxValidation' => true,
    ]);
    ?>

    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'codigo')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>


    <?php ActiveForm::end(); ?>

</div>