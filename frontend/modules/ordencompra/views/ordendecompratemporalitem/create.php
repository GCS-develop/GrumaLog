<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Ordendecompratemporalitem $model */

$this->title = 'Create Ordendecompratemporalitem';
$this->params['breadcrumbs'][] = ['label' => 'Ordendecompratemporalitems', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ordendecompratemporalitem-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
