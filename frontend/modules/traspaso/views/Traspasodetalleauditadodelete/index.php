<?php

use frontend\models\Traspasodetalleauditadodelete;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\widgets\Alert;
use yii\bootstrap4\Modal;

/** @var yii\web\View $this */
/** @var frontend\models\search\Traspasodetalleauditadodeletesearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Traspasodetalleauditadodeletes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="traspasodetalleauditadodelete-index">

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