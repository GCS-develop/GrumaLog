<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Productostiquetesprecio $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="productostiquetesprecio-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'descBodega')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'codigoBarra')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'item')->textInput() ?>

    <?= $form->field($model, 'descItem')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'detalleExt1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'detalleExt2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'existencia')->textInput() ?>

    <?= $form->field($model, 'proveedor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'marca')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'referencia')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'categoria')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subcategoria')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'precio')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
