<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use kartik\date\DatePicker;

/** @var yii\web\View $this */
/** @var frontend\models\search\GrumascanEnvioFisicoPreviewSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $totales */

$this->title = 'Preview Envío Físico (Consolidado)';
?>

<div class="envio-fisico-preview">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('warning')): ?>
        <div class="alert alert-warning"><?= Yii::$app->session->getFlash('warning') ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="text-muted small mt-2">
            Consolidado por <strong>Item + Color + Talla</strong>. Cantidad convertida a <strong>UNIDAD</strong> usando equivalencia de <strong>unidadempaque</strong>.
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => ['preview'],
            ]); ?>

            <div class="row g-2">
                <div class="col-2">
                    <?= $form->field($searchModel, 'codigoBodega')->textInput([
                        'placeholder' => 'Ej: 210',
                        'autocomplete' => 'off',
                    ]) ?>
                </div>

                <div class="col-2">
                    <?= $form->field($searchModel, 'fechaDesde')->widget(DatePicker::class, [
                        'language' => 'es',
                        'options' => [
                            'placeholder' => 'Desde ...',
                            'autocomplete' => 'off'
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ],
                    ]) ?>
                </div>

                <div class="col-2">
                    <?= $form->field($searchModel, 'fechaHasta')->widget(DatePicker::class, [
                        'language' => 'es',
                        'options' => [
                            'placeholder' => 'Hasta ...',
                            'autocomplete' => 'off'
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ],
                    ]) ?>
                </div>

                <div class="col-2 d-flex align-items-end">
                    <?= Html::submitButton('Previsualizar', ['class' => 'btn btn-primary w-100']) ?>
                </div>

                <div class="col-2 d-flex align-items-end">
                    <?php
                    $lineas = (int)($totales['lineas'] ?? 0);
                    $disabled = $lineas === 0;
                    ?>
                    <?= Html::button('Terminar', [
                        'type' => 'button',
                        'class' => 'btn btn-success w-100',
                        'disabled' => $disabled,
                        // Bootstrap 4 (NO bs-*)
                        'data-toggle' => 'modal',
                        'data-target' => '#modalConsecutivo',
                        'title' => $disabled ? 'Primero previsualiza y verifica que haya datos.' : 'Capturar consecutivo para continuar.',
                    ]) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>


        </div>
    </div>

    <?php if ($searchModel->validate()): ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-muted small">Bodega (Código)</div>
                        <div><strong><?= Html::encode($searchModel->codigoBodega) ?></strong></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Líneas</div>
                        <div><strong><?= (int)($totales['lineas'] ?? 0) ?></strong></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Total Paquetes</div>
                        <div><strong><?= (int)($totales['total_paquetes'] ?? 0) ?></strong></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Total unidades</div>
                        <div><strong><?= (int)($totales['total_unidades'] ?? 0) ?></strong></div>
                    </div>
                </div>

                <?php if ((int)($totales['lineas'] ?? 0) === 0): ?>
                    <div class="alert alert-warning mt-3 mb-0">
                        No se encontraron conteos <strong>terminados</strong> (estado=1) para la bodega y rango de fechas seleccionados.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-striped table-bordered table-sm'],
                'columns' => [
                    ['attribute' => 'item', 'label' => 'Item'],
                    ['attribute' => 'color', 'label' => 'Color'],
                    ['attribute' => 'talla', 'label' => 'Talla'],
                    [
                        'attribute' => 'cantidad_paquetes',
                        'label' => 'Cantidad (PAQUETES)',
                        'contentOptions' => ['style' => 'text-align:right;'],
                        'headerOptions' => ['style' => 'text-align:right;'],
                    ],
                    [
                        'attribute' => 'cantidad_unidad',
                        'label' => 'Cantidad (UNIDAD)',
                        'contentOptions' => ['style' => 'text-align:right;'],
                        'headerOptions' => ['style' => 'text-align:right;'],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>

<?=
$this->render('_modal_consecutivo', [
    'searchModel' => $searchModel,
    'totales' => $totales,
]);
?>