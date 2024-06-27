<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Transferencia $model */

$this->title = 'Update Transferencia: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Transferencias', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="transferencia-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
