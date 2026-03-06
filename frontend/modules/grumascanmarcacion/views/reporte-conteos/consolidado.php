<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

use yii\bootstrap4\Modal;
use kartik\date\DatePicker;

/** @var yii\web\View $this */
/** @var frontend\models\search\GrumascanReporteSearch $searchModel */
/** @var yii\data\SqlDataProvider $dataProvider */
/** @var array $resumenTiendas */

$this->title = 'Consolidado Conteo vs Inventario (Tienda / SKU)';

// Modal + loader JS (tu patrón)
$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

?>

<?php
Modal::begin([
    'title' => '<h4>Detalle de conteos / marcaciones</h4>',
    'id' => 'modaldata',
    'size' => 'modal-lg',
    'options' => ['tabindex' => false],
]);
echo "<div id='modalContentData'></div>";
Modal::end();
?>

<div class="reporte-conteos-consolidado">

    <div class="text-muted small mb-2">
        Agrupa conteos <strong>terminados</strong> (estado=1) por Tienda + SKU y compara contra Inventario.
        <br>
        <strong>Optimizado:</strong> las marcaciones se consultan bajo demanda (modal), no en el grid.
    </div>

    <div class="card mb-3">
        <div class="card-body">

            <?php $form = ActiveForm::begin(['method' => 'get']); ?>

            <div class="row g-2">
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'tienda')
                        ->textInput(['placeholder' => 'Ej: 075 o NA010'])
                        ->label('Código tienda') ?>
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

                <!-- ✅ Fechas con Kartik DatePicker (como pediste) -->
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'created_from')->widget(DatePicker::className(), [
                        'language' => 'es',
                        'options' => ['placeholder' => 'Desde ...', 'autocomplete' => 'off'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true
                        ]
                    ])->label('Fecha desde (conteo)') ?>
                </div>

                <div class="col-md-3">
                    <?= $form->field($searchModel, 'created_to')->widget(DatePicker::className(), [
                        'language' => 'es',
                        'options' => ['placeholder' => 'Hasta ...', 'autocomplete' => 'off'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true
                        ]
                    ])->label('Fecha hasta (conteo)') ?>
                </div>

                <div class="col-md-3">
                    <?= $form->field($searchModel, 'dif_mode')->dropDownList([
                        'all' => 'Todos',
                        'diff' => 'Solo con diferencias (≠ 0)',
                        'nodiff' => 'Solo sin diferencias (= 0)',
                    ])->label('Filtro diferencias') ?>
                </div>

                <div class="col-md-3">
                    <?= $form->field($searchModel, 'min_abs_dif')
                        ->textInput(['type' => 'number', 'min' => 0, 'step' => 1, 'placeholder' => 'Ej: 5'])
                        ->label('Mínimo |diferencia|') ?>
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
                                <th class="text-end">% Conteo vs Inv</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resumenTiendas as $r): ?>
                                <tr>
                                    <td><?= Html::encode($r['tienda']) ?> (<?= Html::encode($r['codigoBodega']) ?>)</td>
                                    <td class="text-end"><?= number_format((float)$r['total_conteo'], 0) ?></td>
                                    <td class="text-end"><?= number_format((float)$r['total_existencia'], 0) ?></td>
                                    <td class="text-end"><?= number_format((float)$r['total_diferencia'], 0) ?></td>
                                    <td class="text-end">
                                        <?= number_format((float)$r['pct_conteo_vs_inv'], 2) ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="small text-muted mt-2">
                    % Conteo vs Inv = (Total Conteo / Total Existencia) * 100
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

            // ✅ Botón ojo de primero
            [
                'label' => '',
                'format' => 'raw',
                'value' => function ($m) use ($searchModel) {
                    if (($m['item'] ?? '') === '__SIN_ITEM__') return '<span class="text-muted">-</span>';
                    if ((float)($m['conteo_unidades'] ?? 0) <= 0) return '<span class="text-muted">-</span>';

                    $url = Url::to([
                        'marcaciones-detalle',
                        'codigoBodega' => $m['codigoBodega'] ?? '',
                        'item' => $m['item'] ?? '',
                        'idColor' => $m['idColor'] ?? '',
                        'idTalla' => $m['idTalla'] ?? '',
                        'created_from' => $searchModel->created_from ?: '',
                        'created_to' => $searchModel->created_to ?: '',
                    ]);

                    return Html::button('<i class="fa fa-eye"></i>', [
                        'value' => $url,
                        'class' => 'btn btn-default btn_update',
                        'title' => 'Ver detalle de marcaciones/conteos',
                    ]);
                },
                'contentOptions' => ['class' => 'text-nowrap', 'style' => 'width:45px;'],
            ],

            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'tienda',
                'label' => 'Tienda',
                'value' => fn($m) => ($m['tienda'] ?? '-') . ' (' . ($m['codigoBodega'] ?? '-') . ')',
            ],
            [
                'attribute' => 'item',
                'label' => 'Item',
                'value' => function ($m) {
                    if (($m['item'] ?? '') === '__SIN_ITEM__') return 'INVENTARIO SIN ITEM (HUÉRFANO)';
                    return $m['item'] ?? '-';
                },
            ],
            [
                'attribute' => 'item_descripcion',
                'label' => 'Descripción',
                'value' => fn($m) => $m['item_descripcion'] ?? '',
                'contentOptions' => ['style' => 'min-width:260px;'],
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