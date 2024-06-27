<?php

use frontend\modules\ventas\models\Viewventapos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\search\ViewventaposSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Viewventapos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="viewventapos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Viewventapos', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'codigoCentroOperacion',
            'nombreCentroOperacion',
            'fecha',
            'item',
            //'referencia',
            //'descripcion',
            //'color',
            //'talla',
            //'subtotal',
            //'proveedor',
            //'nombreProveedor',
            //'unidades',
            //'preciounitario',
            //'total',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Viewventapos $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
