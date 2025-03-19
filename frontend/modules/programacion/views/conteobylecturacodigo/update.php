<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conteobylecturacodigo $model */

$this->title = 'Update Conteobylecturacodigo: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Conteobylecturacodigos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="conteobylecturacodigo-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
