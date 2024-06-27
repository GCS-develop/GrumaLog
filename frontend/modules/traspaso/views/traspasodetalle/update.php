<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Traspasodetalle $model */

$this->title = 'Update Traspasodetalle: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Traspasodetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="traspasodetalle-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
