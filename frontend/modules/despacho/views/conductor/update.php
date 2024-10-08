<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conductor $model */

$this->title = 'Update Conductor: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Conductors', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="conductor-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
