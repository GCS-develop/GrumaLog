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

use frontend\models\Traspasodetalledelete;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\widgets\Alert;
use yii\bootstrap4\Modal;

/** @var yii\web\View $this */
/** @var frontend\models\search\TraspasodetalledeleteSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Traspasodetalledeletes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="traspasodetalledelete-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'showPageSummary' => true,
        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options' => [
            'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
        ],
        'emptyText' => 'No se encontraron registros eliminados para este traspaso.',
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],
            // 'id',
            // 'idTraspaso',
            'item',
            'color',
            'talla',
            [
                'attribute' => 'Cantidad registros',
                'contentOptions' => ['data-cellvalue' => 'registros',],
                'value' => function ($model) {
                        return $model->cantidad;
                    },
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'pageSummary' => true,

            ],
            [
                'attribute' => 'Cantidad unidades',
                'contentOptions' => ['data-cellvalue' => 'registros',],
                'value' => function ($model) {
                        return $model->unidades;
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