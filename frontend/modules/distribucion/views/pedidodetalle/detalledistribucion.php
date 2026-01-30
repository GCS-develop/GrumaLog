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

use frontend\models\Pedidoordendecompraitem;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/** @var yii\web\View $this */
/** @var frontend\models\search\PedidoordendecompraitemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Detalle Distribución Pedido';

$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Ymd");
$filename = "DistribucionPedidos_" . $fecha_actual;

?>

<?php

$gridColumns = [
    [
        'attribute' => 'idPedido', // Nombre del atributo en el modelo
        'label'  => 'No. Pedido',
    ],

    [
        'attribute' => 'consecutivo', // Nombre del atributo en el modelo
        'label'  => 'Consecutivo',
        'hAlign' => 'center', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
        'value' => 'ordencompra.consecutivo'
    ],

    [
        'attribute' => 'idBodega',
        'label'     => 'Cod. Tienda',
        'value'     => 'bodega.codigo',
    ],

    [
        'attribute' => 'bodega_nombre',
        'label'     => 'Tienda',
        'value'     => 'bodega.nombre',
    ],

    [
        'attribute' => 'Item',
        'label'     => 'Item',
        'value'     => 'item.item',
    ],

    [
        'attribute' => 'Item',
        'label'     => 'Color',
        'value'     => 'item.color.codigo',
    ],

    [
        'attribute' => 'Item',
        'label'     => 'Talla',
        'value'     => 'item.talla.codigo',
    ],

    [
        'attribute' => 'unidades', // Nombre del atributo en el modelo
        'label' => 'Unidades', // Etiqueta de la columna
    ],

    [
        'attribute' => 'unidadesRecibidas', // Nombre del atributo en el modelo
        'label' => 'Unidades Recibidas', // Etiqueta de la columna
    ],

    [
        'attribute' => 'totalUnidades', // Nombre del atributo en el modelo
        'label' => 'No PAQ OC', // Etiqueta de la columna
        'value' => 'ordencompradetalle.nroPaquetes',
    ],

    [
        'attribute' => 'Item',
        'label'     => 'Referencia',
        'value'     => 'item.referencia',
    ],

    [
        'attribute' => 'Item',
        'label'     => 'Descripción',
        'value'     => 'item.descripcion',
    ],

    [
        'attribute' => 'Item',
        'label'     => 'U.M.',
        'value'     => 'item.unidadEmpaque',
    ],

    [
        'attribute' => 'Item',
        'label'     => 'Código Barras',
        'value'     => 'item.codigoBarras',
    ],

    [
        'attribute' => 'Proveedor',
        'label'     => 'Proveedor',
        'value'     => 'ordencompra.proveedor.razonSocial',
    ],

    [
        'attribute' => 'Item',
        'label'     => 'Categoría',
        'value'     => function ($model) {
            return $model->item && $model->item->categoria
                ? $model->item->categoria->codigoERP . ' - ' . $model->item->categoria->nombre
                : null;
        },
    ],

    [
        'attribute' => 'Item',
        'label'     => 'Subategoría',
        'value'     => function ($model) {
            return $model->item && $model->item->subcategoria
                ? $model->item->subcategoria->codigoERP . ' - ' . $model->item->subcategoria->nombre
                : null;
        },
    ],

];

?>

<?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

<div class="row">

    <div class="col-lg-6 derecha">
        <?php if (!Yii::$app->request->isAjax && !empty($searchModel->idPedido)) : ?>
            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $exportProvider,
                    'columns' => $gridColumns,
                    'target' => ExportMenu::TARGET_SELF,
                    'fontAwesome' => true,
                    'filename' => $filename,
                    'dropdownOptions' => [
                        'label' => 'Exportar',
                        'class' => 'btn btn-success btn-lg btn-create',
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
                            'iconOptions' => ['class' => 'text-success'],
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
        <?php endif; ?>
    </div>
    <?php if (!Yii::$app->request->isAjax) : ?>

        <div class="col-lg-6 izquierda">
            <?= Html::button(
                'Buscar',
                ['value' =>  "#", 'class' => 'btn btn-success btn-lg btn-create', 'id' => 'btn-toggle-search']
            )
            ?>
        </div>
    <?php endif; ?>
</div>

<?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

<div class="pedidoordendecompraitem-index">

    <div id="search-form" style="display:none; margin-bottom:15px;">
        <?= $this->render('_search_detalle', [
            'model' => $searchModel
        ]); ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options' => [
            'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
        ],
        'showPageSummary' => true,
        'rowOptions' => function ($model) {
            $options = [];

            if ($model->unidadesRecibidas < $model->unidades) {
                $options['style'] = 'background-color: #ff4d4d; color:white; font-weight: bold;'; // Puedes cambiar el color aquí
            }
            // if ($model->unidadesRecibidas == $model->unidades) {
            //     $options['style'] = 'background-color: #27a844; color:white; font-weight: bold;'; // Puedes cambiar el color aquí
            // }

            return $options;
        },
        'columns' => [
            /*[
                'class' => 'kartik\grid\SerialColumn'
            ],*/

            [
                'attribute' => 'idPedido', // Nombre del atributo en el modelo
                'label'  => 'No. Pedido',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'consecutivo', // Nombre del atributo en el modelo
                'label'  => 'Consecutivo',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => 'ordencompra.consecutivo'
            ],

            [
                'attribute' => 'idBodega',
                'label'     => 'Cod. Tienda',
                'value'     => 'bodega.codigo',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'bodega_nombre',
                'label'     => 'Tienda',
                'value'     => 'bodega.nombre',
            ],

            [
                'attribute' => 'Item',
                'label'     => 'Item',
                'value'     => 'item.item',
            ],

            [
                'attribute' => 'Item',
                'label'     => 'Color',
                'value'     => 'item.color.codigo',
            ],

            [
                'attribute' => 'Item',
                'label'     => 'Talla',
                'value'     => 'item.talla.codigo',
            ],

            [
                'attribute' => 'unidades', // Nombre del atributo en el modelo
                'label' => 'Unidades', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'unidadesRecibidas', // Nombre del atributo en el modelo
                'label' => 'UND Recibidas', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'totalUnidades', // Nombre del atributo en el modelo
                'label' => 'No PAQ OC', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'value' => 'ordencompradetalle.nroPaquetes',
                'pageSummary' => true,
            ],

            [
                'attribute' => 'Item',
                'label'     => 'Referencia',
                'value'     => 'item.referencia',
            ],

            [
                'attribute' => 'Item',
                'label'     => 'Descripción',
                'value'     => 'item.descripcion',
            ],

            [
                'attribute' => 'Item',
                'label'     => 'U.M.',
                'value'     => 'item.unidadEmpaque',
            ],

            [
                'attribute' => 'Item',
                'label'     => 'Código Barras',
                'value'     => 'item.codigoBarras',
            ],

            [
                'attribute' => 'Proveedor',
                'label'     => 'Proveedor',
                'value'     => 'ordencompra.proveedor.razonSocial',
            ],

            [
                'attribute' => 'Item',
                'label'     => 'Categoría',
                'value'     => function ($model) {
                    return $model->item && $model->item->categoria
                        ? $model->item->categoria->codigoERP . ' - ' . $model->item->categoria->nombre
                        : null;
                },
            ],

            [
                'attribute' => 'Item',
                'label'     => 'Subategoría',
                'value'     => function ($model) {
                    return $model->item && $model->item->subcategoria
                        ? $model->item->subcategoria->codigoERP . ' - ' . $model->item->subcategoria->nombre
                        : null;
                },
            ],

            //'totalUnidades',
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',

        ],
    ]); ?>


</div>

<?php
$this->registerJs("
    $('#btn-toggle-search').on('click', function(e){
        e.preventDefault();
        $('#search-form').toggle();
    });
");
?>