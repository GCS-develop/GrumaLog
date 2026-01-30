<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\VentasimportadasSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ventasimportadas-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'codigoCentroOperacion') ?>

    <?= $form->field($model, 'nombreCentroOperacion') ?>

    <?= $form->field($model, 'fecha') ?>

    <?= $form->field($model, 'rowid_item_ext') ?>

    <?php // echo $form->field($model, 'item') ?>

    <?php // echo $form->field($model, 'codigobarra') ?>

    <?php // echo $form->field($model, 'descripcion') ?>

    <?php // echo $form->field($model, 'descripcionCorta') ?>

    <?php // echo $form->field($model, 'color') ?>

    <?php // echo $form->field($model, 'talla') ?>

    <?php // echo $form->field($model, 'referencia') ?>

    <?php // echo $form->field($model, 'nombreproveedor') ?>

    <?php // echo $form->field($model, 'proveedor') ?>

    <?php // echo $form->field($model, 'unidades') ?>

    <?php // echo $form->field($model, 'precio_aplicado') ?>

    <?php // echo $form->field($model, 'total') ?>

    <?php // echo $form->field($model, 'costo_prom_tot') ?>

    <?php // echo $form->field($model, 'costo_prom_mp') ?>

    <?php // echo $form->field($model, 'factor') ?>

    <?php // echo $form->field($model, 'unidadmedida') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
