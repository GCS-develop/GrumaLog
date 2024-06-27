<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Empleadologistica $model */

$this->title = 'Update Empleadologistica: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Empleadologisticas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="empleadologistica-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
