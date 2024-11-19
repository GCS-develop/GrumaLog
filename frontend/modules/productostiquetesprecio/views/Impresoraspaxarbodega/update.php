<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Impresoraspaxarbodega $model */

$this->title = 'Update Impresoraspaxarbodega: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Impresoraspaxarbodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="impresoraspaxarbodega-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
