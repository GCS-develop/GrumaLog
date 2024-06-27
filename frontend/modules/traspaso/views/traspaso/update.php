<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Traspaso $model */

$this->title = 'Update Traspaso: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Traspasos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="traspaso-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
