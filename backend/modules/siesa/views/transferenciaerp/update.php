<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Transferenciaerp $model */

$this->title = 'Update Transferenciaerp: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Transferenciaerps', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="transferenciaerp-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
