<?php

use frontend\models\Traspaso;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use frontend\models\Estadotraspaso;
use frontend\models\Bodegas;
use frontend\models\TipoDocumento;
use frontend\models\Traspasouserbodega;
use kartik\export\ExportMenu;

use common\widgets\Alert;
use yii\bootstrap4\Modal;

/** @var yii\web\View $this */
/** @var frontend\models\searchTraspasoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Traspasos';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

$fecha_actual = date("Y-m-d");
$filename = "Relacion_Traspaso_" . $fecha_actual;

$gridColumns = [
    [
        'attribute' => 'serie',
        'contentOptions' => ['data-cellvalue' => 'serie'],
        'value' => function ($model) {
            return $model->tipodocumento ? $model->tipodocumento->codigo : null;
        },
    ],
    [
        'attribute' => 'consecutivo',
        'contentOptions' => ['data-cellvalue' => 'consecutivo'],
        'value' => function ($model) {
            return $model->codigoerp ? $model->codigoerp->f350_consec_docto : $model->consecutivo;
        },
    ],
    [
        'attribute' => 'codeBodegaOrigen',
        'value' => function ($model) {
            return $model->bodegaOrigen->codigo;
        },
    ],
    [
        'attribute' => 'idBodegaOrigen',
        'value' => function ($model) {
            return $model->bodegaOrigen->nombre;
        },
        'filter' => Traspasouserbodega::getListaData(),
        'contentOptions' => ['data-cellvalue' => 'idBodegaOrigen'],
    ],
    [
        'attribute' => 'codeBodegaDestino',
        'value' => function ($model) {
            return $model->bodegaDestino->codigo;
        },
    ],
    [
        'attribute' => 'idBodegaDestino',
        'value' => function ($model) {
            return $model->bodegaDestino->nombre;
        },
    ],
    [
        'attribute' => 'numeroCajas',
        'contentOptions' => ['data-cellvalue' => 'numeroCajas',],
    ],
    [
        'attribute' => 'caja',
        'value' => function ($model) {
            return 'PKM';
        },
    ],
    [
        'attribute' => 'idEstado',
        'filter' => Estadotraspaso::getListaData(),
        'value' => function ($model) {
            return $model->estado ? $model->estado->nombre : null;
        },
        'contentOptions' => ['data-cellvalue' => 'idEstado',],
    ],
    [
        'attribute' => 'updated_at',
        'value' => function ($model) {
            $dateTimeParts = explode(' ', $model->created_at);
            return $dateTimeParts[0];
        },
    ],
    [
        'attribute' => 'horaInicio',
        'value' => function ($model) {
            $dateTimeParts = explode(' ', $model->created_at);
            return $dateTimeParts[1];
        },
    ],
    [
        'attribute' => 'fechaUltimoRegistro',
        'value' => function ($model) {
            if ($model->traspasodetalle !== null) {
                $dateTimeParts = explode(' ', $model->traspasodetalle->updated_at);
                return $dateTimeParts[0];
            } else {
                return 'No tiene items asignados';
            }
        },
    ],
    [
        'attribute' => 'horaUltimoRegistro',
        'value' => function ($model) {
            if ($model->traspasodetalle !== null) {
                $dateTimeParts = explode(' ', $model->traspasodetalle->updated_at);
                return $dateTimeParts[1];
            } else {
                return 'No tiene items asignados';
            }
        },
    ],
    [
        'attribute' => 'fechaFin',
        'value' => function ($model) {
            $dateTimeParts = explode(' ', $model->updated_at);
            return $dateTimeParts[0];
        },
    ],
    [
        'attribute' => 'horaFin',
        'value' => function ($model) {
            $dateTimeParts = explode(' ', $model->updated_at);
            return $dateTimeParts[1];
        },
    ],
    [
        'attribute' => 'Und.Empaque',
        'contentOptions' => ['data-cellvalue' => 'Und.Empaque',],
        'value' => function ($model) {
            return $model->totalRegistrosPaquetes;
        },
    ],
    [
        'attribute' => 'Und.Traspaso',
        'contentOptions' => ['data-cellvalue' => 'Und.Traspaso',],
        'value' => function ($model) {
            return $model->totalUnidades;
        },

    ],

    [
        'attribute' => 'created_by',
        'label' => 'Usuario',
        'value' => function ($model) {
            return $model->createdByUser ? $model->createdByUser->username : '(sin usuario)';
        },
        'contentOptions' => ['data-cellvalue' => 'Usuario',],
    ],
];

?>

<link rel="stylesheet" href="css/shared.css">

<?php
Modal::begin([
    'title' => '<h4>Datos básicos bodegas por usuario</h4>',
    'id' => 'modaldata',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData'></div>";

Modal::end();
?>

<div class="traspaso-index">

    <?php echo $this->render('_search', ['model' => $searchModel,]); ?>

    <?= Alert::widget() ?>

    <div class="row">
        <div class="col-lg-6 derecha">

            <?php $url = Url::to(['create']); ?>

            <p>
                <?= Html::button(
                    'Registrar',
                    ['value' => $url, 'class' => 'btn btn-success btn-lg btn-create', 'id' => 'modalButtonCreate']
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
    </div>


</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    // 'filterModel' => $searchModel,
    'showPageSummary' => true,
    'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
    'options' => [
        'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
    ],

    'columns' => [
        ['class' => 'kartik\grid\SerialColumn'],
        'id',
        // 'idTipoDocumento',
        [
            'attribute' => 'idTipoDocumento',
            'filter' => TipoDocumento::getListaDataCodigo(),
            'contentOptions' => ['data-cellvalue' => 'serie'],
            'value' => function ($model) {
    return $model->tipodocumento ? $model->tipodocumento->codigo : 'Sin serie';
},
        ],
    
        [
            'attribute' => 'consecutivo',
            'contentOptions' => ['data-cellvalue' => 'consecutivo'],
            'value' => function ($model) {
    return $model->codigoerp ? $model->codigoerp->f350_consec_docto : $model->consecutivo;
},
        ],
        // 'idCentroOperacion',
        // 'idBodegaOrigen',
        // 'idBodegaDestino',
        [
            'attribute' => 'idBodegaOrigen',
            'filter' => Bodegas::getListaData(),
            'contentOptions' => ['data-cellvalue' => 'idBodegaOrigen', 'class' => 'hidden-xs'],
            'value' => function ($model) {
    return $model->bodegaOrigen->nombre;
},
        ],
        [
            'attribute' => 'idBodegaDestino',
            'filter' => Bodegas::getListaData(),
            'contentOptions' => ['data-cellvalue' => 'idBodegaDestino', 'class' => 'hidden-xs'],
            'value' => function ($model) {
    return $model->bodegaDestino->codigo . ' ' . $model->bodegaDestino->nombre;
},
        ],
        [
            'attribute' => 'numeroCajas',
            'format' => ['decimal', 0], // Formato decimal con 0 decimales,
            'pageSummary' => true,
        ],

        [
            'attribute' => 'Cantidad registros',
            'contentOptions' => ['data-cellvalue' => 'registros',],
            'value' => function ($model) {
    return $model->AllRecords;
},
            'format' => ['decimal', 0], // Formato decimal con 0 decimales,
            'pageSummary' => true,

        ],
        [
            'label' => 'unidades',
            'value' => function ($model) {
    return $model->TotalUnidades;
},
            'format' => ['decimal', 0], // Formato decimal con 0 decimales,
            'pageSummary' => true,

        ],

        // 'idEstado',
        [
            'attribute' => 'idEstado',
            'filter' => Estadotraspaso::getListaData(),
            'value' => function ($model) {
    return $model->estado ? $model->estado->nombre : null;
},
            'contentOptions' => ['data-cellvalue' => 'idEstado',],
        ],
        // 'idUltimoItem',
        'created_at',
        [
            'attribute' => 'created_by',
            'label' => 'Creador',
            'contentOptions' => ['data-cellvalue' => 'Usuario'],
            'value' => function ($model) {
    return $model->created_by . ' - ' . ($model->createdByUser ? $model->createdByUser->username : '(sin usuario)');
},
        ],


        'updated_at',
        [
            'attribute' => 'updated_by',
            'label' => 'Ultimo usuario',
            'contentOptions' => ['data-cellvalue' => 'Usuario'],
            'value' => function ($model) {
    return $model->updated_by . ' - ' . $model->updatedByUser->username;
},
        ],
        [
            'class' => ActionColumn::className(),
            'header' => 'Acción',
            'headerOptions' => ['width' => '10%'],
            'template' => ' {view} {update} {anular} {factura}  ',
            'buttons' => [

                'view' => function ($url, $model) {
        return Html::a(
            '<i class="fa fa-eye"></i>',
            ['/traspaso/traspasodetalle/index', 'idtraspaso' => $model->id],
            [
                'title' => 'Ver',
                'class' => 'btn btn-default btn-view',
            ]
        );
    },

                'update' => function ($url, $model) {
        $t = Url::to([
            'update',
            'id' => $model->id
        ]);

        return Html::button('<i class="fa fa-edit"></i>', [
            'value' => $t,
            'title' => 'Actualizar',
            'class' => 'btn btn-default btn_update',
        ]);
    },

                'factura' => function ($url, $model) {
        return Html::a(
            '<i class="fa fa-print"></i>',
            ['factura', 'id' => $model->id],
            [
                'title' => 'Ver factura generada',
                'class' => 'btn btn-default',
            ]
        );
    },

                'anular' => function ($url, $model) {
        return Html::a(
            '<i class="fa fa-ban"></i>',
            ['anular', 'id' => $model->id],
            [
                'class' => 'btn btn-default',
                'title' => 'Anular Registro',
                'data' => [
                    'confirm' => 'Esta seguro de anular este registro? ( Origen: '
                        . $model->bodegaOrigen->nombre . ', Destino: '
                        . $model->bodegaDestino->nombre . ', Numero de cajas: '
                        . $model->numeroCajas . ' )',
                    'method' => 'post',
                ]
            ]
        );
    },
            ],

            'visibleButtons' => [
                'update' => function ($model, $key, $index) {
        return $model->idEstado == 0; // Condición para mostrar el botón
    },
                'detalle' => function ($model, $key, $index) {
        return $model->idEstado == 0; // Condición para mostrar el botón
    },

                'anular' => function ($model, $key, $index) {
        return $model->idEstado == 1; // Condición para mostrar el botón
    },
            ],

        ],
    ],
]); ?>

</div>