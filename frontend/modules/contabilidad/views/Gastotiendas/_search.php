<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\GastotiendasSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="documento-gasto-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'F350_ID_CO') ?>

    <?= $form->field($model, 'F350_ID_TIPO_DOCTO') ?>

    <?= $form->field($model, 'F350_CONSEC_DOCTO') ?>

    <?= $form->field($model, 'F350_FECHA') ?>

    <?= $form->field($model, 'F350_ID_TERCERO') ?>

    <?php // echo $form->field($model, 'F350_IND_ESTADO') ?>

    <?php // echo $form->field($model, 'F350_NOTAS') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
