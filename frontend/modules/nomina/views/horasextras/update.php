<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Horasextras $model */

$this->title = 'Update Horasextras: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Horasextras', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="horasextras-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
