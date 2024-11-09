<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Devoluciondocumentodetalle $model */

$this->title = 'Update Devoluciondocumentodetalle: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Devoluciondocumentodetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="devoluciondocumentodetalle-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
