<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Transferenciatransitoexcel $model */

$this->title = 'Update Transferenciatransitoexcel: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Transferenciatransitoexcels', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="transferenciatransitoexcel-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
