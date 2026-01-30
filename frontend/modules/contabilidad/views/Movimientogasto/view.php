<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var frontend\models\MovimientoGasto $model */

$this->title = $model->F_CIA;
$this->params['breadcrumbs'][] = ['label' => 'Movimiento Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="movimiento-gasto-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'F_CIA' => $model->F_CIA, 'F350_CONSEC_DOCTO' => $model->F350_CONSEC_DOCTO, 'F350_ID_CO' => $model->F350_ID_CO], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'F_CIA' => $model->F_CIA, 'F350_CONSEC_DOCTO' => $model->F350_CONSEC_DOCTO, 'F350_ID_CO' => $model->F350_ID_CO], [
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
            'F_CIA',
            'F350_ID_CO',
            'F350_ID_TIPO_DOCTO',
            'F350_CONSEC_DOCTO',
            'F351_ID_AUXILIAR',
            'F351_ID_TERCERO',
            'F351_ID_CO_MOV',
            'F351_ID_UN',
            'F351_ID_CCOSTO',
            'F351_ID_FE',
            'F351_VALOR_DB',
            'F351_VALOR_CR',
            'F351_BASE_GRAVABLE',
            'F351_DOCTO_BANCO',
            'F351_NRO_DOCTO_BANCO',
            'F351_NOTAS:ntext',
        ],
    ]) ?>

</div>
