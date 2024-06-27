<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Subcategoria $model */

$this->title = 'Update Subcategoria: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Subcategorias', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="subcategoria-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
