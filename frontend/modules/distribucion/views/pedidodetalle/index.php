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

use frontend\models\Pedidodetalle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\Search\PedidodetalleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Distribución Pedido - Bodega';
$this->params['breadcrumbs'][] = [
    'label' => 'Pedidos - Ordenes de Compra',
    'url' => [
        '/distribucion/pedidoordendecompra/index',
        'idpedido' => $modelpoc->idPedido
    ]
];
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Ymd");
$filename = "PedidoOCItemBodega_" . $modelpoc->idPedido . '_' .
    $modelpoc->ordencompra->tipoDocumento->codigo .
    $modelpoc->ordencompra->consecutivo . '_' .
    $fecha_actual;
?>

<?php
$gridColumns = [
    [
        'attribute' => 'idPedido', // Nombre del atributo en el modelo
        'label'  => 'No. Pedido',
    ],

    [
        'attribute' => 'bodega_codigo',
        'label'     => 'Cod. C.O.',
        'value'     => 'bodega.codigo',
    ],

    [
        'attribute' => 'bodega_nombre',
        'label'     => 'C.O.',
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
        'value'     => function ($model) {
            return $model->item->codigoBarras;
        },
        'format' => 'text',
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

    [
        'attribute' => 'bodega_cod_origen',
        'label'     => 'Cod. C.O. Origen',
        'value'     => 'pedidoordencompraitem.bodega.codigo',
    ],

    [
        'attribute' => 'idBodegaOrigen',
        'label'     => 'C.O. Origen',
        'value'     => 'pedidoordencompraitem.bodega.nombre',
    ],

    [
        'attribute' => 'Proveedor',
        'label'     => 'Modelo Lógistico',
        'value'     => 'ordencompra.proveedor.criterioModeloLogistico',
    ],

];
?>

<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <?= Html::label('No. Pedido', 'idpedido', ['class' => 'form-label']) ?>
                <?= Html::textInput(
                    null,                         // sin nombre, no afecta formularios
                    $modelpoc->pedido->id ?? '',
                    ['class' => 'form-control', 'readonly' => true, 'id' => 'idpedido']
                )
                ?>
            </div>

            <div class="col-md-3">
                <?= Html::label('Fecha Pedido', 'fecha', ['class' => 'form-label']) ?>
                <?= Html::textInput(
                    null,                         // sin nombre, no afecta formularios
                    $modelpoc->pedido->fecha ?? '',
                    ['class' => 'form-control', 'readonly' => true, 'id' => 'fecha']
                )
                ?>
            </div>

            <div class="col-md-3">
                <?= Html::label('No. Orden de Compra', 'fecha', ['class' => 'form-label']) ?>
                <?= Html::textInput(
                    null,                         // sin nombre, no afecta formularios
                    $modelpoc->ordencompra->cO->codigo . '-' .
                        $modelpoc->ordencompra->tipoDocumento->codigo . '-' .
                        $modelpoc->ordencompra->consecutivo ?? '',
                    ['class' => 'form-control', 'readonly' => true, 'id' => 'fecha']
                )
                ?>
            </div>

            <div class="col-md-3">
                <?= Html::label('Total Unidades', 'fecha', ['class' => 'form-label']) ?>
                <?= Html::textInput(
                    null,                         // sin nombre, no afecta formularios
                    $modelpoc->totalUnidades ?? '',
                    ['class' => 'form-control', 'readonly' => true, 'id' => 'fecha']
                )
                ?>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-4">
                <?= Html::label('Observaciones', 'observaciones', ['class' => 'form-label']) ?>
                <?= Html::textInput(
                    null,                         // sin nombre, no afecta formularios
                    $modelpoc->pedido->observaciones ?? '',
                    ['class' => 'form-control', 'readonly' => true, 'id' => 'observaciones']
                )
                ?>
            </div>

            <div class="col-md-3">
                <?= Html::label('Creado por', 'creado-por', ['class' => 'form-label']) ?>
                <?= Html::textInput(
                    null,                         // sin nombre, no afecta formularios
                    $modelpoc->pedido->creadopor->username ?? '',
                    ['class' => 'form-control', 'readonly' => true, 'id' => 'creado-por']
                )
                ?>
            </div>
        </div>
    </div>
</div>

<?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

<div class="row">

    <div class="col-lg-6 derecha">
        <?php echo ExportMenu::widget(
            [
                'dataProvider' => $dataProvider,
                'columns' => $gridColumns,
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
    </div>

    <div class="col-lg-6 izquierda">
        <?= Html::button(
            'Buscar',
            ['value' =>  "#", 'class' => 'btn btn-success btn-lg btn-create', 'id' => 'btn-toggle-search']
        )
        ?>
    </div>

</div>

<?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

<div class="pedidodetalle-index">

    <?= Alert::widget() ?>

    <div id="search-form" style="display:none; margin-bottom:15px;">
        <?= $this->render('_search', [
            'model' => $searchModel,
            'idpedido' => $modelpoc->idPedido,
            'idordencompra' => $modelpoc->idOrdenCompra
        ]); ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,

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
            if ($model->unidadesRecibidas == $model->unidades) {
                $options['style'] = 'background-color: #27a844; color:white; font-weight: bold;'; // Puedes cambiar el color aquí
            }

            return $options;
        },
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'idPedido', // Nombre del atributo en el modelo
                'label'  => 'No. Pedido',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'bodega_codigo',
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
                'label' => 'UND Pedido', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],
            [
                'attribute' => 'unidadesRecibidas',
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
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

            [
                'attribute' => 'idBodegaOrigen',
                'label'     => 'Cod. Tienda Origen',
                'value'     => 'pedidoordencompraitem.bodega.codigo',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'idBodegaOrigen',
                'label'     => 'Tienda Origen',
                'value'     => 'pedidoordencompraitem.bodega.nombre',
            ],

            [
                'attribute' => 'Proveedor',
                'label'     => 'Modelo Lógistico',
                'value'     => 'ordencompra.proveedor.criterioModeloLogistico',
            ],

            [
                'class'    => ActionColumn::className(),
                'header'   => 'Acción',
                'template' => '{delete}',
                'buttons'  => [
                    'delete' => function ($url, $model) {
                        $item   = $model->item;
                        $bodega = $model->bodega;
                        $label  = ($item ? $item->item : $model->idItem)
                            . ' / ' . ($bodega ? $bodega->codigo : $model->idBodega);
                        $recibidas = (int) $model->unidadesRecibidas;
                        if ($recibidas > 0) {
                            return Html::tag('span',
                                '<i class="fa fa-lock"></i>',
                                ['class' => 'btn btn-secondary btn-sm disabled', 'title' => 'Ya contado - no se puede eliminar']
                            );
                        }
                        return Html::a(
                            '<i class="fa fa-trash"></i>',
                            ['/distribucion/pedidodetalle/delete', 'id' => $model->id],
                            [
                                'class' => 'btn btn-danger btn-sm',
                                'title' => 'Eliminar SKU del pedido',
                                'data'  => [
                                    'confirm' => '¿Eliminar el SKU ' . $label . '? La acción quedará en el log de auditoría.',
                                    'method'  => 'post',
                                ],
                            ]
                        );
                    },
                ],
            ],

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