<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Impresoraspaxarbodega $model */

$this->title = 'Actualizar Impresora paxar: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Impresoras paxar', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="impresoraspaxarbodega-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
