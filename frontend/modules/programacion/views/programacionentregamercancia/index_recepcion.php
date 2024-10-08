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

use frontend\models\Agendaentregamercancia;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

use common\models\ProcedimientosGenerales;
use frontend\models\Transportadora;

use common\widgets\Alert;
use yii\bootstrap4\Modal;

use kartik\icons\Icon;

Icon::map($this, Icon::FAS);

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendaentregamercanciaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Recepción Mercancía';
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");
$filename = "Relacion_RecepcionMercancia_" . $fecha_actual;

?>

<?php
Modal::begin([
    'title' => '<h4>Datos Básicos Recepción Mercancía</h4>',
    'id' => 'modaldata',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData'></div>";

Modal::end();
?>

<?php
$gridColumns = [
    [
        'attribute' => 'id', // Nombre del atributo en el modelo
    ],
    [
        'attribute' => 'fechaCita', // Nombre del atributo en el modelo
        //'label' => 'Desde', // Etiqueta de la columna
        'format' => ['date', 'php:Y-m-d'],
    ],
    [
        'attribute' => 'horaCita', // Nombre del atributo en el modelo
        //'label' => 'Desde', // Etiqueta de la columna
        'format' => ['date', 'php:H:i'],
    ],
    [
        'attribute' => 'nit', // Nombre del atributo en el modelo
        'label' => 'Nit', // Etiqueta de la columna
        //'format' => ['decimal', 0], // Formato decimal con 0 decimales
    ],
    [
        'attribute' => 'razonSocial', // Nombre del atributo en el modelo
        'label' => 'Razón Social', // Etiqueta de la columna
    ],
    [
        'attribute' => 'nombreCategoria', // Nombre del atributo en el modelo
        'label' => 'Categoría', // Etiqueta de la columna
    ],
    [
        'attribute' => 'subcategorias', // Nombre del atributo en el modelo
        'label' => 'SubCategoría', // Etiqueta de la columna
    ],
    [
        'attribute' => 'codigoTipoDocumento',
        'label' => 'Serie',
    ],
    [
        'attribute' => 'numeroOrdenCompra', // Nombre del atributo en el modelo
        'label' => 'Número Orden', // Etiqueta de la columna
    ],
    [
        'attribute' => 'unidades', // Nombre del atributo en el modelo
        'label' => 'UND Agendadas', // Etiqueta de la columna
    ],

    /*[
        'attribute' => 'unidades empaque', // Nombre del atributo en el modelo
        'label' => 'Und empaque', // Etiqueta de la columna
    ],*/

    [
        'attribute' => 'unidadesCumplidas', // Nombre del atributo en el modelo
        'label' => 'UND Cumplidas', // Etiqueta de la columna
    ],
    [
        'attribute' => 'idEstado', // Nombre del atributo en el modelo
        'value' => function ($model) {
            return $model->estado->nombre;
        }
    ],
    [
        'attribute' => 'criterioMercancia', // Nombre del atributo en el modelo
        'label' => 'Tipo Proveedor', // Etiqueta de la columna
    ],
    [
        'attribute' => 'modeloLogistico', // Nombre del atributo en el modelo
        'label' => 'Modelo Logístico', // Etiqueta de la columna
    ],
    [
        'attribute' => 'observacion', // Nombre del atributo en el modelo
        'label' => 'Observaciones', // Etiqueta de la columna
    ],

    /*[
        'attribute' => 'codigoCentroOperacion',
        'label' => 'Almacen',
    ],
    [
        'attribute' => 'numeroCajas', // Nombre del atributo en el modelo
        'label' => 'Cajas', // Etiqueta de la columna
    ],
    [
        'attribute' => 'idTransportadora', // Nombre del atributo en el modelo
        'label' => 'Transportadora', // Etiqueta de la columna
        'value' => function ($model){
            $nombre = '-';
            if ($model->idTransportadora){
                $nombre = $model->transportadora->nombre;
            }
            return $nombre;
        }
    ],
    [
        'attribute' => 'contacto', // Nombre del atributo en el modelo
        //'label' => 'Contacto', // Etiqueta de la columna
    ],  
    [
        'attribute' => 'fechaContacto', // Nombre del atributo en el modelo
        //'label' => 'Fecha Contacto', // Etiqueta de la columna
        'format' => ['date', 'php:Y-m-d'],
    ],

    //'numeroGuia',
    //'observacion',
    */
];
?>

<div class="agendaentregamercancia-index">

    <?= Alert::widget() ?>

    <?php echo $this->render('_search_recepcion', ['model' => $searchModel, 'menu' => $menu]); ?>

    <div class="row">

        <div class="col-lg-12 centrar">
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

    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
    
        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options' => [
            'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
        ],

        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
    
            [
                'attribute' => 'id', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'codigoCentroOperacion',
                'label' => 'Almacen',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'codigoTipoDocumento',
                'label' => 'Serie',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'numeroOrdenCompra', // Nombre del atributo en el modelo
                'label' => 'Número Orden', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
    
            ],
            [
                'attribute' => 'nit', // Nombre del atributo en el modelo
                'label' => 'Nit', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],
            [
                'attribute' => 'razonSocial', // Nombre del atributo en el modelo
                'label' => 'Razón Social', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'fechaCita', // Nombre del atributo en el modelo
                //'label' => 'Desde', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d H:i'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'numeroCajas', // Nombre del atributo en el modelo
                'label' => 'Cajas', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
    
            ],

            [
                'attribute' => 'idTransportadora', // Nombre del atributo en el modelo
                'label' => 'Transportadora', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model) {
            $nombre = '-';
            if ($model->idTransportadora) {
                $nombre = $model->transportadora->nombre;
            }
            return $nombre;
        }
            ],

            [
                'attribute' => 'contacto', // Nombre del atributo en el modelo
                //'label' => 'Contacto', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'modeloLogistico', // Nombre del atributo en el modelo
                'label' => 'Modelo Logístico', // Etiqueta de la columna
            ],

            [
                'attribute' => 'fechaContacto', // Nombre del atributo en el modelo
                //'label' => 'Fecha Contacto', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'nroPaquetes', // Nombre del atributo en el modelo
                'label' => 'Und. Empaque', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],
            [
                'attribute' => 'subcategorias', // Nombre del atributo en el modelo
                'label' => 'SubCategoría', // Etiqueta de la columna
            ],
            [
                'attribute' => 'unidades', // Nombre del atributo en el modelo
                'label' => 'UND Agendadas', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],

            [
                'attribute' => 'unidadesCumplidas', // Nombre del atributo en el modelo
                'label' => 'UND Cumplidas', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],

            //'numeroGuia',
            //'observacion',
            [
                'attribute' => 'idEstado', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model) {
            return $model->estado->nombre;
        }
            ],

            [
                'class' => ActionColumn::className(),
                'header' => 'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{receive}',

                'buttons' => [

                    'receive' => function ($url, $model) {
                $t = Url::to([
                    'receive',
                    'idagendaentrega' => $model->id
                ]);

                return Html::button('<i class="fa fa-edit"></i>', [
                    'value' => $t,
                    'title' => 'Actualizar Estado - Unidades Cumplidas En Puerta',
                    'class' => 'btn btn-default btn_update',
                ]);
            },

                ],

            ],
        ],
    ]); ?>


</div>