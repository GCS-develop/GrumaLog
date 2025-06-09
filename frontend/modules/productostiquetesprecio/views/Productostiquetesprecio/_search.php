<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\ProductostiquetesprecioSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="productostiquetesprecio-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <!-- <?= $form->field($model, 'id') ?> -->

    <div class="row mt-3">
        <div class="col-2">
            <!-- <?= $form->field($model, 'descBodega') ?> -->
            <?php
            echo $form->field($model, 'descBodega')->label('Bodega')->dropDownList(
                $model->getListaDataDescBodega(),
                [
                    'prompt' => ' Seleccionar bodega ... ',
                    'id' => 'descBodega',
                ]
            );
            ?>

        </div>

        <div class="col-2">
            <?php
            echo $form->field($model, 'categoria')->label('categoria')->dropDownList(
                $model->getListaDataCategoria(),
                [
                    'prompt' => ' Seleccionar categoria ... ',
                    'id' => 'categoria',
                ]
            );
            ?>
        </div>


        <div class="col-2">
            <?= $form->field($model, 'item') ?>
        </div>

        <div class="col-3">
            <?= $form->field($model, 'codigoBarra') ?>
        </div>
        <div class="col-3">
            <?= $form->field($model, 'descItem') ?>
        </div>
    </div>

    <div class="row mt-1">

        <div class="col-2">
            <?php echo $form->field($model, 'detalleExt1') ?>
        </div>

        <div class="col-2">
            <?php echo $form->field($model, 'detalleExt2') ?>
        </div>

        <div class="col-2">
            <?php echo $form->field($model, 'existencia') ?>
        </div>

        <div class="col-2">
            <?php echo $form->field($model, 'precio') ?>
        </div>

        <div class="col-2">
            <?= $form->field($model, 'oferta')->dropDownList([
                1 => 'En oferta',
                0 => 'Sin oferta',
            ], ['prompt' => 'Seleccione']) ?>

        </div>

        <div class="col-2">
            <?php echo $form->field($model, 'proveedor') ?>
        </div>

    </div>




    <?php // echo $form->field($model, 'detalleExt2') ?>

    <?php // echo $form->field($model, 'existencia') ?>

    <?php // echo $form->field($model, 'proveedor') ?>

    <?php // echo $form->field($model, 'marca') ?>

    <?php // echo $form->field($model, 'referencia') ?>


    <?php // echo $form->field($model, 'subcategoria') ?>

    <?php // echo $form->field($model, 'precio') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
        <!-- <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary btn-lg btn-create']) ?> -->
    </div>

    <?php ActiveForm::end(); ?>

</div>