<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\GrumascanReporteSearch $searchModel */
/** @var yii\data\SqlDataProvider $dataProvider */
/** @var array $resumenTiendas */

$this->title = 'Consolidado Conteo vs Inventario (Tienda / SKU)';
?>

<div class="reporte-conteos-consolidado">

    <div class="text-muted small mb-2">
        Agrupa conteos terminados (estado=1) de todos los usuarios por Tienda + SKU y compara contra Inventario.
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <?php $form = ActiveForm::begin(['method' => 'get']); ?>

            <div class="row g-2">
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'tienda')->textInput(['placeholder' => 'Ej: 075 o NA010'])->label('Código tienda') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'item')->textInput(['placeholder' => 'Item'])->label('Item') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'color')->textInput(['placeholder' => 'Color'])->label('Color') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'talla')->textInput(['placeholder' => 'Talla'])->label('Talla') ?>
                </div>
            </div>

            <div class="mt-2 d-flex gap-2">
                <?= Html::submitButton('Filtrar', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Limpiar', ['consolidado'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php if (!empty($resumenTiendas)): ?>
        <div class="card mb-3">
            <div class="card-header"><strong>Totales por tienda</strong></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Tienda</th>
                                <th class="text-end">Total Conteo (unid)</th>
                                <th class="text-end">Total Existencia (unid)</th>
                                <th class="text-end">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resumenTiendas as $r): ?>
                                <tr>
                                    <td><?= Html::encode($r['tienda']) ?> (<?= Html::encode($r['codigoBodega']) ?>)</td>
                                    <td class="text-end"><?= number_format((float)$r['total_conteo'], 0) ?></td>
                                    <td class="text-end"><?= number_format((float)$r['total_existencia'], 0) ?></td>
                                    <td class="text-end"><?= number_format((float)$r['total_diferencia'], 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'responsiveWrap' => false,
        'showPageSummary' => true,
        'summary' => 'Mostrando {begin} - {end} de {totalCount} grupos',
        'tableOptions' => ['class' => 'table table-bordered table-striped'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'tienda',
                'label' => 'Tienda',
                'value' => fn($m) => ($m['tienda'] ?? '-') . ' (' . ($m['codigoBodega'] ?? '-') . ')',
            ],
            [
                'attribute' => 'item',
                'label' => 'Item',
                'value' => fn($m) => $m['item'] ?? '-',
            ],
            [
                'attribute' => 'color',
                'label' => 'Color',
                'value' => fn($m) => $m['color'] ?? 'NA',
            ],
            [
                'attribute' => 'talla',
                'label' => 'Talla',
                'value' => fn($m) => $m['talla'] ?? 'NA',
            ],
            [
                'attribute' => 'conteo_unidades',
                'label' => 'Conteo (unid)',
                'format' => ['decimal', 0],
                'pageSummary' => true,
                'value' => fn($m) => (float)($m['conteo_unidades'] ?? 0),
            ],
            [
                'attribute' => 'existencia_unidades',
                'label' => 'Inventario (unid)',
                'format' => ['decimal', 0],
                'pageSummary' => true,
                'value' => fn($m) => (float)($m['existencia_unidades'] ?? 0),
            ],
            [
                'attribute' => 'diferencia',
                'label' => 'Dif (Conteo - Inv)',
                'format' => ['decimal', 0],
                'pageSummary' => true,
                'value' => fn($m) => (float)($m['diferencia'] ?? 0),
                'contentOptions' => function ($m) {
                    $d = (float)($m['diferencia'] ?? 0);
                    if ($d > 0) return ['class' => 'text-success fw-semibold text-end'];
                    if ($d < 0) return ['class' => 'text-danger fw-semibold text-end'];
                    return ['class' => 'text-muted text-end'];
                },
            ],
        ],
    ]); ?>

</div>