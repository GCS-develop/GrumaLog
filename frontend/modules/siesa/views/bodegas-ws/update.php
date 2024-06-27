<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Bodegas $model */

$this->title = 'Update Bodegas: ' . $model->Codigo_Bodega;
$this->params['breadcrumbs'][] = ['label' => 'Bodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->Codigo_Bodega, 'url' => ['view', 'Codigo_Bodega' => $model->Codigo_Bodega]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="bodegas-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
