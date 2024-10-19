<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var frontend\models\Planillaembarquetraspaso $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Planillaembarquetraspasos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="planillaembarquetraspaso-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'idPlanillaEmbarque',
            'idTraspaso',
            'idBodegaOrigen',
            'idBodegaDestino',
            'unidades',
            'unidadesEmp',
            'sello',
            'fechaRecibido',
            'idUsuarioRecibido',
            'idEstado',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ],
    ]) ?>

</div>
