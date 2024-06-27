<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Viewventapos $model */

$this->title = 'Update Viewventapos: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Viewventapos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="viewventapos-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
