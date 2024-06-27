<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Crossdocking $model */

$this->title = 'Update Crossdocking: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Crossdockings', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="crossdocking-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
