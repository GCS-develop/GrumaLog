<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\ventasimportadas $model */

$this->title = 'Update Ventasimportadas: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ventasimportadas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ventasimportadas-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
