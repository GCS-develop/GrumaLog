<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var frontend\models\Ordendecompra $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ordendecompras', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="ordendecompra-view">

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
            'idCO',
            'idTipoDocumento',
            'consecutivo',
            'fecha',
            'idProveedor',
            'idEstado',
            'fechaEntrega',
            'totalCantidadPedida',
            'totalCantidadEntrada',
            'totalCantidadPendiente',
            'nroPaquetes',
            'comprador',
            'nitcomprador',
            'sucursalProveedor',
            'idTipoDocumentoEntrada',
            'idCODocumentoEntrada',
            'fechaDocumentoEntrada',
            'consecutivoDocumentoEntrada',
            'consignacion',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ],
    ]) ?>

</div>
