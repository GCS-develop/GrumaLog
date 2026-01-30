<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\ventasimportadas $model */

$this->title = 'Create Ventasimportadas';
$this->params['breadcrumbs'][] = ['label' => 'Ventasimportadas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ventasimportadas-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
