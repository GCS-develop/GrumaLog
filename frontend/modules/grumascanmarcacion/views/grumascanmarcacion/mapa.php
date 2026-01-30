<?php

use frontend\models\Bodegas;
use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\export\ExportMenu;

/** @var yii\web\View $this */
/** @var frontend\models\search\VwGrumascanMapaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Mapa de marcación (rangos por bodega)';
$this->params['breadcrumbs'][] = $this->title;

$columns = [
    ['class' => 'yii\grid\SerialColumn'],

    [
        'attribute' => 'idbodega',
        'label' => 'Bodega',
        'filter' => Bodegas::getListaData(),
        'contentOptions' => ['style' => 'width:90px;'],
        'value' => fn($model) => $model->bodega ? $model->bodega->nombre : 'sin bodega',
    ],
    [
        'attribute' => 'ubicacion',
        'label' => 'Ubicación',
        'filter' => true,
    ],
    [
        'attribute' => 'seccion',
        'label' => 'Sección',
        'filter' => true,
    ],
    [
        'attribute' => 'desde',
        'label' => 'Desde',
        'format' => ['decimal', 0],
        'contentOptions' => ['style' => 'width:120px; text-align:right;'],
    ],
    [
        'attribute' => 'hasta',
        'label' => 'Hasta',
        'format' => ['decimal', 0],
        'contentOptions' => ['style' => 'width:120px; text-align:right;'],
    ],
    [
        'attribute' => 'cantidad',
        'label' => 'Cantidad',
        'format' => ['decimal', 0],
        'contentOptions' => ['style' => 'width:120px; text-align:right;'],
    ],
];


$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

$fecha_actual = date("Y-m-d");
$filename = "Relacion_Mapa_" . $fecha_actual;

?>
<div class="vw-grumascan-mapa-marcacion">
    <div class="row">
        <div class="col-12 text-center">
            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $dataProvider,
                    'columns' => $columns,
                    'fontAwesome' => true,
                    'filename' => $filename,
                    'dropdownOptions' => [
                        'label' => 'Exportar',
                        'class' => 'btn btn-primary btn-lg btn-create',
                    ],
                    'exportConfig' => [
                        ExportMenu::FORMAT_TEXT => false,
                        ExportMenu::FORMAT_HTML => false,
                        ExportMenu::FORMAT_EXCEL => false,
                        ExportMenu::FORMAT_PDF => false,
                        ExportMenu::FORMAT_CSV => false,
                        ExportMenu::FORMAT_EXCEL_X => [
                            'label' => 'Excel 2007+',
                            'icon' => 'file-excel-o',
                            'iconOptions' => ['class' => 'text-success btn-create'],
                            'linkOptions' => [],
                            'options' => ['title' => 'Microsoft Excel 2007+ (xlsx)'],
                            'alertMsg' => 'Se va a generar un archivo en formato EXCEL 2007+ (xlsx).',
                            'mime' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'extension' => 'xlsx',
                            'writer' => ExportMenu::FORMAT_EXCEL_X
                        ],
                    ]
                ]
            );
            ?>

        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'responsiveWrap' => false,
        'hover' => true,
        'striped' => true,
        'bordered' => true,

        'summary' => 'Mostrando {begin}-{end} de {totalCount}',
        'columns' => $columns,
    ]); ?>

</div>