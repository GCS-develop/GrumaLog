<?php

use frontend\models\Bodegas;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\InventarioSearch $model */
?>

<div class="card mb-3">
    <div class="card-body py-3">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
        ]); ?>

        <div class="row g-2">
            <div class="col-md-4">
                <?= $form->field($model, 'codigoBodega')->label('Bodega(s)')->widget(Select2::class, [
                    'data'    => Bodegas::getListaDataCodigo(),
                    'options' => [
                        'multiple'    => true,
                        'placeholder' => 'Todas las bodegas',
                    ],
                    'pluginOptions' => [
                        'allowClear'    => true,
                        'closeOnSelect' => false,
                    ],
                ]) ?>
            </div>

            <div class="col-md-2">
                <?= $form->field($model, 'item')->label('Item') ?>
            </div>

            <div class="col-md-2">
                <?= $form->field($model, 'codigoBarras')->label('Cód. Barras') ?>
            </div>

            <div class="col-md-2">
                <?= $form->field($model, 'color')->label('Color') ?>
            </div>

            <div class="col-md-2">
                <?= $form->field($model, 'talla')->label('Talla') ?>
            </div>
        </div>

        <div class="d-flex gap-2 mt-2">
            <?= Html::submitButton('<i class="fas fa-search"></i> Filtrar', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-times"></i> Limpiar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
