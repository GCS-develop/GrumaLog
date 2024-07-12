<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Facturaitem $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Facturaitems', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="facturaitem-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'idFactura',
            'codigoBarra',
            'item',
            'referencia',
            'descripcion',
            'color',
            'talla',
            'precioUnitario',
            'totalUnidadesFactura',
            'totalUnidadesSiesa',
        ],
    ]) ?>

</div>
