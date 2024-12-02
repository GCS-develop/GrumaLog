<?php

use frontend\models\Traspasodetalle;
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
<div class="traspasodetalle-index">

    <h1>
        <?php if ($traspaso): ?>
            <?= Html::encode($traspaso->tipodocumento->codigo . '-' . $traspaso->consecutivo . '  Estado: ' . $traspaso->estado->nombre) ?>
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

        // 'id',
        'idTraspaso',
        [
            'label' => 'Traspaso',
            'value' => function ($model) {
                return $model->traspaso->tipodocumento->codigo . '-' . $model->traspaso->consecutivo;
            },
        ],
        [
            'attribute' => 'idItem',
            'value' => function ($model) {
                return $model->item->item;
            },
            'enableSorting' => true,
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

    <!-- <?php echo $this->render('_search', ['model' => $searchModel]); ?> -->

    <?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

    <div class="row">

        <!-- <div class="col-lg-12 centrar">
        <h3><?php var_dump($usuarioCreador) ?></h3>
    </div> -->

        <div class="col-lg-12 centrar">


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