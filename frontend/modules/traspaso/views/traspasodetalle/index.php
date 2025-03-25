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
            if (confirm('¿Estás seguro de que deseas eliminar los registros seleccionados?')) {
                var ids = [];
                $('input[name=\"selection[]\"]:checked').each(function() {
                    ids.push($(this).val());
                });

                if (ids.length === 0) {
                    alert('Debes seleccionar al menos un registro.');
                    return;
                }

                $.ajax({
                    url: '" . \yii\helpers\Url::to(['/traspaso/traspasodetalle/eliminar']) . "',
                    type: 'POST',
                    data: { ids: ids },
                    success: function(response) {
                        if (response.success) {
                            alert('Registros eliminados con éxito: ' + response.message);
                            // $.pjax.reload({container: '#my-container'});
                            $.pjax.reload({container: '#alert-pjax-container'});

                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Hubo un error al eliminar el registro.';
                        alert(errorMsg);
                        // console.log(xhr.responseText);
                    }
                });
            } else {
                console.log('Cancelado.');
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

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <?php

    $fecha_actual = date("Y-m-d");
    $filename = "Relacion_PlanillaEmbarque_" . $fecha_actual;
    $usuarioCreador = $dataProvider->getModels()[0]->usuarioCreador ?? 'Desconocido';


    $gridColumns = [
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
                return $model->item->nombreProveedor;
            },
            'enableSorting' => true,
            'group' => true,
        ],
        [
            'attribute' => 'idItem',
            'value' => function ($model) {
                return $model->item->item;
            },
            'enableSorting' => true,
            'group' => true,
        ],
        [
            'attribute' => 'idItem',
            'value' => function ($model) {
                return $model->item->codigoBarras;
            },
            'enableSorting' => true,
            'group' => true,
        ],
        [
            'label' => 'Talla',
            // 'attribute' => 'idItem',
            'value' => function ($model) {
                return $model->item->talla->nombre;
            },
        ],
        [
            'label' => 'Color',
            // 'attribute' => 'idItem',
            'value' => function ($model) {
                return $model->item->color->nombre;
            },
        ],
        [
            'label' => 'Descripcion',
            // 'attribute' => 'idItem',
            'value' => function ($model) {
                return $model->item->descripcion;
            },
        ],
        [
            'label' => 'paquete',
            // 'attribute' => 'idItem',
            'value' => function ($model) {
                return $model->item->unidadempaque ? $model->item->unidadempaque->codigo : $model->item->unidadorden->codigo;
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
                return $model->cantidad * ($model->item->unidadempaque ? $model->item->unidadempaque->equivalencia : $model->item->unidadorden->equivalencia);
            },
            'format' => ['decimal', 0], // Formato decimal con 0 decimales,
            'pageSummary' => true,

        ],

        [
            'label' => 'unidadesInventarioSiesa',
            'value' => function ($model) {
                // Verifica si el estado es "Pendiente"
                $estado = $model->traspaso->estado ? $model->traspaso->estado->nombre : '';
                if (strcasecmp($estado, 'Pendiente') === 0) {
                    return Item::getInventario($model->item->codigoBarras, $model->traspaso->bodegaOrigen->codigo);
                }
                return null; // O devuelve '-' si prefieres que se vea un guion en lugar de vacío
            },
            'format' => ['decimal', 0], // Formato decimal con 0 decimales
            'pageSummary' => true,
        ],

        [
            'label' => 'unidadesInventarioGruma',
            'value' => function ($model) {
                // Verifica si el estado es "Pendiente"
                $estado = $model->traspaso->estado ? $model->traspaso->estado->nombre : '';
                if (strcasecmp($estado, 'Pendiente') === 0) {
                    return $model->item->geInventariogruma($model->item->codigoBarras, $model->traspaso->bodegaOrigen->codigo);
                    ;
                }
                return null; // O devuelve '-' si prefieres que se vea un guion en lugar de vacío
            },
            'format' => ['decimal', 0], // Formato decimal con 0 decimales
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
                'class' => 'btn btn-danger btn-create btn-lg',
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