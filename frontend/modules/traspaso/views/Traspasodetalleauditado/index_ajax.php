<?php
$this->registerCss('
    .mi-gridview {
        font-size: 12px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

    .btn-create {
        width: 300px;
    }
    
    .centrar {
        text-align: center;
    }
');

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
use frontend\models\Traspasodetalleauditado;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

use common\widgets\Alert;
use yii\bootstrap4\Modal;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var frontend\models\search\TraspasodetalleauditadoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$fecha_actual = date("Y-m-d");
$filename = "Relacion_Traspaso_Auditado_" . $fecha_actual;

$this->title = 'Traspasodetalleauditados';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);


$gridColumns = [
    // 'id',
    // 'idTraspaso',
    'consecutivoSiesa',
    'item',
    'talla',
    'color',
    // [
    //     'attribute' => 'Registros auditados',
    //     'contentOptions' => ['data-cellvalue' => 'registros',],
    //     'value' => function ($model) {
    //         return $model->cantidad;
    //     },
    //     'format' => ['decimal', 0], // Formato decimal con 0 decimales,
    //     'pageSummary' => true,

    // ],
    // [
    //     'attribute' => 'Registros traspaso',
    //     'contentOptions' => ['data-cellvalue' => 'registros',],
    //     'value' => function ($model) {
    //         return $model->cantidadTraspasoRegistros;
    //     },
    //     'format' => ['decimal', 0], // Formato decimal con 0 decimales,
    //     'pageSummary' => true,

    // ],
    [
        'attribute' => 'Unidades auditados',
        'contentOptions' => ['data-cellvalue' => 'registros',],
        'value' => function ($model) {
            return $model->unidades;
        },
        'format' => ['decimal', 0], // Formato decimal con 0 decimales,
        'pageSummary' => true,

    ],
    [
        'attribute' => 'Unidades traspasos',
        'contentOptions' => ['data-cellvalue' => 'registros',],
        'value' => function ($model) {
            return $model->cantidadTraspasounidades;
        },
        'format' => ['decimal', 0], // Formato decimal con 0 decimales,
        'pageSummary' => true,

    ],
    'diferencia',
    // 'nombreCreo',
    // 'created_at',
    'userTraspaso',
    'nombreActualizo',
    'updated_at',

];
?>

<link rel="stylesheet" href="css/shared.css">

<?php
Modal::begin([
    'title' => '<h4>Traspaso detalle auditado ELIMINADOS</h4>',
    'id' => 'modaldata2',
    'size' => 'modal-xl',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData2'></div>";

Modal::end();
?>

<div class="traspasodetalleauditado-index">
    <div class="row">

        <h1 class="col-lg-12 centrar"> Traspaso Detalle Auditado </h1>

        <div class="col-lg-12 centrar">
            <?php
            $url = Url::to([
                '/traspaso/traspasodetalleauditadodelete/index',
                'idtraspaso' => $idtraspaso
            ]);
            ?>

            <p>
                <?= Html::button(
                    'Ver eliminados',
                    [
                        'value' => $url,
                        'class' => 'btn btn-success btn-lg btn-create',
                        'id' => 'modalButtonCreateEliminados'
                    ]
                )
                    ?>
            </p>
        </div>

        <div class="col-lg-12">

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                // 'filterModel' => $searchModel,
                'showPageSummary' => true,
                'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                'options' => [
                    'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
                ],
                'emptyText' => 'No se encontraron registros auditados para este traspaso.',
                'columns' => array_merge(
                    [
                        ['class' => 'kartik\grid\SerialColumn'],
                    ],

                    $gridColumns,

                ),
            ]); ?>

        </div>
    </div>