<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Traspasouserbodega $model */

$this->title = 'Actualizar Traspaso user bodega: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Traspaso user bodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="traspasouserbodega-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
