<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Ordendecompratemporal $model */

$this->title = 'Update Ordendecompratemporal: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ordendecompratemporals', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ordendecompratemporal-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
