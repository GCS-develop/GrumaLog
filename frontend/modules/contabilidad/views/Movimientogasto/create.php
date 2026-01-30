<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\MovimientoGasto $model */

$this->title = 'Create Movimiento Gasto';
$this->params['breadcrumbs'][] = ['label' => 'Movimiento Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="movimiento-gasto-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
