<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Condicionpago $model */

$this->title = 'Update Condicionpago: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Condicionpagos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="condicionpago-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
