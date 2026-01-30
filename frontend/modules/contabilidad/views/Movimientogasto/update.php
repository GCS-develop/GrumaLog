<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\MovimientoGasto $model */

$this->title = 'Update Movimiento Gasto: ' . $model->F_CIA;
$this->params['breadcrumbs'][] = ['label' => 'Movimiento Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->F_CIA, 'url' => ['view', 'F_CIA' => $model->F_CIA, 'F350_CONSEC_DOCTO' => $model->F350_CONSEC_DOCTO, 'F350_ID_CO' => $model->F350_ID_CO]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="movimiento-gasto-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
