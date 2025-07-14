<?php

use frontend\models\SiesaConectorDocumentoCampo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\SiesaconectordocumentocampoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Siesa Conector Documento Campos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="siesa-conector-documento-campo-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Siesa Conector Documento Campo', ['create'], ['class' => 'btn btn-success']) ?>
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
                'urlCreator' => function ($action, SiesaConectorDocumentoCampo $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'id' => $model->id]);
                    }
            ],
        ],
    ]); ?>


</div>