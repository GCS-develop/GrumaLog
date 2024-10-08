<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Subcategoria $model */

$this->title = 'Create Subcategoria';
$this->params['breadcrumbs'][] = ['label' => 'Subcategorias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="subcategoria-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
