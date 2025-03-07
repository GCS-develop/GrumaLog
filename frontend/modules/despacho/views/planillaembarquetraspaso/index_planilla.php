<?php

use frontend\models\Planillaembarquetraspaso;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/** @var yii\web\View $this */
/** @var frontend\models\search\PlanillaembarquetraspasoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


$this->title = ' Detalle de planilla: ' . $model->id . ' | usuario: ' . $model->usuario->username . ' | Sello inicial: ' . $model->sello
    . ' |  ' . $model->estado->nombre;

$this->params['breadcrumbs'][] = ['label' => 'Planilla Embarque', 'url' => ['/despacho/planillaembarque/index']];
$this->params['breadcrumbs'][] = $this->title;

// $this->title = 'Detalle planilla ' . ' - usuario : ' . $usuarioCreador;


$fecha_actual = date("Y-m-d");
$filename = "Relacion_PlanillaEmbarque_" . $fecha_actual;

?>

<link rel="stylesheet" href="css/shared.css">

<?php
$gridColumns = [
    // 'id',
    // 'idPlanillaEmbarque',
    // 'idTraspaso',
    // 'usuarioCreador',
    'usuarioPlanilla',
    'codAlmacenOrigen',
    'almacenOrigen',
    'codAlmacenDestino',
    'almacenDestino',
    [
        'attribute' => 'tipoDocumento',
        'contentOptions' => ['data-cellvalue' => 'tipoDocumento'],
        'value' => function ($model) {
            return $model->tipoDocumento ? $model->tipoDocumento : $model->tipoDocumentoInterno;
        },
    ],
    [
        'attribute' => 'consecutivoDocumento',
        'contentOptions' => ['data-cellvalue' => 'consecutivoDocumento'],
        'value' => function ($model) {
            return $model->consecutivoDocumento ? $model->consecutivoDocumento : $model->consecutivoInterno;
        },
    ],
    [
        'attribute' => 'fechaTraspaso',
        'contentOptions' => ['data-cellvalue' => 'fechaTraspaso'],
        'value' => function ($model) {
            return Yii::$app->formatter->asDate($model->fechaTraspaso, 'php:Y-m-d');
        },
    ],
    [
        'attribute' => 'horaTraspaso',
        'contentOptions' => ['data-cellvalue' => 'horaTraspaso'],
        'value' => function ($model) {
            return Yii::$app->formatter->asDate($model->fechaTraspaso, 'php:H:i:s');
        },
    ],

    // 'fechaPlanillaembarque',
    // 'horaPlanillaembarque',

    [
        'label' => 'Fecha.Des',
        'attribute' => 'created_at',
        'contentOptions' => ['data-cellvalue' => 'created_at'],
        'value' => function ($model) {
            return Yii::$app->formatter->asDate($model->created_at, 'php:Y-m-d');
        },
    ],
    [
        'label' => 'Hora.Des',
        'attribute' => 'created_at',
        'contentOptions' => ['data-cellvalue' => 'created_at'],
        'value' => function ($model) {
            return Yii::$app->formatter->asDatetime(strtotime($model->created_at), 'php:H:i:s');

        },
    ],
    [
        'attribute' => 'unidades',
        'contentOptions' => ['data-cellvalue' => 'unidades',],
        'format' => ['decimal', 0], // Formato decimal con 0 decimales,
        'pageSummary' => true,
    ],
    // 'unidadesEmp',
    [
        'attribute' => 'unidadesEmp',
        'contentOptions' => ['data-cellvalue' => 'unidadesEmp',],
        'format' => ['decimal', 0], // Formato decimal con 0 decimales,
        'pageSummary' => true,
    ],
    'conductor',
    'estado',

    'fechaRecibido',
    //   'horaRecibido',
    'usuarioRecibido',
    [
        'label' => 'Planilla',
        'value' => function ($model) {
            return ($model->codAlmacenOrigen) . '-' . ($model->planillaEmbarque->id);
        },

    ],

    'orden',
    'selloLlegada',
    'selloSalida',

    // 'fechaRecibido',// Fecha recibido transito : es para los documentos TRT o documentos elaborados para traslados entre tiendas ,este sector es para el recibo de estos documentos en transito CEDI , seguido a este se cambia a despachado CEDI ,asi se identifican los docuemtos elaborados para nivelacion de producto entre tiendas .

    // 'horaRecibido',// Hora recibido transito  : indica la hora en que los documentos en trasito fueron recibidos en el CEDI por el auxiliar administrativo de transporte. 

    // 'planillaTransito ',// Planilla transito     : planilla que se elabora para el envío nuevamente desde el CEDI de los documentos TRT o traslados entre tiendas recibidos en trasito CEDI


];


?>


<div class="planillaembarquetraspaso-index">


    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

    <div class="row">

        <div class="col-lg-12 centrar">

            <div class="col-lg-12 centrar">
                <!-- <h3><?php var_dump($model->usuario->username) ?></h3> -->
            </div>

        </div>

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
        'rowOptions' => function ($model) {
            $classes = [];
            if ($model->estado === 'Recibido') {
                $classes[] = 'text-success';
            }
            if ($model->estado === 'Anulado') {
                $classes[] = 'text-danger';
            }
            if ($model->estado === 'Sin Enviar') {
                $classes[] = 'text-primary';
            }
            return ['class' => implode(' ', $classes)];
        },

        'columns' => array_merge(
            [
                ['class' => 'kartik\grid\SerialColumn'],
            ],
            $gridColumns,
        ),


    ]); ?>



</div>