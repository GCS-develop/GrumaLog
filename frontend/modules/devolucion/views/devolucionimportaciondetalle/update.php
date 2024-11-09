<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Devolucionimportaciondetalle $model */

$this->title = 'Update Devolucionimportaciondetalle: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Devolucionimportaciondetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="devolucionimportaciondetalle-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
