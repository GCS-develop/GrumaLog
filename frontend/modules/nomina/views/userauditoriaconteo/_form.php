<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use frontend\models\Empleadologistica;
use frontend\models\Userauditoriaconteo;

/** @var yii\web\View $this */
/** @var frontend\models\Userauditoriaconteo $model */
?>

<div class="userauditoriaconteo-form">

    <?php $form = ActiveForm::begin([
        'id'                  => 'modal-form-userauditoriaconteo',
        'enableAjaxValidation' => true,
    ]); ?>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'idEmpleadoLogistica')->widget(Select2::classname(), [
                'data'          => Userauditoriaconteo::getListaDataNoUsuario(),
                'options'       => [
                    'placeholder' => 'Seleccionar Colaborador ...',
                    'multiple'    => false,
                ],
                'pluginOptions' => ['allowClear' => true],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'username')->textInput() ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'email') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'password')->passwordInput() ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'retypePassword')->passwordInput() ?>
        </div>
    </div>

    <div class="form-group text-center">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
