<?php

use frontend\models\Traspasodetalle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\TraspasodetalleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Traspaso detalle';
$this->params['breadcrumbs'][] = ['label' => 'Traspasos', 'url' => ['traspaso/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="traspasodetalle-index">

    <h1>
        <?= Html::encode($traspaso->tipodocumento->codigo . '-' . $traspaso->consecutivo . '  Estado: ' . $traspaso->estado->nombre) ?>
    </h1>
    <h2>
        <?= Html::encode($traspaso->bodegaOrigen->codigo . $traspaso->bodegaOrigen->nombre . ' - ' . $traspaso->bodegaDestino->codigo
            . $traspaso->bodegaDestino->nombre . ' Cajas: ' . $traspaso->numeroCajas) ?>
    </h2>

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
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            // 'id',
            'idTraspaso',
            // [
            //     'label' => 'Traspaso',
            //     'value' => function($model){
            //         return  ;
            //     },
            // ],
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
            // [
            //     'class' => ActionColumn::className(),
            //     'urlCreator' => function ($action, Traspasodetalle $model, $key, $index, $column) {
            //         return Url::toRoute([$action, 'id' => $model->id]);
            //      }
            // ],
        ],
    ]); ?>


</div>