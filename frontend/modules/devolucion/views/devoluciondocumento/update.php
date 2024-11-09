<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Devoluciondocumento $model */

$this->title = 'Update Devoluciondocumento: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Devoluciondocumentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="devoluciondocumento-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
