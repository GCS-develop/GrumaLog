<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Talla $model */

$this->title = 'Crear Talla';
$this->params['breadcrumbs'][] = ['label' => 'Tallas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="talla-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
