<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Userdespacho $model */

$this->title = 'Update Userdespacho: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Userdespachos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="userdespacho-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
