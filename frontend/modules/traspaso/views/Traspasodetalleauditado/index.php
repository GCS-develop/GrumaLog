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

use common\widgets\Alert;
use yii\bootstrap4\Modal;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var frontend\models\search\TraspasodetalleauditadoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Traspasodetalleauditados';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
?>

<link rel="stylesheet" href="css/shared.css">

<?php
Modal::begin([
    'title' => '<h4>Traspaso detalle auditado ELIMINADOS</h4>',
    'id' => 'modaldata2',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData2'></div>";

Modal::end();
?>

<div class="traspasodetalleauditado-index">

    <h1 class="col-log-12 centrar"> Traspaso Detalle Auditado </h1>

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
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],
            // 'id',
            // 'idTraspaso',
            'item',
            'color',
            'talla',
            [
                'attribute' => 'Cantidad registros auditados',
                'contentOptions' => ['data-cellvalue' => 'registros',],
                'value' => function ($model) {
        return $model->cantidad;
    },
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'pageSummary' => true,

            ],
            [
                'attribute' => 'Cantidad registros traspaso',
                'contentOptions' => ['data-cellvalue' => 'registros',],
                'value' => function ($model) {
        return $model->cantidadTraspasoRegistros;
    },
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'pageSummary' => true,

            ],
            [
                'attribute' => 'Cantidad unidades auditados',
                'contentOptions' => ['data-cellvalue' => 'registros',],
                'value' => function ($model) {
        return $model->unidades;
    },
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'pageSummary' => true,

            ],
            [
                'attribute' => 'Cantidad unidades traspasos',
                'contentOptions' => ['data-cellvalue' => 'registros',],
                'value' => function ($model) {
        return $model->cantidadTraspasounidades;
    },
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'pageSummary' => true,

            ],
            'created_at',
            'nombreActualizo',
            'updated_at',
            'nombreCreo',

        ],
    ]); ?>


</div>