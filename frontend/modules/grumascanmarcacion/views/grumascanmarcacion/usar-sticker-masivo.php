<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use frontend\models\Bodegas;

/** @var $this yii\web\View */
/** @var $model frontend\models\forms\GrumascanMarcacionBulkUseForm */

$this->title = 'Asignar marcación masiva (rango)';
$this->params['breadcrumbs'][] = ['label' => 'Marcación', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="grumascan-usar-sticker-masivo">

    <h3><?= Html::encode($this->title) ?></h3>

    <?php $form = ActiveForm::begin([
        'method' => 'post',
    ]); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'desde')->textInput(['type' => 'number', 'min' => 1]) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'hasta')->textInput(['type' => 'number', 'min' => 1]) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'idbodega')->dropDownList(
                Bodegas::getListaData(),
                ['prompt' => 'Seleccione bodega...']
            ) ?>
        </div>

        <div class="col-md-3" style="padding-top: 25px;">
            <?= $form->field($model, 'sobrescribir')->checkbox([
                'label' => 'Sobrescribir bodega, sección y ubicación si ya están asignadas'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'ubicacion')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'seccion')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="form-group" style="margin-top:15px;">
        <?= Html::submitButton('Asignar rango', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Volver', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>