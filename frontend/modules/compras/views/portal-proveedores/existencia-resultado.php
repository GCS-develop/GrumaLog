<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\export\ExportMenu;

$this->title = 'Consulta de Existencias por Proveedor';

$this->registerCss('
    .mi-gridview { font-size: 11px; }
    .btn-create  { width: 300px; }
    .centrar     { text-align: center; }
    .izquierda   { text-align: left; }
    .derecha     { text-align: right; }
    .horizontal-line { border: none; border-top: 1px solid #ccc; margin: 10px 0; }
    .titulonombre { color: black; font-weight: bold; font-size: 20px; }
');

$fecha_actual  = date('Y-m-d');
$filename      = 'Relacion_Existencias_' . $codigo . '_' . $fecha_actual;
$totalUnidades = array_sum(array_column($dataProvider->allModels, 'Existencia'));

$gridColumns = [
    'nombreCentroOperacion', 'codigobarra', 'item', 'descripcion', 'color', 'talla', 'Existencia',
    'proveedor', 'nombreproveedor', 'marca', 'nombremarca', 'referencia',
    'categoria', 'nombrecategoria', 'subcategoria', 'nombresubcategoria',
];
?>

<div class="row">
    <div class="col-lg-6 titulonombre izquierda">
        <?= Html::encode($razonSocial) ?>
    </div>
    <div class="col-lg-6 titulonombre centrar">
        <p><strong>Unidades:</strong> <?= Yii::$app->formatter->asDecimal($totalUnidades, 0) ?></p>
    </div>
</div>

<?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

<div class="row">
    <div class="col-lg-6 derecha">
        <?= Html::a('Regresar', ['existencia-general'], ['class' => 'btn btn-success btn-lg btn-create']) ?>
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
    'dataProvider'    => $dataProvider,
    'filterModel'     => $searchModel,
    'summary'         => 'Mostrando {begin} - {end} de {totalCount} resultados',
    'formatter'       => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
    'options'         => ['class' => 'mi-gridview'],
    'showPageSummary' => true,
    'columns'         => [
        'nombreCentroOperacion', 'codigobarra', 'item', 'descripcion', 'color', 'talla',
        ['attribute' => 'Existencia', 'hAlign' => 'right', 'vAlign' => 'middle', 'format' => ['decimal', 0], 'pageSummary' => true],
        'proveedor', 'nombreproveedor', 'marca', 'nombremarca', 'referencia',
        'categoria', 'nombrecategoria', 'subcategoria', 'nombresubcategoria',
    ],
]); ?>
