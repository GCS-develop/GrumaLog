<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Transferenciaordencompraexcel $model */

$this->title = 'Update Transferenciaordencompraexcel: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Transferenciaordencompraexcels', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="transferenciaordencompraexcel-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
