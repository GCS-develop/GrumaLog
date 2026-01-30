<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Pedidodetalle $model */

$this->title = 'Update Pedidodetalle: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Pedidodetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pedidodetalle-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
