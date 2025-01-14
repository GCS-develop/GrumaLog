<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Ordendecompra $model */

$this->title = 'Update Ordendecompra: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ordendecompras', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ordendecompra-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
