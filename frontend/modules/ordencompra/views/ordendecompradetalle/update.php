<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Ordendecompradetalle $model */

$this->title = 'Update Ordendecompradetalle: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ordendecompradetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ordendecompradetalle-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
