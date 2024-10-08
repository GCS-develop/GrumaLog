<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Ordendecompratemporalitem $model */

$this->title = 'Update Ordendecompratemporalitem: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ordendecompratemporalitems', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ordendecompratemporalitem-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
