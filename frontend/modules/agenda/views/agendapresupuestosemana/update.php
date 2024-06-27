<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Agendapresupuestosemana $model */

$this->title = 'Update Agendapresupuestosemana: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Agendapresupuestosemanas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="agendapresupuestosemana-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
