<?php

use frontend\models\Transferdevdocumentos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\TransferdevdocumentosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Transferdevdocumentos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transferdevdocumentos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Transferdevdocumentos', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'centro_operacion',
            'tipo_documento',
            'consecutivo_documento',
            'fecha_documento',
            //'tercero_proveedor',
            //'notas',
            //'sucursal_proveedor',
            //'comprador',
            //'consignacion',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Transferdevdocumentos $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
