<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Bodegatipodocumento $model */

$this->title = 'Actualizar Bodega tipo documento: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Bodega tipo documentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="bodegatipodocumento-update">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
