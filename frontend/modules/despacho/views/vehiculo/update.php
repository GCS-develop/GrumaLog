<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Vehiculo $model */

$this->title = 'Update Vehiculo: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Vehiculos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="vehiculo-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
