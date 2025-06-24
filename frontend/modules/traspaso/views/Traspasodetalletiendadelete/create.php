<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Traspasodetalletiendadelete $model */

$this->title = 'Create Traspasodetalletiendadelete';
$this->params['breadcrumbs'][] = ['label' => 'Traspasodetalletiendadeletes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="traspasodetalletiendadelete-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
