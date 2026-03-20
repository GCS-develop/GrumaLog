<?php

use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

use frontend\models\Item;
use frontend\models\Traspasodetalle;
use frontend\models\TipoDocumento;
use frontend\models\Estadotraspaso;
use common\widgets\Alert;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/** @var yii\web\View $this */
/** @var frontend\models\search\TraspasodetalleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Traspaso detalle';
$this->params['breadcrumbs'][] = ['label' => 'Traspasos', 'url' => ['traspaso/index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php

$this->registerJs("
    $(document).ready(function() {

        $('#EliminarBtn').on('click', function() {
            var ids = [];
            $('input[name=\"selection[]\"]:checked').each(function() {
                ids.push($(this).val());
            });

            if (ids.length === 0) {
                alert('Debes seleccionar al menos un registro.');
                return;
            }

            var cantidad = prompt('¿Cuántas unidades deseas eliminar por cada ítem seleccionado?');
            cantidad = parseInt(cantidad);

            if (isNaN(cantidad) || cantidad <= 0) {
                alert('Cantidad inválida. Debe ser un número mayor a cero.');
                return;
            }

            if (confirm('¿Estás seguro de que deseas eliminar ' + cantidad + ' unidad(es) de cada uno de los ' + ids.length + ' ítem(s) seleccionados?')) {

                $.ajax({
                    url: '" . \yii\helpers\Url::to(['/traspaso/traspasodetalle/eliminar']) . "',
                    type: 'POST',
                    data: {
                        ids: ids,
                        cantidad: cantidad
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Registros actualizados con éxito: ' + response.message);
                            $.pjax.reload({container: '#alert-pjax-container'});
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Hubo un error al eliminar el registro.';
                        alert(errorMsg);
                    }
                });

            } else {
                console.log('Eliminación cancelada por el usuario.');
            }
        });

    });
", \yii\web\View::POS_READY);
?>





<div class="traspasodetalle-index">

    <h1>
        <?php if ($traspaso): ?>
            <?= Html::encode(
                $traspaso->tipodocumento->codigo . '-' .
                    ($traspaso->codigoerp ? $traspaso->codigoerp->f350_consec_docto : $traspaso->consecutivo) .
                    '  Estado: ' . $traspaso->estado->nombre
            ) ?>
        <?php else: ?>
            <?= Html::encode('Todos los traspasos') ?>
        <?php endif; ?>
    </h1>

    <h2>
        <?php if ($traspaso && $traspaso->bodegaOrigen && $traspaso->bodegaDestino): ?>
            <?= Html::encode($traspaso->bodegaOrigen->codigo . $traspaso->bodegaOrigen->nombre . ' - ' .
                $traspaso->bodegaDestino->codigo . $traspaso->bodegaDestino->nombre .
                ' Cajas: ' . $traspaso->numeroCajas) ?>
        <?php else: ?>
            <?= Html::encode(' ') ?>
        <?php endif; ?>
    </h2>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>


    <?php

    $fecha_actual = date("Y-m-d");
    $filename = "Relacion_PlanillaEmbarque_" . $fecha_actual;
    $usuarioCreador = $dataProvider->getModels()[0]->usuarioCreador ?? 'Desconocido';


    $gridColumns = [
        [
            'class' => ActionColumn::className(),
            'contentOptions' => ['data-cellvalue' => 'Accion'],
            'header' => 'Eliminar',
            'template' => ' {eliminarajax} ', // Define qué acciones se mostrarán como botones
            'buttons' => [

                'eliminarajax' => function ($url, $model) {
                    $printUrl = Url::to(['eliminarajax']);
                    return Html::a(
                        '<i class="fa fa-trash fa-xs"></i>',
                        '#',
                        [
                            'class' => 'btn btn-default disabled',
                            'title' => 'Eliminar varios',
                            'onclick' => "openPrintModal('{$model->id}', '{$printUrl}', '{$model->idTraspaso}')",
                        ]
                    );
                },
            ],
            'visibleButtons' => [
                'eliminarajax' => function ($model, $key, $index) {
                    return $model->traspaso->estado->nombre === 'pendiente'; // Condición para mostrar el botón
                },
            ]
        ],
        [
            'class' => 'kartik\grid\CheckboxColumn',
            'checkboxOptions' => function ($model, $key, $index, $column) {
                return ($model->traspaso->estado->nombre === 'pendiente')
                    ? ['value' => $model->id, 'name' => 'seleccionar[]']
                    : ['style' => 'display:none']; // Oculta el checkbox si el estado no es 'pendiente'
            },
            'visible' => function ($model, $key, $index, $column) {
                return $model->traspaso->estado->nombre === 'pendiente'; // Oculta completamente la columna si no hay registros en estado 'pendiente'
            },
        ],

        [
            'label' => 'bodegaorigen',
            'value' => function ($model): mixed {
                return $model->traspaso->bodegaOrigen->nombre;
            },
            'group' => true,
        ],
        [
            'label' => 'bodegadestino',
            'value' => function ($model) {
                return $model->traspaso->bodegaDestino->nombre;
            },
            'group' => true,
        ],
        [
            'label' => 'No Interno',
            'value' => function ($model) {
                return $model->traspaso->consecutivo;
            },
            'group' => true,
        ],
        [
            'attribute' => 'idTraspaso',
            'group' => true,

        ],
        [
            'attribute' => 'tipoMovimiento',
            'value' => function ($model) {
                return $model->traspaso->tipoMovimiento == 1 ? 'Traspaso' : 'Entradas';
            },
            'group' => true,
        ],
        [
            'label' => 'Traspaso',
            'value' => function ($model) {
                // Verifica si el modelo y las relaciones existen
                $codigoErp = $model->traspaso->codigoerp->f350_consec_docto ?? null;
                if ($codigoErp) {
                    return $model->traspaso->tipodocumento->codigo . '-' . $codigoErp;
                }
                return $model->traspaso->tipodocumento->codigo . '-' . $model->traspaso->consecutivo ?? 'N/A';
            },
            'group' => true,
        ],
        [
            'attribute' => 'Bodega origen',
            'value' => function ($model) {
                return $model->traspaso->bodegaOrigen->codigo . '-' . $model->traspaso->bodegaOrigen->nombre;
            },
            'enableSorting' => true,
            'group' => true,
        ],
        [
            'attribute' => 'Bodega destino',
            'value' => function ($model) {
                return $model->traspaso->bodegaDestino->codigo . '-' . $model->traspaso->bodegaDestino->nombre;
            },
            'enableSorting' => true,
            'group' => true,
        ],
        [
            'attribute' => 'proveedor',
            'value' => function ($model) {
                return $model->itemAll ? $model->itemAll->nombreProveedor : '-';
            },
            'enableSorting' => true,
            'group' => true,
        ],
        [
            'attribute' => 'idItem',
            'value' => function ($model) {
                return $model->itemAll ? $model->itemAll->item : '-';
            },
            'enableSorting' => true,
            'group' => true,
        ],
        [
            'attribute' => 'idItem',
            'value' => function ($model) {
                return $model->itemAll ? $model->itemAll->codigoBarras : '-';
            },
            'enableSorting' => true,
            'group' => true,
        ],
        [
            'label' => 'Talla',
            'value' => function ($model) {
                return $model->itemAll && $model->itemAll->talla ? $model->itemAll->talla->nombre : '-';
            },
        ],
        [
            'label' => 'Color',
            'value' => function ($model) {
                return $model->itemAll && $model->itemAll->color ? $model->itemAll->color->nombre : '-';
            },
        ],
        [
            'label' => 'Descripcion',
            'value' => function ($model) {
                return $model->itemAll ? $model->itemAll->descripcion : '-';
            },
        ],
        [
            'label' => 'Estado Item',
            'value' => function ($model) {
                return $model->itemAll ? $model->itemAll->idEstado : '-';
            },
            'contentOptions' => function ($model) {
                return ($model->item === null && $model->itemAll !== null)
                    ? ['style' => 'font-weight:bold; color:#c00;']
                    : [];
            },
        ],
        [
            'label' => 'paquete',
            'value' => function ($model) {
                return $model->itemAll
                    ? ($model->itemAll->unidadempaque
                        ? $model->itemAll->unidadempaque->codigo
                        : ($model->itemAll->unidadorden ? $model->itemAll->unidadorden->codigo : '-'))
                    : '-';
            },
        ],
        [
            'label' => ' Registros',
            'attribute' => 'cantidad',
            'format' => ['decimal', 0], // Formato decimal con 0 decimales,
            'pageSummary' => true,

        ],
        [
            'label' => 'unidades',
            'value' => function ($model) {
                $item = $model->itemAll;

                if ($item === null) {
                    return 0;
                }

                if ($item->unidadempaque !== null) {
                    return $model->cantidad * $item->unidadempaque->equivalencia;
                }

                if ($item->unidadorden !== null) {
                    return $model->cantidad * $item->unidadorden->equivalencia;
                }

                return 0;
            },
            'format' => ['decimal', 0],
            'pageSummary' => true,
        ],

        [
            'label' => 'unidadesInventarioSiesa',
            'value' => function ($model) {
                $estado = $model->traspaso->estado ? $model->traspaso->estado->nombre : '';
                if (strcasecmp($estado, 'Pendiente') === 0 && $model->itemAll) {
                    return Item::getInventario($model->itemAll->codigoBarras, $model->traspaso->bodegaOrigen->codigo);
                }
                return null;
            },
            'format' => ['decimal', 0],
            'pageSummary' => true,
        ],

        [
            'label' => 'unidadesInventarioGruma',
            'value' => function ($model) {
                $estado = $model->traspaso->estado ? $model->traspaso->estado->nombre : '';
                if (strcasecmp($estado, 'Pendiente') === 0 && $model->itemAll) {
                    return $model->itemAll->geInventariogruma($model->itemAll->codigoBarras, $model->traspaso->bodegaOrigen->codigo);
                }
                return null;
            },
            'format' => ['decimal', 0],
            'pageSummary' => true,
        ],

        [
            'attribute' => 'idEstado',
            'contentOptions' => ['data-cellvalue' => 'idEstado',],
            'filter' => Estadotraspaso::getListaData(),
            'value' => function ($model) {
                return $model->traspaso->estado->nombre;
            },
        ],
        'estadoPlanilla',
        'created_at',
        [
            'attribute' => 'created_by',
            'value' => function ($model) {
                return $model->usuariocreated->username;
            },
        ],
        [
            'label' => 'actualizado',
            'attribute' => 'updated_at'
        ],
        [
            'attribute' => 'updated_by',
            'value' => function ($model) {
                return $model->usuarioupdated->username;
            },
        ],

    ];


    ?>

    <link rel="stylesheet" href="css/shared.css">


    <?= $this->render('_search', ['model' => $searchModel]) ?>


    <?= Alert::widget() ?>


    <?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

    <div class="row">

        <!-- <div class="col-lg-12 centrar">
        <h3><?php var_dump($usuarioCreador) ?></h3>
    </div> -->

        <div class="col-lg-6 derecha">


            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $dataProvider,
                    'columns' => $gridColumns,
                    'fontAwesome' => true,
                    'filename' => $filename,
                    'dropdownOptions' => [
                        'label' => 'Exportar',
                        'class' => 'btn btn-primary btn-lg btn-create ',
                        // btn disabled',
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


        <div class="col-lg-6 izquierda">

            <!-- Botón para cambiar el estado de los registros -->
            <?= Html::button('Eliminar', [
                'class' => 'btn btn-danger btn-create btn-lg disabled d-none',
                'id' => 'EliminarBtn',
            ]) ?>

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
            // Item bloqueado (idEstado != ACTIVO): relación getItem() retorna null
            if ($model->item === null) {
                $classes[] = 'danger';
                return ['class' => implode(' ', $classes), 'title' => 'Item bloqueado'];
            }
            if ($model->traspaso->estado->nombre === 'muelle') {
                $classes[] = 'text-success';
            }
            if ($model->traspaso->estado->nombre === 'anulado') {
                $classes[] = 'text-danger';
            }
            if ($model->traspaso->estado->nombre === 'pendiente') {
                $classes[] = 'text-primary';
            }
            if (($model->traspaso->estado->nombre === 'pendiente') && (Item::getInventario($model->item->codigoBarras, $model->traspaso->bodegaOrigen->codigo) < $model->cantidadunidades)) {
                $classes[] = 'text-danger';
            }
            return ['class' => implode(' ', $classes)];
        },
        'columns' => array_merge(
            [
                ['class' => 'kartik\grid\SerialColumn'],
            ],

            $gridColumns,

        ),
    ]);
    ?>
    <?php Pjax::end(); ?>

</div>




<script src="<?= Yii::$app->request->baseUrl ?>/js/sweetalert2@11.js"></script>
<link rel="stylesheet" href="css/shared.css">
<link rel="stylesheet" href="<?= Yii::$app->request->baseUrl ?>/css/swal.css">

<script>
    function openPrintModal(id, url, idTraspaso) {
        Swal.fire({
            title: 'Digite la cantidad a eliminar',
            text: 'Por favor, ingrese un valor antes de continuar:',
            input: 'text',
            inputPlaceholder: 'Escribe aquí...',
            showCancelButton: true,
            buttonsStyling: false,
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Aceptar',
            inputAttributes: {
                step: '1',
                min: '1'
            },
            input: 'number',
            inputAttributes: {
                min: 1
            },

            inputValidator: (value) => {
                if (!value) {
                    return '¡Debes ingresar un valor!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Muestra el indicador de carga
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Por favor espere mientras se procesa la solicitud.',
                    allowOutsideClick: false, // Desactiva hacer clic fuera de la alerta
                    didOpen: () => {
                        Swal.showLoading(); // Muestra el cargador
                    },
                    willClose: () => {
                        // Esto se asegura de que el modal de carga no se cierre hasta que termine la operación
                    }
                });
                // Enviar el valor y el ID al controlador mediante POST
                $.post(url, {
                        id: id,
                        input: result.value,
                        idtraspaso: idTraspaso
                    })
                    .done(function(response) {
                        if (response.status === 'success') {
                            Swal.fire('¡Éxito!', response.message, 'success');
                            $('#count').val(response.count);
                            $('#cantidad_paquetes').val(response.cantidad_paquetes);
                            // $.pjax.reload({ container: '#pjax-container' });
                            $.pjax.reload({
                                container: '#alert-pjax-container',
                                async: false
                            });

                        } else {
                            Swal.fire('¡Error!', response.message, 'error');
                        }
                    })
                    .fail(function() {
                        Swal.close(); // Cierra la alerta de carga
                        Swal.fire('¡Error!', 'Hubo un problema con la conexión.', 'error');
                    })

                // .always(function () {
                //     // Este bloque siempre se ejecuta, sin importar si la solicitud fue exitosa o fallida
                //     // Se cierra la alerta de carga
                //     Swal.close(); // Cierra la alerta de carga
                // });
            }
        });
    }
</script>