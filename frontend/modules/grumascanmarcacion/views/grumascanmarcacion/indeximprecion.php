<?php

use common\widgets\Alert;
use frontend\models\Bodegas;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/** @var $this yii\web\View */
/** @var $model frontend\models\forms\GrumascanMarcacionPrintForm */
/** @var $printers array */

$this->title = 'Imprimir stickers de marcación';
$this->params['breadcrumbs'][] = $this->title;

$printerItems = $printers; // ya viene listo: [id => label]

?>

<div class="marcacion-print-index">

    <?= Alert::widget() ?>

    <?php $form = ActiveForm::begin([
        'action' => ['print'],
        'method' => 'post',
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'printer_id')->dropDownList(
                $printerItems,
                ['prompt' => 'Seleccione una impresora...']
            ) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'cantidad')->input('number', [
                'min' => 1,
                'max' => 500,
                'value' => $model->cantidad ?: 1,
            ]) ?>
        </div>
    </div>

    <hr>

    <!-- <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'idbodega')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => ' Bodega Origen ... ',
                    'id' => 'id-bodega-origen',
                    'required' => true
                ]
            )
            ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'ubicacion')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'seccion')->textInput(['maxlength' => true]) ?>
        </div>
    </div> -->

    <div class="form-group" style="margin-top: 10px;">
        <?= Html::submitButton('Imprimir', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <div style="margin-top: 15px;">
        <p><strong>Formato impreso:</strong></p>
        <pre style="background:#f7f7f7; padding:10px; border:1px solid #ddd;">
| --------------------------------------------------------------|
|   codigo de barras (id grumascanMarcacion)                     |
| Unidades :__________                                           |
| Usuario  :__________                                           |
| --------------------------------------------------------------|
        </pre>
    </div>

</div>

<hr class="my-4">

<div class="marcacion-reprint-index">
    <h4><i class="fas fa-redo"></i> Re-imprimir stickers existentes</h4>
    <p class="text-muted">Ingresa el rango de IDs de las marcaciones que quieres volver a imprimir. No crea registros nuevos.</p>

    <form method="post" action="<?= \yii\helpers\Url::to(['reprint']) ?>">
        <?= \yii\helpers\Html::hiddenInput(\Yii::$app->request->csrfParam, \Yii::$app->request->csrfToken) ?>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Impresora</label>
                    <select name="printer_id_reprint" class="form-control" required>
                        <option value="">Seleccione una impresora...</option>
                        <?php foreach ($printerItems as $pid => $plabel): ?>
                            <option value="<?= $pid ?>"><?= \yii\helpers\Html::encode($plabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>ID desde</label>
                    <input type="number" name="desde_reprint" class="form-control" min="1" placeholder="ej. 1001" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>ID hasta</label>
                    <input type="number" name="hasta_reprint" class="form-control" min="1" placeholder="ej. 1050" required>
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="form-group w-100">
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-print"></i> Re-imprimir
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>