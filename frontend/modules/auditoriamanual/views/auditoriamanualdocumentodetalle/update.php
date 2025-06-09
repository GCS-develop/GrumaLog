<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Auditoriamanualdocumentodetalle $model */

$this->title = 'Update auditoria manual documentodetalle: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'auditoria manual documentodetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="auditoriamanualdocumentodetalle-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
