<?php

use frontend\models\MovimientoGasto;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\MovimentogastoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Movimiento Gastos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="movimiento-gasto-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Movimiento Gasto', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'F_CIA',
            'F350_ID_CO',
            'F350_ID_TIPO_DOCTO',
            'F350_CONSEC_DOCTO',
            'F351_ID_AUXILIAR',
            //'F351_ID_TERCERO',
            //'F351_ID_CO_MOV',
            //'F351_ID_UN',
            //'F351_ID_CCOSTO',
            //'F351_ID_FE',
            //'F351_VALOR_DB',
            //'F351_VALOR_CR',
            //'F351_BASE_GRAVABLE',
            //'F351_DOCTO_BANCO',
            //'F351_NRO_DOCTO_BANCO',
            //'F351_NOTAS:ntext',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, MovimientoGasto $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'F_CIA' => $model->F_CIA, 'F350_CONSEC_DOCTO' => $model->F350_CONSEC_DOCTO, 'F350_ID_CO' => $model->F350_ID_CO]);
                 }
            ],
        ],
    ]); ?>


</div>
