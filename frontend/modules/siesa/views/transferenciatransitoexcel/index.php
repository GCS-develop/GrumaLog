<?php

use frontend\models\Transferenciatransitoexcel;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\TransferenciatransitoexcelSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Transferenciatransitoexcels';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transferenciatransitoexcel-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Transferenciatransitoexcel', ['create'], ['class' => 'btn btn-success']) ?>
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
            'fechaDocumento',
            //'bodegaSalidaDocumento',
            //'bodegaEntradaDocumento',
            //'centroOperacion',
            //'tipoDocumentoMovimiento',
            //'bodegaSalidaMovimiento',
            //'centroOperacionMovimiento',
            //'unidadSalida',
            //'cantidadBase',
            //'costoPromedioUnitario',
            //'item',
            //'color',
            //'talla',
            //'numero',
            //'procesado',
            //'fila',
            //'notas',
            //'codigoBarras',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Transferenciatransitoexcel $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
