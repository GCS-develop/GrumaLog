<?php
use frontend\models\Unidadempaque;


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

    .titulo {
        color: black;
        font-weight: bold;
    }
');

use frontend\models\Devoluciondocumentodetalle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\DevoluciondocumentodetalleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Detalle';
$this->params['breadcrumbs'][] = ['label' => 'Devolución Documentos', 'url' => ['/devolucion/devoluciondocumento/register']];
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");
$filename = "Relacion_Devoluciones_" . $fecha_actual;
?>

<?php
$gridColumns = [
    'codigoBodegaSalida',
    'numeroDocumento',
    'codigoBarras',
    'item',
    'talla',
    'color',
    'referencia',
    'itemResumen',

    'unidadMedida',

    [
        'attribute' => 'equivalencia',
        'label' => 'Eq. UM',
        'value' => function ($model) {
            $equivalencia = 1;

            if ($model->unidadempaque) {
                $equivalencia = $model->unidadempaque->equivalencia;
            };
            return $equivalencia;
        },

    ],

    [
        'attribute' => 'cantidadDevolucion',
        'label' => 'Cant. Saldo Base',
    ],

    [
        'attribute' => 'cantidadRegistrada',
        'label' => 'Cant. Conteo Base',
    ],

    [
        'attribute' => 'diferencia',
        'label' => 'Diferencia Base',
    ],

    [
        'attribute' => 'cantidadDevolucion',
        'value' => function ($model) {
            $equivalencia = 1;

            if ($model->unidadempaque) {
                $equivalencia = $model->unidadempaque->equivalencia;
            };
            return $model->cantidadDevolucion * $equivalencia;
        },
    ],

    [
        'attribute' => 'cantidadRegistrada',
        'value' => function ($model) {
            $equivalencia = 1;

            if ($model->unidadempaque) {
                $equivalencia = $model->unidadempaque->equivalencia;
            };
            return $model->cantidadRegistrada * $equivalencia;
        },
    ],
    [
        'attribute' => 'diferencia',
        'value' => function ($model) {
            $equivalencia = 1;

            if ($model->unidadempaque) {
                $equivalencia = $model->unidadempaque->equivalencia;
            };
            return $model->diferencia * $equivalencia;
        },
        'label' => 'Diferencia',
    ],

    'fechaRegistra',
    [
        'attribute' => 'usuarioRegistra', // Nombre del atributo en el modelo
        'value' => function ($model) {
            return $model->usuarioregistra ? $model->usuarioregistra->username : ' - ';
        },
    ],

    [
        'attribute' => 'registrada', // Nombre del atributo en el modelo
        'value' => function ($model) {
            return $model->registrada == 1 ? 'SI' : 'NO';
        },
        'label' => 'Tiene Registro'
    ],
];
?>


<div class="card">
    <div class="card-body">

        <div class="row">
            <div class="col-12 titulo">
                <?= Html::encode($modeluser->username) . ' - ' . $modeluser->empleado->nombreEmpleado ?>
            </div>
        </div>
    </div>
</div>

<div class="devoluciondocumentodetalle-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

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
        'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options' => [
            'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
        ],

        'showPageSummary' => true,

        'rowOptions' => function ($model) {
        $options = [];

        if ($model->cantidadDevolucion != $model->cantidadRegistrada) {
            $options['style'] = 'background-color: #ff4d4d; color:white; font-weight: bold;'; // Puedes cambiar el color aquí
        }

        return $options;
    },

        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            /*'id',
            'idDocumento',*/
            [
                'attribute' => 'codigoBodegaSalida', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'numeroDocumento', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'codigoBarras', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'item', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'referencia', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'itemResumen', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'unidadMedida', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'equivalencia',
                'label' => 'Eq. UM',
                'value' => function ($model) {
            $equivalencia = 1;

            if ($model->unidadempaque) {
                $equivalencia = $model->unidadempaque->equivalencia;
            };
            return $equivalencia;
        },
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            //'talla',
            //'color',
    
            [
                'attribute' => 'cantidadDevolucion',
                'label' => 'Cant. Saldo Base',
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'filter' => '',
                'pageSummary' => true,
            ],

            [
                'attribute' => 'cantidadRegistrada',
                'label' => 'Cant. Conteo Base',
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'filter' => '',
                'pageSummary' => true,
            ],

            [
                'attribute' => 'diferencia',
                'label' => 'Diferencia Base',
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'pageSummary' => true,
            ],

            [
                'attribute' => 'cantidadDevolucion', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'label' => 'Cant. Saldo',
                'filter' => '',
                'value' => function ($model) {
            $equivalencia = 1;

            if ($model->unidadempaque) {
                $equivalencia = $model->unidadempaque->equivalencia;
            };
            return $model->cantidadDevolucion * $equivalencia;
        },
                'pageSummary' => true,
            ],

            [
                'attribute' => 'cantidadRegistrada', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'label' => 'Cant. Registrada',
                'filter' => '',
                'value' => function ($model) {
            $equivalencia = 1;

            if ($model->unidadempaque) {
                $equivalencia = $model->unidadempaque->equivalencia;
            };
            return $model->cantidadRegistrada * $equivalencia;
        },
                'pageSummary' => true,
            ],

            [
                'attribute' => 'diferencia', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                // 'label' => 'Cant. Registrada',
                'filter' => '',
                'value' => function ($model) {
            $equivalencia = 1;

            if ($model->unidadempaque) {
                $equivalencia = $model->unidadempaque->equivalencia;
            };
            return $model->diferencia * $equivalencia;
        },
                'pageSummary' => true,
                'label' => 'Diferencia',
            ],

            [
                'attribute' => 'fechaRegistra', // Nombre del atributo en el modelo
                //'format' => ['date', 'php:Y-m-d H:i'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model) {
            if ($model->fechaRegistra) {
                return substr($model->fechaRegistra, 0, 16);
            }

            return '-';
        }
            ],

            [
                'attribute' => 'usuarioRegistra', // Nombre del atributo en el modelo
                'value' => function ($model) {
            return $model->usuarioregistra ? $model->usuarioregistra->username : ' - ';
        },
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'registrada', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model) {
            return $model->registrada == 1 ? 'SI' : 'NO';
        },
                'filter' => ['0' => 'NO', '1' => 'SI'],
                'label' => 'Tiene Registro'
            ],

            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Devoluciondocumentodetalle $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
        ],
    ]); ?>


</div>