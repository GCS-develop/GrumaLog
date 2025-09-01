<?php
$this->registerCss('
    .mi-gridview {
        font-size: 12px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

    .btn-create {
        width: 300px;
    }
    
    .centrar {
        text-align: center;
    }
');

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
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
use yii\grid\GridView as BaseGrid;
use yii\data\ArrayDataProvider;

/** @var yii\web\View $this */
/** @var frontend\models\search\TraspasodetalletiendaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$fecha_actual = date("Y-m-d");
$filename = "Relacion_Traspaso_tienda_" . $fecha_actual;

// $this->title = 'Traspasodetalletiendas';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);



$resumenData = Traspasodetalletienda::resumenPorUsuarioTraspaso($idtraspaso);

$dataProviderResumen = new ArrayDataProvider([
    'allModels' => $resumenData,
    'pagination' => false,
]);

$gridColumns = [
    // 'id',
    // 'idTraspaso',
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
        'attribute' => 'Cantidad registros tiendas',
        'contentOptions' => ['data-cellvalue' => 'registros',],
        'value' => function ($model) {
            return $model->cantidad;
        },
        'format' => ['decimal', 0], // Formato decimal con 0 decimales,
        'pageSummary' => true,

    ],
    [
        'attribute' => 'Cantidad registros traspaso',
        'contentOptions' => ['data-cellvalue' => 'registros',],
        'value' => function ($model) {
            return $model->cantidadTraspasoRegistros;
        },
        'format' => ['decimal', 0], // Formato decimal con 0 decimales,
        'pageSummary' => true,

    ],
    [
        'attribute' => 'Cantidad unidades tiendas',
        'contentOptions' => ['data-cellvalue' => 'registros',],
        'value' => function ($model) {
            return $model->unidades;
        },
        'format' => ['decimal', 0], // Formato decimal con 0 decimales,
        'pageSummary' => true,

    ],
    [
        'attribute' => 'Cantidad unidades traspasos',
        'contentOptions' => ['data-cellvalue' => 'registros',],
        'value' => function ($model) {
            return $model->cantidadTraspasounidades;
        },
        'format' => ['decimal', 0], // Formato decimal con 0 decimales,
        'pageSummary' => true,

    ],
    'diferencia',
    'userTraspaso',
    [
        'attribute' => 'nombreCreo',
        'group' => true,
    ],
    'created_at',
    'nombreActualizo',
    'updated_at',
];

$resumenColumns = [
    [
        'attribute' => 'usuario',
        'label' => 'Usuario de Traspaso',
    ],
    [
        'attribute' => 'novedades_positivas',
        'label' => 'Unidades Positivas',
        'format' => ['decimal', 0],
    ],
    [
        'attribute' => 'novedades_negativas',
        'label' => 'Unidades Negativas',
        'format' => ['decimal', 0],
    ],
    [
        'attribute' => 'traspasos',
        'label' => 'Traspasos Involucrados',
    ],
    [
        'attribute' => 'documentosSiesa',
        'label' => 'ID Documento SIESA',
    ],

];

?>

<link rel="stylesheet" href="css/shared.css">

<?php
Modal::begin([
    'title' => '<h4>Traspaso detalle tienda ELIMINADOS</h4>',
    'id' => 'modaldata2',
    'size' => 'modal-xl',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData2'></div>";

Modal::end();
?>

<div class="traspasodetalletienda-index">
    <div class="row">
        <h1 class="col-lg-12 centrar"> Resumen por Usuario de Traspaso</h1>

        <div class="col-lg-12 centrar">
            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $dataProviderResumen,
                    'columns' => $resumenColumns,
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
            <?php
            echo BaseGrid::widget([
                'dataProvider' => $dataProviderResumen,
                'columns' => array_merge(
                    [
                        ['class' => 'yii\grid\SerialColumn'],
                    ],
                    $resumenColumns
                ),
            ]);
            ?>

        </div>

        <h1 class="col-lg-12 centrar"> Novedades del Traspaso (Diferencias) </h1>

        <div class="col-lg-12">
            <?php // echo $this->render('_search', ['model' => $searchModel,]); ?>
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
                    $dif = (int) $model->diferencia;
                    if ($dif > 0) {
                        return ['class' => 'fila-verde'];
                    }
                    if ($dif < 0) {
                        return ['class' => 'fila-roja'];
                    }
                    return [];
                },
                'emptyText' => 'No se encontraron registros tiendas para este traspaso.',
                'columns' => array_merge(
                    [
                        ['class' => 'kartik\grid\SerialColumn'],
                    ],

                    $gridColumns,

                ),
            ]); ?>

        </div>



    </div>
</div>