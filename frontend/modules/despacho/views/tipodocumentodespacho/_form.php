<?php

use frontend\models\Tipodocumento;
use frontend\models\Tipodocumentodespacho;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Tipodocumentodespacho $model */

$this->registerCss('
    .btn-create { width: 100%; }
    .centrar    { text-align: center; }
    .badge-origen {
        font-size: 14px;
        padding: 6px 14px;
        border-radius: 20px;
        display: inline-block;
        margin-bottom: 4px;
    }
');

$tiposDocumento = ArrayHelper::map(
    Tipodocumento::find()->orderBy('codigo')->all(),
    'id',
    fn($m) => $m->codigo . ' – ' . $m->nombre
);

$origenLabel = ['Cedi' => 'primary', 'Tienda' => 'warning'];
$accionLabel = ['Despachar' => 'success', 'Recibir'  => 'info'];
?>

<div class="tipodocumentodespacho-form">

    <?php $form = ActiveForm::begin([
        'id'                  => 'modal-form-tipodocumentodespacho',
        'enableAjaxValidation' => true,
    ]); ?>

    <?php if (!$model->isNewRecord || ($model->origen && $model->accion)): ?>
        <div class="text-center mb-3">
            <span class="badge badge-<?= $origenLabel[trim($model->origen)] ?? 'secondary' ?> badge-origen">
                <?= Html::encode(trim($model->origen)) ?>
            </span>
            &nbsp;→&nbsp;
            <span class="badge badge-<?= $accionLabel[trim($model->accion)] ?? 'secondary' ?> badge-origen">
                <?= Html::encode(trim($model->accion)) ?>
            </span>
        </div>
        <?= Html::activeHiddenInput($model, 'origen') ?>
        <?= Html::activeHiddenInput($model, 'accion') ?>
    <?php else: ?>
        <div class="row mb-3">
            <div class="col-6">
                <?= $form->field($model, 'origen')->dropDownList(
                    Tipodocumentodespacho::origenList(),
                    ['prompt' => 'Seleccionar origen...', 'required' => true]
                ) ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'accion')->dropDownList(
                    Tipodocumentodespacho::accionList(),
                    ['prompt' => 'Seleccionar acción...', 'required' => true]
                ) ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <?= $form->field($model, 'idTipoDocumento')->widget(Select2::class, [
            'data'          => $tiposDocumento,
            'options'       => ['placeholder' => 'Seleccionar tipo de documento...'],
            'pluginOptions' => ['allowClear' => true],
        ])->label('Tipo Documento') ?>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton(
            ($model->isNewRecord ? '<i class="fas fa-plus"></i> Agregar' : '<i class="fas fa-save"></i> Guardar'),
            ['class' => 'btn btn-success btn-lg btn-create']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
