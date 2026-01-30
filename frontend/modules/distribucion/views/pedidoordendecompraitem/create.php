<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Pedidoordendecompraitem $model */

$this->title = 'Create Pedidoordendecompraitem';
$this->params['breadcrumbs'][] = ['label' => 'Pedidoordendecompraitems', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pedidoordendecompraitem-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
