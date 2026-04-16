<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\export\ExportMenu;

$this->title = 'Consulta de Ventas por Proveedor';

$this->registerCss('
    .mi-gridview { font-size: 11px; }
    .btn-create  { width: 300px; }
    .centrar     { text-align: center; }
    .izquierda   { text-align: left; }
    .derecha     { text-align: right; }
    .horizontal-line { border: none; border-top: 1px solid #ccc; margin: 10px 0; }
    .titulonombre { color: black; font-weight: bold; font-size: 20px; }
');

$filename = 'Relacion_VentasPOS_' . $codigo . '_' . $fechaDesde . '_' . $fechaHasta;

$gridColumns = [
    'fecha', 'item', 'codigobarra', 'descripcion', 'color', 'talla', 'referencia',
    ['attribute' => 'unidadmedida', 'label' => 'Unidad'],
    'unidades', 'precio_aplicado', 'total',
];
?>

<div class="row">
    <div class="col-lg-12 titulonombre">
        <?= Html::encode($razonSocial . ' (' . $fechaDesde . ' - ' . $fechaHasta . ')') ?>
    </div>
</div>

<?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

<div class="row">
    <div class="col-lg-6 derecha">
        <?= Html::a('Regresar', ['ventas-diario'], ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <div class="col-lg-6 izquierda">
        <?= ExportMenu::widget([
            'dataProvider'    => $dataProvider,
            'columns'         => $gridColumns,
            'batchSize'       => 500,
            'fontAwesome'     => true,
            'filename'        => $filename,
            'dropdownOptions' => ['label' => 'Exportar', 'class' => 'btn btn-success btn-lg btn-create'],
            'exportConfig'    => [
                ExportMenu::FORMAT_TEXT    => false,
                ExportMenu::FORMAT_HTML    => false,
                ExportMenu::FORMAT_EXCEL   => false,
                ExportMenu::FORMAT_PDF     => false,
                ExportMenu::FORMAT_CSV     => false,
                ExportMenu::FORMAT_EXCEL_X => [
                    'label'       => 'Excel 2007+',
                    'icon'        => 'file-excel-o',
                    'iconOptions' => ['class' => 'text-success'],
                    'options'     => ['title' => 'Microsoft Excel 2007+ (xlsx)'],
                    'alertMsg'    => 'Se va a generar un archivo en formato EXCEL 2007+ (xlsx).',
                    'mime'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'extension'   => 'xlsx',
                    'writer'      => ExportMenu::FORMAT_EXCEL_X,
                ],
            ],
        ]) ?>
    </div>
</div>

<?= GridView::widget([
    'dataProvider'   => $dataProvider,
    'filterModel'    => $searchModel,
    'summary'        => 'Mostrando {begin} - {end} de {totalCount} resultados',
    'formatter'      => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
    'options'        => ['class' => 'mi-gridview'],
    'showPageSummary' => true,
    'columns'        => [
        'fecha', 'item', 'codigobarra', 'descripcion', 'color', 'talla', 'referencia',
        'nombreCentroOperacion',
        ['attribute' => 'unidadmedida',    'label'  => 'Unidad'],
        ['attribute' => 'unidades',        'hAlign' => 'right', 'vAlign' => 'middle', 'format' => ['decimal', 0], 'pageSummary' => true],
        ['attribute' => 'precio_aplicado', 'hAlign' => 'right', 'vAlign' => 'middle', 'format' => ['decimal', 0], 'pageSummary' => false],
        ['attribute' => 'total',           'hAlign' => 'right', 'vAlign' => 'middle', 'format' => ['decimal', 0], 'pageSummary' => true],
    ],
]); ?>
