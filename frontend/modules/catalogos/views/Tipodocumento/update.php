<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Tipodocumento $model */

$this->title = 'Update Tipodocumento: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Tipodocumentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="tipodocumento-update">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
