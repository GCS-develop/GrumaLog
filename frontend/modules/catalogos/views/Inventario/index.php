<?php

use frontend\models\Inventario;
use frontend\models\Item;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\InventarioSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Inventarios';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="inventario-index">

    <!-- <p>
        <?= Html::a('Create Inventario', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php
    $totalCantidadSiesa = (int) Inventario::getTotalExistenciasSiesa($searchModel->codigoBodega);
    $totalCantidadGruma = (int) Inventario::getotalExistenciasGruma($searchModel->codigoBodega);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'beforeRow' => function ($model, $key, $index, $grid) use ($totalCantidadSiesa, $totalCantidadGruma) {
            if ($index === 0) { // Primera fila de cada página
                return "<tr>
                    <td colspan='7'><strong>Gran Total</strong></td>
                    <td><strong>$totalCantidadGruma</strong></td>
                    <td><strong>$totalCantidadSiesa</strong></td>
                </tr>";
            }
        },


        'showPageSummary' => true,
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],
            'id',
            'item',
            'codigoBarras',
            'color',
            'talla',
            'codigoBodega',
            [
                'label' => 'Existencia grumalog',
                'attribute' => 'existencia',
                'value' => function ($model) {
                    return $model->existencia;
                },
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],
            [
                'label' => 'unidades inventario Siesa',
                'value' => function ($model) {
                    return Item::getInventario($model->codigoBarras, $model->codigoBodega);
                },
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],            //'fechaUltimaActualizacion',
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            // [
            //     'class' => ActionColumn::className(),
            //     'urlCreator' => function ($action, Inventario $model, $key, $index, $column) {
            //         return Url::toRoute([$action, 'id' => $model->id]);
            //      }
            // ],
        ],
    ]); ?>


</div>