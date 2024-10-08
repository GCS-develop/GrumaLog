<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Categoria $model */

$this->title = 'Create Categoria';
$this->params['breadcrumbs'][] = ['label' => 'Categorias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="categoria-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
