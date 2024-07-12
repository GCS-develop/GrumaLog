<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Factura $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Facturas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="factura-view">

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
            'centroOperacion',
            'tipoDocumento',
            'consecutivoDocumento',
            'fechaDocumento',
            'codigoProveedor',
            'documentoProveedor',
            'codigoSucursal',
            'prefijoDocumentoProveedor',
            'consecutivoDocumentoProveedor',
            'fechaDocumentoProveedor',
            'condicionPago',
            'tipoProveedor',
            'valorDocumento',
            'porcentajeCuota',
            'fechaVencimientoCuota',
            'fechaProntoPago',
            'fechaDesde',
            'fechaHasta',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ],
    ]) ?>

</div>
