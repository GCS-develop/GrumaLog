<?php

use frontend\models\Grumascanconteo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\GrumascanconteoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Grumascanconteos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grumascanconteo-index">

    <!-- <p>
        <?= Html::a('Create Grumascanconteo', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->

    <?php echo $this->render('_search', ['model' => $searchModel]);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'idmarcacion',
                'value' => function ($model) {
                    return $model->marcacion->bodega ? $model->marcacion->bodega->nombre : 'sin marcacion';
                },
            ],
            [
                'attribute' => 'idmarcacion',
                'value' => function ($model) {
                    return $model->marcacion->ubicacion ?? 'sin ubicacion';
                },
            ],
            [
                'attribute' => 'idmarcacion',
                'value' => function ($model) {
                    return $model->marcacion->seccion ?? 'sin seccion';
                },
            ],
            [
                'attribute' => 'idmarcacion',
                'label' => 'consecutivo marcacion',
                'value' => function ($model) {
                    return $model->idmarcacion;
                },
            ],
            [
                'attribute' => 'idestado',
                'value' => function ($model) {
                    return $model->estado->nombre;
                },
            ],
            'ultimoean',
            'totalregistros',
            'totalunidades',
            [
                'attribute' => 'created_by',
                'label' => 'Usuario',
                'contentOptions' => ['data-cellvalue' => 'Usuario'],
                'value' => function ($model) {
                    return $model->usuario ? $model->usuario->username : 'Sin nombre de usuario';
                },
            ],
            'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            // [
            //     'class' => ActionColumn::className(),
            //     'urlCreator' => function ($action, Grumascanconteo $model, $key, $index, $column) {
            //         return Url::toRoute([$action, 'id' => $model->id]);
            //     }
            // ],
        ],
    ]); ?>


</div>