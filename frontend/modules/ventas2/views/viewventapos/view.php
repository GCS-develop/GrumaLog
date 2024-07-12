<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Viewventapos $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Viewventapos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="viewventapos-view">

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
            'codigoCentroOperacion',
            'nombreCentroOperacion',
            'fecha',
            'item',
            'referencia',
            'descripcion',
            'color',
            'talla',
            'subtotal',
            'proveedor',
            'nombreProveedor',
            'unidades',
            'preciounitario',
            'total',
        ],
    ]) ?>

</div>
