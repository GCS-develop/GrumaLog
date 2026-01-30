<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\MovimentogastoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="movimiento-gasto-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'F_CIA') ?>

    <?= $form->field($model, 'F350_ID_CO') ?>

    <?= $form->field($model, 'F350_ID_TIPO_DOCTO') ?>

    <?= $form->field($model, 'F350_CONSEC_DOCTO') ?>

    <?= $form->field($model, 'F351_ID_AUXILIAR') ?>

    <?php // echo $form->field($model, 'F351_ID_TERCERO') ?>

    <?php // echo $form->field($model, 'F351_ID_CO_MOV') ?>

    <?php // echo $form->field($model, 'F351_ID_UN') ?>

    <?php // echo $form->field($model, 'F351_ID_CCOSTO') ?>

    <?php // echo $form->field($model, 'F351_ID_FE') ?>

    <?php // echo $form->field($model, 'F351_VALOR_DB') ?>

    <?php // echo $form->field($model, 'F351_VALOR_CR') ?>

    <?php // echo $form->field($model, 'F351_BASE_GRAVABLE') ?>

    <?php // echo $form->field($model, 'F351_DOCTO_BANCO') ?>

    <?php // echo $form->field($model, 'F351_NRO_DOCTO_BANCO') ?>

    <?php // echo $form->field($model, 'F351_NOTAS') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
