<?php

use frontend\models\Grumascanestado;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\GrumascanmarcacionSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="grumascanmarcacion-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">

        <div class="col-3">
            <?= $form->field($model, 'id') ?>
        </div>

        <div class="col-3">
            <?= $form->field($model, 'idbodega')->label('Bodega')->widget(Select2::class, [
                'data' => $model->getListaBodegas(),
                'options' => [
                    'placeholder' => 'Seleccionar bodega...',
                    'multiple' => true,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]) ?>
        </div>

        <div class="col-3">
            <?= $form->field($model, 'ubicacion')->label('Ubicación')->widget(Select2::class, [
                'data' => $model->getListaUbicaciones(),
                'options' => [
                    'placeholder' => 'Seleccionar ubicación...',
                    'multiple' => true,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]) ?>
        </div>

        <div class="col-3">
            <?= $form->field($model, 'seccion')->label('Sección')->widget(Select2::class, [
                'data' => $model->getListaSecciones(),
                'options' => [
                    'placeholder' => 'Seleccionar sección...',
                    'multiple' => true,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]) ?>
        </div>

        <div class="col-3">
            <?= $form->field($model, 'created_at')->textInput([
                'placeholder' => 'YYYY-MM-DD',
            ]) ?>
        </div>

        <div class="col-3">
            <?= $form->field($model, 'estado')->label('Estado')->widget(Select2::class, [
                'data' => ['3' => 'Sin conteo'] + Grumascanestado::getListaData(),
                'options' => [
                    'placeholder' => 'Seleccionar estado...',
                    'multiple' => true,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]) ?>
        </div>

    </div>

    <div class="form-group text-center mt-3">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Limpiar', ['index'], ['class' => 'btn btn-outline-secondary ms-2']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>