<?php

use frontend\models\Traspasodetalletienda;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

use common\widgets\Alert;
use yii\bootstrap4\Modal;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var frontend\models\search\TraspasodetalletiendaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


$fecha_actual = date("Y-m-d");
$filename = "Relacion_Traspaso_Auditado_" . $fecha_actual;

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

$this->title = 'Traspaso detalle tiendas';
$this->params['breadcrumbs'][] = $this->title;



$gridColumns = [
    'serie',
    [
        'attribute' => 'consecutivoSiesa',
        'group' => true,
    ],
    'Origen',
    'Destino',
    'item',
    'talla',
    'color',
    [
        'attribute' => 'codigoBarras',
        'label' => 'Códigos de barras',
        'value' => fn($m) => $m['codigoBarras'] ?? null,
        'format' => 'ntext',
        'contentOptions' => ['style' => 'max-width:420px; white-space:normal;'],
    ],
    [
        'attribute' => 'cantidadRegistrosTienda',
        'label' => 'Cantidad registros tienda',
        'value' => fn($m) => $m['cantidadRegistrosTienda'] ?? 0,
        'format' => ['decimal', 0],
        'pageSummary' => true,
    ],
    [
        'attribute' => 'cantidadTraspasoRegistros',
        'label' => 'Cantidad registros traspaso',
        'value' => fn($m) => $m['cantidadTraspasoRegistros'] ?? 0,
        'format' => ['decimal', 0],
        'pageSummary' => true,
    ],
    [
        'attribute' => 'unidades',
        'label' => 'Cantidad unidades tienda',
        'value' => fn($m) => $m['unidades'] ?? 0,
        'format' => ['decimal', 0],
        'pageSummary' => true,
    ],
    [
        'attribute' => 'cantidadTraspasounidades',
        'label' => 'Cantidad unidades traspasos',
        'value' => fn($m) => $m['cantidadTraspasounidades'] ?? 0,
        'format' => ['decimal', 0],
        'pageSummary' => true,
    ],
    [
        'attribute' => 'diferencia',
        'label' => 'Diferencia',
        'value' => fn($m) => $m['diferencia'] ?? 0,
        'format' => ['decimal', 0],
        'pageSummary' => true,
    ],
    [
        'attribute' => 'userTraspaso',
        'value' => fn($m) => $m['userTraspaso'] ?? null,
    ],
    [
        'attribute' => 'nombreCreo',
        'group' => true,
        'value' => fn($m) => $m['nombreCreo'] ?? null,
    ],
    [
        'attribute' => 'created_at',
        'value' => fn($m) => $m['created_at'] ?? null,
        'format' => 'datetime',
    ],
    [
        'attribute' => 'nombreActualizo',
        'value' => fn($m) => $m['nombreActualizo'] ?? null,
    ],
    [
        'attribute' => 'updated_at',
        'value' => fn($m) => $m['updated_at'] ?? null,
        'format' => 'datetime',
    ],
];



?>

<link rel="stylesheet" href="css/shared.css">


<?php
Modal::begin([
    'title' => '<h4>Traspaso detalle tiendas ELIMINADOS</h4>',
    'id' => 'modaldata2',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData2'></div>";

Modal::end();
?>

<div class="traspasodetalletienda-index">

    <div class="row">

        <h1 class="col-lg-12 centrar"> Traspaso Detalle Tiendas </h1>

        <div class="col-lg-12">
            <?php echo $this->render('_search', ['model' => $searchModel,]); ?>
        </div>

        <div class="col-lg-6 derecha">
            <?php
            $url = Url::to([
                '/traspaso/traspasodetalletiendadelete/index',
                'idtraspaso' => $idtraspaso
            ]);
            ?>

            <p>
                <?= Html::button(
                    'Ver eliminados',
                    [
                        'value' => $url,
                        'class' => 'btn btn-success btn-lg btn-create',
                        'id' => 'modalButtonCreateEliminados'
                    ]
                )
                ?>
            </p>
        </div>

        <div class="col-lg-6 izquierda">
            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $dataProvider,
                    'columns' => $gridColumns,
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
        <div class="col-lg-12">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                // 'filterModel' => $searchModel,
                'showPageSummary' => true,
                'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                'options' => [
                    'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
                ],
                'rowOptions' => function ($model) {
                    $dif = (int)(is_array($model) ? ($model['diferencia'] ?? 0) : ($model->diferencia ?? 0));
                    if ($dif > 0) {
                        return ['class' => 'fila-verde'];
                    }
                    if ($dif < 0) {
                        return ['class' => 'fila-roja'];
                    }
                    return [];
                },
                'emptyText' => 'No se encontraron registros tienda para este traspaso.',
                'columns' => array_merge(
                    [
                        ['class' => 'kartik\grid\SerialColumn'],
                    ],

                    $gridColumns,

                ),
            ]); ?>

        </div>


    </div>