<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Talla $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="talla-form">

    <?php $form = ActiveForm::begin([
        'id' => 'modal-form-talla',
        'enableAjaxValidation' => true,
    ]);
    ?>

    <div class="row">
        <div class="col-4">
            <?= $form->field($model, 'codigo')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-4">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-4">
            <?= $form->field($model, 'orden')->textInput() ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>