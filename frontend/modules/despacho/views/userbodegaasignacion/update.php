<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Userbodegaasignacion $model */

$this->title = 'Update Userbodegaasignacion: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Userbodegaasignacions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="userbodegaasignacion-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
