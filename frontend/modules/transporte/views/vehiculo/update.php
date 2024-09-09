<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Vehiculo $model */

$this->title = 'Actualizar Vehiculo: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Vehiculos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="vehiculo-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
