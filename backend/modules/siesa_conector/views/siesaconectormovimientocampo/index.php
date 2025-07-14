<?php

use frontend\models\SiesaConectorMovimientoCampo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\SiesaconectormovimientocampoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Siesa Conector Movimiento Campos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="siesa-conector-movimiento-campo-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Siesa Conector Movimiento Campo', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'conector_id',
                'value' => function ($model) {
                        return $model->conector->nombre;
                    },
            ],
            'nombre_campo',
            'alias',
            'tipo_dato',
            //'obligatorio',

            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, SiesaConectorMovimientoCampo $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'id' => $model->id]);
                    }
            ],
        ],
    ]); ?>


</div>