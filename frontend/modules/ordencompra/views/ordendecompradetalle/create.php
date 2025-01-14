<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Ordendecompradetalle $model */

$this->title = 'Create Ordendecompradetalle';
$this->params['breadcrumbs'][] = ['label' => 'Ordendecompradetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ordendecompradetalle-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
