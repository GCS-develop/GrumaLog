<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var frontend\models\Devolucionimportaciondetalle $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Devolucionimportaciondetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="devolucionimportaciondetalle-view">

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
            'idInterfase',
            'co',
            'fecha',
            'bodegaSalida',
            'item',
            'talla',
            'color',
            'numeroDocumento',
            'notasDocumento',
            'bodegaEntrada',
            'codigoBodegaEntrada',
            'codigoBodegaSalida',
            'referencia',
            'itemResumen',
            'unidadMedida',
            'cantidad',
            'categoria',
            'proveedor',
            'codigoBarras',
        ],
    ]) ?>

</div>
