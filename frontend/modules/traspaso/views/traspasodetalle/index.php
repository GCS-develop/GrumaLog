<?php
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;


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


<!-- <?php

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
                    url: '" . \yii\helpers\Url::to(['/traspaso/traspasodetalle/cambiar-estado']) . "',
                    type: 'POST',
                    data: { ids: ids },
                    success: function(response) {
                        if (response.success) {
                            alert('Estado actualizado con éxito: ' + response.message);
                            $.pjax.reload({container: '#gridview-container'}); // Recargar tabla
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Hubo un error al actualizar el estado.';
                        alert(errorMsg);
                        console.log(xhr.responseText);
                    }
                });
            } else {
                console.log('Cambio de estado cancelado.');
            }
        });

    });
", \yii\web\View::POS_READY);


?> -->




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
        // [
        //     'class' => 'kartik\grid\CheckboxColumn',
        //     'checkboxOptions' => function ($model, $key, $index, $column) {
        //         return ['value' => $model->id, 'name' => 'seleccionar[]']; // Asegúrate de que 'id' sea la clave primaria
        //     },
        // ],
        // 'id',
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
            'attribute' => 'idItem',
            'value' => function ($model) {
                return $model->item->item;
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
            'attribute' => 'idEstado',
            'contentOptions' => ['data-cellvalue' => 'idEstado',],
            'filter' => Estadotraspaso::getListaData(),
            'value' => function ($model) {
                // Obtiene el nombre del estado de la planilla y el estado general
                $estado = $model->traspaso->planillaembarquetraspaso ? $model->traspaso->planillaembarquetraspaso->estadoPlanilla->nombre : '';
                $estadoPlanilla = $model->traspaso->estado ? $model->traspaso->estado->nombre : '';

                // Concatenar los dos estados (si ambos existen)
                return $estadoPlanilla && $estado ? $estadoPlanilla . ' / ' . $estado : $estadoPlanilla . $estado;
            },
        ],
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

            <?php if (Yii::$app->user->identity->username == 'victor.burbano'): ?>

                <!-- Botón para cambiar el estado de los registros -->
                <?= Html::button('Muelle-ajax', [
                    'class' => 'btn btn-warning btn-create btn-lg',
                    'id' => 'cambiarEstadoBtn',
                ]) ?>



            <?php endif; ?>

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

        'columns' => array_merge(
            [
                ['class' => 'kartik\grid\SerialColumn'],
            ],
            $gridColumns,
        ),
    ]);
    ?>


</div>