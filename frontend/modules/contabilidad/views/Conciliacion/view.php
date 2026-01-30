<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var frontend\models\ventasimportadas $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ventasimportadas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="ventasimportadas-view">

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
            'rowid_item_ext',
            'item',
            'codigobarra',
            'descripcion',
            'descripcionCorta',
            'color',
            'talla',
            'referencia',
            'nombreproveedor',
            'proveedor',
            'unidades',
            'precio_aplicado',
            'total',
            'costo_prom_tot',
            'costo_prom_mp',
            'factor',
            'unidadmedida',
            'created_at',
        ],
    ]) ?>

</div>
