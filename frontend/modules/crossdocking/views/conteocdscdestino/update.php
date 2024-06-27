<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conteocdscdestino $model */

$this->title = 'Update Conteocdscdestino: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Conteocdscdestinos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="conteocdscdestino-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
