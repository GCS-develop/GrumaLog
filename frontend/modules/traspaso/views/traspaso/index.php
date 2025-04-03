<?php

use frontend\models\Traspaso;
use frontend\models\Usertraspaso;
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

use yii\widgets\ActiveForm;
use yii\widgets\Pjax;


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
    'id',
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
        'contentOptions' => ['data-cellvalue' => 'idEstado',],
        'filter' => Estadotraspaso::getListaData(),
        'value' => function ($model) {
            return $model->estado->nombre;
        },
    ],
    'estadoPlanilla',
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

    'idusertraspasocdsc',
    'anula_at',
    [
        'attribute' => 'anula_by',
        'label' => 'Usuario que puso en anula',
        'contentOptions' => ['data-cellvalue' => 'Usuario'],
        'value' => function ($model) {
            return $model->anula_by ? $model->anula_by . '-' . $model->anulaByUser->username : ' - ';
        },
    ],
    'muelle_at',
    [
        'attribute' => 'muelle_by',
        'label' => 'Usuario que puso en muelle',
        'contentOptions' => ['data-cellvalue' => 'Usuario'],
        'value' => function ($model) {
            return $model->muelle_by ? $model->muelle_by . '-' . $model->muelleByUser->username : ' - ';
        },
    ],
];

?>
<?php

$this->registerJs("
    $(document).ready(function() {
        
        $('#cambiarEstadoBtn').on('click', function() {
            if (confirm('¿Estás seguro de que deseas enviar a muelle los registros seleccionados?')) {
                var ids = [];
                $('input[name=\"selection[]\"]:checked').each(function() {
                    ids.push($(this).val());
                });

                if (ids.length === 0) {
                    alert('Debes seleccionar al menos un registro.');
                    return;
                }

                $.ajax({
                    url: '" . \yii\helpers\Url::to(['/traspaso/traspaso/cambiar-estado']) . "',
                    type: 'POST',
                    data: { ids: ids },
                    success: function(response) {
                        if (response.success) {
                            alert('Estado actualizado con éxito: ' + response.message);
                            // $.pjax.reload({container: '#my-container'});
                            $.pjax.reload({container: '#alert-pjax-container'});

                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Hubo un error al actualizar el estado.';
                        alert(errorMsg);
                        // console.log(xhr.responseText);
                    }
                });
            } else {
                console.log('Cambio de estado cancelado.');
            }
        });

    });
", \yii\web\View::POS_READY);


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
        <!-- <div class="col-lg-6 derecha">

            <?php $url = Url::to(['create']); ?>

            <p>
                <?= Html::button(
                    'Registrar',
                    ['value' => $url, 'class' => 'btn btn-success btn-lg btn-create', 'id' => 'modalButtonCreate']
                )
                    ?>
            </p>

        </div> -->

        <div class="col-lg-6 derecha">

            <!-- Botón para cambiar el estado de los registros -->
            <?= Html::button('Muelle masivo', [
                'class' => 'btn btn-info btn-create btn-lg',
                'id' => 'cambiarEstadoBtn',
            ]) ?>

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

<?php Pjax::begin(['id' => 'alert-pjax-container']); ?>

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
    $classes = [];
    if (
        $model->estadoPlanilla == 'Recibido'
    ) {
        $classes[] = 'text-success';
    }
    if ($model->estado->nombre === 'muelle') {
        $classes[] = 'text-info';
    }
    if ($model->estado->nombre === 'anulado') {
        $classes[] = 'text-danger';
    }
    if ($model->estado->nombre === 'terminado') {
        $classes[] = 'text-secondary';
    }
    if (
        $model->estadoPlanilla == 'Recibido' &&
        strtotime($model->fechaRecibido) < strtotime('-2 days')
    ) {
        $classes[] = 'text-danger';
    }



    return ['class' => implode(' ', $classes)];
},
    'toolbar' => [
        '{export}',
    ],
    'export' => [
        'fontAwesome' => true,
        'target' => GridView::TARGET_SELF, // Evita la recarga completa de la página
        'filename' => $filename,
    ],

    'columns' => [
        ['class' => 'kartik\grid\SerialColumn'],

        [
            'class' => 'kartik\grid\CheckboxColumn',
            'checkboxOptions' => function ($model, $key, $index, $column) {
    return ($model->estado->nombre === 'terminado')
        ? ['value' => $model->id, 'name' => 'seleccionar[]']
        : ['style' => 'display:none']; // Oculta el checkbox si el estado no es 'Terminado'
},
            'visible' => function ($model, $key, $index, $column) {
    return $model->estado->nombre === 'terminado'; // Oculta completamente la columna si no hay registros en estado 'Terminado'
},
        ],

        'id',
        // 'idTipoDocumento',
        [
            'attribute' => 'idTipoDocumento',
            'label' => 'Tipo Dcto.',
            'filter' => TipoDocumento::getListaDataCodigo(),
            'contentOptions' => ['data-cellvalue' => 'serie'],
            'value' => function ($model) {
    return $model->tipodocumento ? $model->tipodocumento->codigo : 'Sin serie';
},
        ],

        [
            'attribute' => 'consecutivo',
            'label' => 'No Interno',
        ],
        [
            'attribute' => 'consecutivo',
            'label' => 'No. ERP',
            'value' => function ($model) {
    return $model->codigoerp ? $model->codigoerp->f350_consec_docto : '-';
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
            'label' => 'nombre de proveedor',
            'value' => function ($model) {
    return isset($model->traspasodetalles[0]->item) ? $model->traspasodetalles[0]->item->nombreProveedor : 'Sin proveedor';
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

        [
            'attribute' => 'idEstado',
            'contentOptions' => ['data-cellvalue' => 'idEstado',],
            'filter' => Estadotraspaso::getListaData(),
            'value' => function ($model) {
    return $model->estado->nombre;
},
        ],
        [
            'attribute' => 'transferenciaerp',
            'value' =>
                function ($model) {
        return $model->estadoTransferencia;
    },
        ],
        'estadoPlanilla',
        'fechaRecibido',

        // 'idUltimoItem',
        [
            'attribute' => 'created_at',
            'label' => 'Fecha Crea',
            'value' => function ($model) {
    return substr($model->created_at, 0, 16);
},
        ],

        'idusertraspasocdsc',

        [
            'attribute' => 'updated_at',
            'label' => 'Fecha Act.',
            'value' => function ($model) {
    return substr($model->updated_at, 0, 16);
},
        ],
        [
            'attribute' => 'updated_by',
            'label' => 'Ultimo usuario',
            'contentOptions' => ['data-cellvalue' => 'Usuario'],
            'value' => function ($model) {
    return $model->updated_by . ' - ' . $model->updatedByUser->username;
},
        ],
        [
            'attribute' => 'tipoMovimiento',
            'value' => function ($model) {
    switch ($model->tipoMovimiento) {
        case 3:
            $movimiento = 'CDSC';
            break;
        case 2:
            $movimiento = 'Entradas';
            break;
        default:
            $movimiento = 'Traspaso';
    }
    return $movimiento;
},
        ],
        'anula_at',
        [
            'attribute' => 'anula_by',
            'label' => 'Usuario que puso en anula',
            'contentOptions' => ['data-cellvalue' => 'Usuario'],
            'value' => function ($model) {
    return $model->anula_by ? $model->anula_by . '-' . $model->anulaByUser->username : ' - ';
},
        ],
        'muelle_at',
        [
            'attribute' => 'muelle_by',
            'label' => 'Usuario que puso en muelle',
            'contentOptions' => ['data-cellvalue' => 'Usuario'],
            'value' => function ($model) {
    return $model->muelle_by ? $model->muelle_by . '-' . $model->muelleByUser->username : ' - ';
},
        ],
        [
            'class' => ActionColumn::className(),
            'header' => 'Acción',
            'headerOptions' => ['width' => '10%'],
            'template' => ' {view} {update} {anular} {factura} {directo} {interno} {siesa}',
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
                'directo' => function ($url, $model) {
        return Html::a(
            '<i class="fa fa-check"></i>',
            ['directo', 'id' => $model->id],
            [
                'title' => 'Traspaso directo de tienda',
                'class' => 'btn btn-default',
            ]
        );
    },
                'interno' => function ($url, $model) {
        return Html::a(
            '<i class="fa fa-arrow-right"></i>',
            ['interno', 'id' => $model->id],
            [
                'title' => 'Traspaso interno',
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

                'siesa' => function ($url, $model) {
        return Html::a(
            '<i class="fa fa-sync"></i>',
            ['sincronizar', 'id' => $model->id],
            [
                'class' => 'btn btn-default',
                'title' => 'Sincronizar Documento ERP',
                'data' => [
                    'confirm' => 'Esta seguro de Sincronizar Este Documento? ( Origen: '
                        . $model->bodegaOrigen->nombre . ', Destino: '
                        . $model->bodegaDestino->nombre . ', No. Traspaso: ' . $model->id . ' )',
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
        return $model->idEstado != 2; // Condición para mostrar el botón
    },
                'factura' => function ($model, $key, $index) {
        return $model->idEstado == 1 || $model->idEstado == 3; // Condición para mostrar el botón
    },
                'siesa' => function ($model, $key, $index) {
        return $model->idEstado == 1 || $model->idEstado == 3; // Condición para mostrar el botón
    },
                'directo' => function ($model, $key, $index) {
        return $model->idEstado == 1 || $model->idEstado == 3; // Condición para mostrar el botón
    },
                'interno' => function ($model, $key, $index) {
        return $model->idEstado == 1 || $model->idEstado == 3; // Condición para mostrar el botón
    },
            ],

        ],
    ],
]); ?>
<?php Pjax::end(); ?>

</div>