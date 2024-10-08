<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Bodegas $model */

$this->title = $model->Codigo_Bodega;
$this->params['breadcrumbs'][] = ['label' => 'Bodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="bodegas-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'Codigo_Bodega' => $model->Codigo_Bodega], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'Codigo_Bodega' => $model->Codigo_Bodega], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'Codigo_Bodega',
            'Descripcion_Bodega',
        ],
    ]) ?>

</div>
