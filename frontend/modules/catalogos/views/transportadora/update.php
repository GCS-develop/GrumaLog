<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Transportadora $model */

$this->title = 'Update Transportadora: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Transportadoras', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="transportadora-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
