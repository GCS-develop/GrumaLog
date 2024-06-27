<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Userbodega $model */

$this->title = 'Update Userbodega: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Userbodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="userbodega-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
