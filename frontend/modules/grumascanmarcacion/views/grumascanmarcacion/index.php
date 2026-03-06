<?php

use frontend\models\Grumascanestado;
use frontend\models\Grumascanmarcacion;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\GrumascanmarcacionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Gruma scan marcaciones';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grumascanmarcacion-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->


    <?php
    echo $this->render('_search', ['model' => $searchModel]);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'idbodega',
                'value' => function ($model) {
                    return $model->bodega ? $model->bodega->nombre : 'sin marcacion';
                },
            ],
            'ubicacion',
            'seccion',
            [
                'attribute' => 'estado',
                'label' => 'Estado',
                'filter' => ['3' => 'Sin conteo'] + Grumascanestado::getListaData(),
                'value' => function ($model) {
                    return $model->estado ? $model->estado : 'Sin conteo';
                },
            ],
            'idconteo',


            'created_at',
            [
                'attribute' => 'created_by',
                'value' => function ($model) {
                    return $model->createdby ? $model->createdby->username : 'sin marcacion';
                },
            ],
            'updated_at',
            [
                'attribute' => 'updated_by',
                'value' => function ($model) {
                    return $model->updatedby ? $model->updatedby->username : 'sin marcacion';
                },
            ],
            // [
            //     'class' => ActionColumn::className(),
            //     'urlCreator' => function ($action, Grumascanmarcacion $model, $key, $index, $column) {
            //         return Url::toRoute([$action, 'id' => $model->id]);
            //     }
            // ],
        ],
    ]); ?>


</div>