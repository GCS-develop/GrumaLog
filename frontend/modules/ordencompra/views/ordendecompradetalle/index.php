<?php

$this->registerCss('
    .mi-gridview {
        font-size: 11px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

    .btn-create {
        width: 300px;
    }
    
    .centrar {
        text-align: center;
    }

    .izquierda {
        text-align: left;
    }

    .derecha {
        text-align: right;
    }

    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc; /* Color y grosor de la línea */
        margin: 10px 0; /* Espacio alrededor de la línea */
    }
');

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

$this->registerJs("
    $('#modalButtonCreateP2').click(function () {
        $('#modaldata').modal('show')
            .find('#modalContentData')
            .load($(this).attr('value'));
    });
");

use frontend\models\Ordendecompradetalle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\widgets\Alert;
use yii\bootstrap4\Modal;
use common\models\OrdendecompraSIESA;
use kartik\export\ExportMenu;


/** @var yii\web\View $this */
/** @var frontend\models\search\OrdendecompradetalleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$oc = $modeloc->tipoDocumento->codigo  . '-' . $modeloc->consecutivo;
$this->title = 'Detalles de  ' . $oc;
$this->params['breadcrumbs'][] = ['label' => 'Ordenes de Compra', 'url' => ['/ordencompra/ordendecompra/index']];
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");
$filename = "Orden_compra_" . $oc . '-' . $fecha_actual;

$gridColumns = [
    [
        'class' => 'kartik\grid\SerialColumn',
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],

    [
        'attribute' => 'codigoEAN', // Nombre del atributo en el modelo
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],

    [
        'attribute' => 'item', // Nombre del atributo en el modelo
        'hAlign' => 'center', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],

    [
        'attribute' => 'descripcion', // Nombre del atributo en el modelo
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],

    [
        'attribute' => 'referencia', // Nombre del atributo en el modelo
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],

    [
        'attribute' => 'talla', // Nombre del atributo en el modelo
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],

    [
        'attribute' => 'color', // Nombre del atributo en el modelo
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],

    [
        'label' => 'Último Precio',
        'hAlign' => 'right',
        'vAlign' => 'middle',
        'value' => function ($model) {
            $resultado = OrdendecompraSIESA::obtenerUltimoPrecioVenta($model->codigoEAN, '001');
            return (!empty($resultado) && isset($resultado[0])) ? $resultado[0]['f126_precio'] : null;
        },
        'format' => ['decimal', 0],
    ],
    [
        'label' => 'Precio Anterior',
        'hAlign' => 'right',
        'vAlign' => 'middle',
        'value' => function ($model) {
            $resultado = OrdendecompraSIESA::obtenerUltimoPrecioVenta($model->codigoEAN, '001');
            return (!empty($resultado) && isset($resultado[1])) ? $resultado[1]['f126_precio'] : null;
        },
        'format' => ['decimal', 0],
    ],
    
    'cantidad',
    'fecha_activacion',

    /*'id',
    'idOrdenCompra',
    'idItem',
    'idCategoria',
    'idSubcategoria',*/
    'cantidadPedida',
    'cantidadEntrada',
    // 'cantidadPendiente',
    'fechaEntrega',
    'unidadPaquete',
    //'nroPaquetes',
    //'bodega',
    //'codigointernomovto',
    //'created_at',
    //'created_by',
    //'updated_at',
    //'updated_by',
    [
        'class' => ActionColumn::className(),
        'header' => 'Acción',
        'template' => '{printitems}',

        'buttons' => [

            'printitems' => function ($url, $model) {
                $t1 = Url::to([
                    'printitems',
                    'idordencompra' => $model->idOrdenCompra,
                    'fecha_activacion' => $model->fecha_activacion,
                    'iditem' => $model->idItem,
                    'origen' => 'item',
                    'precio_index' => 0,
                ]);
                $t2 = Url::to([
                    'printitems',
                    'idordencompra' => $model->idOrdenCompra,
                    'fecha_activacion' => $model->fecha_activacion,
                    'iditem' => $model->idItem,
                    'origen' => 'item',
                    'precio_index' => 1,
                ]);

                $btn1 = Html::button('<i class="fa fa-barcode"></i> P1', [
                    'value' => $t1,
                    'title' => 'Imprimir Sticker - Precio 1 (más reciente)',
                    'class' => 'btn btn-default btn_update',
                ]);
                $btn2 = Html::button('<i class="fa fa-barcode"></i> P2', [
                    'value' => $t2,
                    'title' => 'Imprimir Sticker - Precio 2 (anterior)',
                    'class' => 'btn btn-warning btn_update',
                ]);

                return $btn1 . ' ' . $btn2;
            },

        ],

    ],


];


?>

<?php
Modal::begin([
    'title' => '<h4>Registro datos para impresión</h4>',
    'id' => 'modaldata',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData'></div>";

Modal::end();
?>

<div class="ordendecompradetalle-index">

    <?= Alert::widget() ?>

    <div class="row">

        <div class="col-lg-6 derecha">
            <?php

            $urlP1 = Url::to([
                'printitems',
                'idordencompra' => $modeloc->id,
                'fecha_activacion' => $modeloc->fecha,
                'iditem' => null,
                'origen' => 'oc',
                'precio_index' => 0,
            ]);

            $urlP2 = Url::to([
                'printitems',
                'idordencompra' => $modeloc->id,
                'fecha_activacion' => $modeloc->fecha,
                'iditem' => null,
                'origen' => 'oc',
                'precio_index' => 1,
            ]);

            ?>

            <p>
                <?= Html::button(
                    'Imprimir Stickers P1',
                    ['value' => $urlP1, 'class' => 'btn btn-success btn-lg', 'id' => 'modalButtonCreate', 'title' => 'Imprimir con Precio 1 (más reciente)']
                ) ?>
                <?= Html::button(
                    'Imprimir Stickers P2',
                    ['value' => $urlP2, 'class' => 'btn btn-warning btn-lg', 'id' => 'modalButtonCreateP2', 'title' => 'Imprimir con Precio 2 (anterior)']
                ) ?>
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
    </div>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options' => [
            'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
        ],
        'columns' => array_merge(
            [
                ['class' => 'kartik\grid\SerialColumn'],
            ],

            $gridColumns,
        ),
    ]); ?>


</div>