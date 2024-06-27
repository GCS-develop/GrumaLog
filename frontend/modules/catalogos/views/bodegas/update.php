<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Bodegas $model */

$this->title = 'Update Bodegas: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Bodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="bodegas-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
