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
    'serie',
    'consecutivo',
    'consecutivosiesa',
    'Origen',
    'Destino',
    'numeroCajas',
    [
        'label' => 'nombre de proveedor',
        'value' => function ($model) {
            return isset($model->traspasodetalles[0]->item) ? $model->traspasodetalles[0]->item->nombreProveedor : 'Sin proveedor';
        },

    ],
    [
        'attribute' => 'totalRegistros',
        'pageSummary' => true,
    ],
    [
        'attribute' => 'totalUnidad',
        'pageSummary' => true,

    ],
    'estadoNombre',
    'estadoPlanilla',
    'reciboMasivo',
    'fechaRecibido',
    'FechaCrea',
    'idusertraspasocdsc',
    'FechaActualiza',
    'userActualiza',
    'tipoMovimientoNombre',
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
    'title' => '<h4>Revisar items</h4>',
    'id' => 'modaldata',
    'size' => 'modal-xl',
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

            <!-- <?php if (Yii::$app->user->id == '17'): ?>

                <?= Html::a('Exportar Excel', ['exportar-excel'], [
                            'class' => 'btn btn-success btn-lg btn-create',
                            'target' => '_blank'
                        ]) ?>

            <?php endif; ?> -->


            <?= Html::a('Sincronizar', ['ejecutar-exe'], [
                'class' => 'btn btn-success btn-lg btn-create',
                'target' => '_blank'
            ]) ?>

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

        if ($model->estado->nombre === 'muelle' && $model->estadoPlanilla != 'Recibido') {
            $classes[] = 'text-info';
        } else {
            $classes[] = 'text-success';
        }
        if ($model->estado->nombre === 'anulado') {
            $classes[] = 'text-danger';
        }
        if ($model->estado->nombre === 'terminado') {
            $classes[] = 'text-secondary';
        }
        // if (
        //     $model->estadoPlanilla == 'Recibido'
        // ) {
        //     $classes[] = 'text-success';
        // }
        // if (
        //     $model->estadoPlanilla == 'Recibido' &&
        //     strtotime($model->fechaRecibido) < strtotime('-2 days')
        // ) {
        //     $classes[] = 'text-danger';
        // }



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
                return (($model->estado->nombre === 'terminado') || ($model->estado->nombre === 'directo tienda'))
                    ? ['value' => $model->id, 'name' => 'seleccionar[]']
                    : ['style' => 'display:none']; // Oculta el checkbox si el estado no es 'Terminado'
            },
            'visible' => function ($model, $key, $index, $column) {
                return $model->estado->nombre === 'terminado'; // Oculta completamente la columna si no hay registros en estado 'Terminado'
            },
        ],




        // 'id',
        // 'tipodocumento',
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
        // [
        //     'attribute' => 'transferenciaerp',
        //     'value' =>
        //     function ($model) {
        //         return $model->estadoTransferencia;
        //     },
        // ],
        'estadoPlanilla',
        [
            'attribute' => 'reciboMasivo',
            'value' => function ($model) {
                return $model->reciboMasivo ? 'Si' : 'No';
            },
        ],
        'fechaRecibido',
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
            'template' => '{retornar} {anular} {view} {update}  {factura} {directo} {interno} {siesa} {desbloquear} {viewTraspasodetalledelete} ,{viewTraspasodetalleauditado}',
            'buttons' => [
                'anular' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-ban"></i>',
                        ['anular', 'id' => $model->id],
                        [
                            'class' => 'btn btn-default text-danger',
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

                'retornar' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-undo"></i>',
                        ['retornar', 'id' => $model->id],
                        [
                            'class' => 'btn btn-default text-secondary',
                            'title' => 'Retornar a estado anterior',
                            'data' => [
                                'confirm' => '¿Está seguro de retornar el estado de este traspaso' . '? ( Origen: '
                                    . $model->bodegaOrigen->nombre . ', Destino: '
                                    . $model->bodegaDestino->nombre . ', Numero de cajas: '
                                    . $model->numeroCajas . ' )',
                                'method' => 'post',
                            ]
                        ]
                    );
                },

                'view' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-eye"></i>',
                        ['/traspaso/traspasodetalle/index', 'idtraspaso' => $model->id],
                        [
                            'title' => 'Ver detalles',
                            'class' => 'btn btn-default btn-view',
                        ]
                    );
                },

                'viewTraspasodetalledelete' => function ($url, $model) {
                    $url = Url::to(['/traspaso/traspasodetalledelete/index', 'idtraspaso' => $model->id]);
                    return Html::button(
                        '<i class="fa fa-search"></i>',
                        [
                            'value' => $url,
                            'class' => 'btn btn-default btn_create',  // clase btn_create para que lo escuche tu JS
                            'title' => 'Ver detalles eliminados',
                        ]
                    );
                },

                'viewTraspasodetalleauditado' => function ($url, $model) {
                    $url = Url::to(['/traspaso/traspasodetalleauditado/index', 'idtraspaso' => $model->id]);
                    return Html::button(
                        '<i class="fa fa-clipboard-check"></i>',
                        [
                            'value' => $url,
                            'class' => 'btn btn-default btn_create ',  // clase btn_create para que lo escuche tu JS
                            'title' => 'Ver detalles auditados',
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



                'siesa' => function ($url, $model) {
                    $actionUrl = Url::to(['sincronizar', 'id' => $model->id]);
                    $desc = 'Origen: ' . $model->bodegaOrigen->nombre
                        . ', Destino: ' . $model->bodegaDestino->nombre
                        . ', No. Traspaso: ' . $model->id;
                    return Html::button(
                        '<i class="fa fa-sync"></i>',
                        [
                            'class' => 'btn btn-default btn-sincronizar-siesa',
                            'title' => 'Sincronizar Documento ERP',
                            'data-url' => $actionUrl,
                            'data-desc' => $desc,
                        ]
                    );
                },

                'desbloquear' => function ($url, $model) {
                    $statusUrl   = Url::to(['desbloquear-status', 'id' => $model->id]);
                    $unlockUrl   = Url::to(['desbloquear', 'id' => $model->id]);
                    return Html::button(
                        '<i class="fa fa-lock-open"></i>',
                        [
                            'class'           => 'btn btn-default text-warning btn-desbloquear',
                            'title'           => 'Desbloquear traspaso atascado',
                            'data-status-url' => $statusUrl,
                            'data-unlock-url' => $unlockUrl,
                            'data-id'         => $model->id,
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
                // 'factura' => function ($model, $key, $index) {
                //     return $model->idEstado == 1 || $model->idEstado == 3; // Condición para mostrar el botón
                // },
                'siesa' => function ($model, $key, $index) {
                    return $model->idEstado != 2 || $model->idEstado != 0; // Condición para mostrar el botón

                    // return $model->idEstado == 1 || $model->idEstado == 3; // Condición para mostrar el botón
                },
                'directo' => function ($model, $key, $index) {
                    return $model->idEstado == 1 || $model->idEstado == 3; // Condición para mostrar el botón
                },
                'interno' => function ($model, $key, $index) {
                    return $model->idEstado == 1 || $model->idEstado == 3; // Condición para mostrar el botón
                },

                'retornar' => function ($model, $key, $index) {
                    return $model->idEstado == 6; //Condicion para mostrar el boton
                },

                'desbloquear' => function ($model, $key, $index) {
                    return (int)$model->transferenciaerp === 2;
                },

            ],

        ],
    ],
]); ?>
<?php Pjax::end(); ?>

<!-- Modal confirmación sincronizar SIESA -->
<div class="modal fade" id="modalSincronizarSiesa" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-sync"></i> Sincronizar con ERP</h5>
            </div>
            <div id="siesa-confirm-panel" class="modal-body">
                <p>¿Está seguro de sincronizar este traspaso con SIESA?</p>
                <p class="text-muted mb-0" style="font-size:12px" id="siesa-desc-text"></p>
            </div>
            <div id="siesa-spinner-panel" class="modal-body text-center d-none">
                <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
                <p class="mt-3 mb-0">Sincronizando con SIESA...</p>
            </div>
            <div class="modal-footer" id="siesa-modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmSincronizar">
                    <i class="fa fa-sync"></i> Sincronizar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal desbloquear traspaso -->
<div class="modal fade" id="modalDesbloquear" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-lock-open text-warning"></i> Desbloquear Traspaso</h5>
            </div>

            <!-- Panel: consultando -->
            <div id="dblq-checking-panel" class="modal-body text-center">
                <div class="spinner-border text-warning" style="width:2.5rem;height:2.5rem;" role="status"></div>
                <p class="mt-3 mb-0">Consultando estado en SIESA...</p>
            </div>

            <!-- Panel: confirmación -->
            <div id="dblq-confirm-panel" class="modal-body d-none">
                <div id="dblq-siesa-existe" class="alert alert-warning d-none">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>El documento YA existe en SIESA.</strong><br>
                    Al confirmar se sincronizará y se marcará como <strong>completado</strong>.
                </div>
                <div id="dblq-siesa-noexiste" class="alert alert-info d-none">
                    <i class="fa fa-info-circle"></i>
                    El documento <strong>no existe en SIESA</strong>.<br>
                    Al confirmar se liberará el candado para que pueda volver a enviarse.
                </div>
                <p class="text-muted mb-0" style="font-size:12px" id="dblq-info-text"></p>
            </div>

            <!-- Panel: spinner ejecutando -->
            <div id="dblq-spinner-panel" class="modal-body text-center d-none">
                <div class="spinner-border text-warning" style="width:3rem;height:3rem;" role="status"></div>
                <p class="mt-3 mb-0">Desbloqueando traspaso...</p>
            </div>

            <!-- Panel: resultado -->
            <div id="dblq-result-panel" class="modal-body d-none">
                <div id="dblq-result-msg"></div>
            </div>

            <div class="modal-footer" id="dblq-footer-confirm" style="display:none!important">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmDesbloquear">
                    <i class="fa fa-lock-open"></i> Confirmar desbloqueo
                </button>
            </div>
            <div class="modal-footer" id="dblq-footer-close" style="display:none!important">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="btnCerrarDesbloquear">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
var _dblqUnlockUrl = '';

$(document).on('click', '.btn-desbloquear', function () {
    var statusUrl = $(this).data('status-url');
    _dblqUnlockUrl = $(this).data('unlock-url');

    // Reset modal
    $('#dblq-checking-panel').removeClass('d-none');
    $('#dblq-confirm-panel,#dblq-spinner-panel,#dblq-result-panel').addClass('d-none');
    $('#dblq-siesa-existe,#dblq-siesa-noexiste').addClass('d-none');
    $('#dblq-footer-confirm,#dblq-footer-close').css('display', 'none !important').hide();
    $('#modalDesbloquear').modal('show');

    $.ajax({
        url: statusUrl,
        method: 'GET',
        dataType: 'json',
        success: function (resp) {
            $('#dblq-checking-panel').addClass('d-none');
            if (!resp.ok) {
                $('#dblq-result-msg').html('<div class="alert alert-danger"><i class="fa fa-times-circle"></i> ' + resp.error + '</div>');
                $('#dblq-result-panel').removeClass('d-none');
                $('#dblq-footer-close').show();
                return;
            }
            if (resp.existeEnSiesa) {
                $('#dblq-siesa-existe').removeClass('d-none');
            } else {
                $('#dblq-siesa-noexiste').removeClass('d-none');
            }
            var info = resp.info;
            $('#dblq-info-text').text('Traspaso #' + info.id + ' | Consecutivo: ' + info.consecutivo + ' | ' + info.origen + ' → ' + info.destino);
            $('#dblq-confirm-panel').removeClass('d-none');
            $('#dblq-footer-confirm').show();
        },
        error: function () {
            $('#dblq-checking-panel').addClass('d-none');
            $('#dblq-result-msg').html('<div class="alert alert-danger"><i class="fa fa-times-circle"></i> Error al consultar el estado. Intenta de nuevo.</div>');
            $('#dblq-result-panel').removeClass('d-none');
            $('#dblq-footer-close').show();
        }
    });
});

$('#btnConfirmDesbloquear').on('click', function () {
    $('#dblq-confirm-panel').addClass('d-none');
    $('#dblq-footer-confirm').hide();
    $('#dblq-spinner-panel').removeClass('d-none');

    $.ajax({
        url: _dblqUnlockUrl,
        method: 'POST',
        data: { _csrf: yii.getCsrfToken() },
        dataType: 'json',
        success: function (resp) {
            $('#dblq-spinner-panel').addClass('d-none');
            if (resp.ok) {
                var cls = resp.action === 'completado' ? 'alert-success' : 'alert-info';
                var icon = resp.action === 'completado' ? 'fa-check-circle' : 'fa-unlock';
                $('#dblq-result-msg').html('<div class="alert ' + cls + '"><i class="fa ' + icon + '"></i> ' + resp.message + '</div>');
            } else {
                $('#dblq-result-msg').html('<div class="alert alert-danger"><i class="fa fa-times-circle"></i> ' + resp.error + '</div>');
            }
            $('#dblq-result-panel').removeClass('d-none');
            $('#dblq-footer-close').show();
        },
        error: function () {
            $('#dblq-spinner-panel').addClass('d-none');
            $('#dblq-result-msg').html('<div class="alert alert-danger"><i class="fa fa-times-circle"></i> Error al ejecutar el desbloqueo.</div>');
            $('#dblq-result-panel').removeClass('d-none');
            $('#dblq-footer-close').show();
        }
    });
});

$('#btnCerrarDesbloquear').on('click', function () {
    location.reload();
});
JS;
$this->registerJs($js);
?>

<?php
$js = <<<JS
var _siesaSincronizarUrl = '';

$(document).on('click', '.btn-sincronizar-siesa', function () {
    _siesaSincronizarUrl = $(this).data('url');
    var desc = $(this).data('desc');
    $('#siesa-desc-text').text(desc);
    $('#siesa-confirm-panel').removeClass('d-none');
    $('#siesa-spinner-panel').addClass('d-none');
    $('#siesa-modal-footer').removeClass('d-none');
    $('#modalSincronizarSiesa').modal('show');
});

$('#btnConfirmSincronizar').on('click', function () {
    $('#siesa-confirm-panel').addClass('d-none');
    $('#siesa-spinner-panel').removeClass('d-none');
    $('#siesa-modal-footer').addClass('d-none');
    window.location.href = _siesaSincronizarUrl;
});
JS;
$this->registerJs($js);
?>

</div>