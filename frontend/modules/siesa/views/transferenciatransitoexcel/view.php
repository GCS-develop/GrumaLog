<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var frontend\models\Transferenciatransitoexcel $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Transferenciatransitoexcels', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="transferenciatransitoexcel-view">

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
            'idTransferenciaerp',
            'centroOperacionDocumento',
            'tipoDocumento',
            'fechaDocumento',
            'bodegaSalidaDocumento',
            'bodegaEntradaDocumento',
            'centroOperacion',
            'tipoDocumentoMovimiento',
            'bodegaSalidaMovimiento',
            'centroOperacionMovimiento',
            'unidadSalida',
            'cantidadBase',
            'costoPromedioUnitario',
            'item',
            'color',
            'talla',
            'numero',
            'procesado',
            'fila',
            'notas',
            'codigoBarras',
        ],
    ]) ?>

</div>
