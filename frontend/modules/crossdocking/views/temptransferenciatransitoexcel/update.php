<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Temptransferenciatransitoexcel $model */

$this->title = 'Update Temptransferenciatransitoexcel: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Temptransferenciatransitoexcels', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="temptransferenciatransitoexcel-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
