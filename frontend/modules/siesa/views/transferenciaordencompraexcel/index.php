<?php

use frontend\models\Transferenciaordencompraexcel;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\TransferenciaordencompraexcelSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Transferenciaordencompraexcels';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transferenciaordencompraexcel-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Transferenciaordencompraexcel', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'idTransferenciaerp',
            'centroOperacionDocumento',
            'tipoDocumento',
            'consecutivoDocumento',
            //'fechaDocumento',
            //'tercero',
            //'numeroFactura',
            //'sucursal',
            //'idTerceroComprador',
            //'consignacion',
            //'centroOperacionOrdenCompra',
            //'tipoDocumentoOrdenCompra',
            //'consecutivoOrdenCompra',
            //'centroOperacionMovimiento',
            //'tipoDocumentoMovimiento',
            //'consecutivoMovimiento',
            //'numeroRegistroMovimiento',
            //'bodegaMovimiento',
            //'unidadMovimiento',
            //'fechaEntregaMovimiento',
            //'cantidadBase',
            //'item',
            //'color',
            //'talla',
            //'rowid',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Transferenciaordencompraexcel $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
