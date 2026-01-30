<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\models\Bodegas;

/** @var yii\web\View $this */
/** @var frontend\models\Impresoraspaxarbodega $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="impresoraspaxarbodega-form">

    <link rel="stylesheet" href="css/shared.css">

    <?php $form = ActiveForm::begin([
        'id' => 'modal-form-impresoraspaxarbodega',
        'enableAjaxValidation' => true,
    ]);
    ?>

    <div class="row">

        <div class="col-4">
            <?= $form->field($model, 'bodega_id')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => ' Bodega... ',
                    'id' => 'id-bodega',
                    'required' => true
                ]
            )
                ?>
        </div>

        <div class="col-4">
            <?= $form->field($model, 'tipo')->dropDownList(
                [
                    'ip' => 'ip',
                    'epl' => 'epl',
                    'recurso' => 'recurso',
                    'termica' => 'termica',

                ],
                [
                    'prompt' => 'Seleccione el tipo...',
                    'id' => 'id-tipo',
                    'required' => true,
                ]
            ) ?>
        </div>
        <div class="col-4">
            <?= $form->field($model, 'ip')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'puerto')->textInput() ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'recurso')->textInput(['maxlength' => true]) ?>
        </div>

    </div>

    <!-- <?= $form->field($model, 'bodega_id')->textInput() ?> -->
    <!-- 
    <?= $form->field($model, 'tipo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ip')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'puerto')->textInput() ?>

    <?= $form->field($model, 'recurso')->textInput(['maxlength' => true]) ?> -->

    <!-- <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?> -->

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>