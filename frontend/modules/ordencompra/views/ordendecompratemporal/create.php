<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Ordendecompratemporal $model */

$this->title = 'Create Ordendecompratemporal';
$this->params['breadcrumbs'][] = ['label' => 'Ordendecompratemporals', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ordendecompratemporal-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
