<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Bodegatipodocumento $model */

$this->title = 'Create Bodegatipodocumento';
$this->params['breadcrumbs'][] = ['label' => 'Bodegatipodocumentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodegatipodocumento-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
