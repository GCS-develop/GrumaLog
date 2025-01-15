<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Impresora $model */

$this->title = 'Update Impresora: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Impresoras', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="impresora-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
