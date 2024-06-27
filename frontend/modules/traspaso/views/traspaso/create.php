<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Traspaso $model */

$this->title = 'Create Traspaso';
$this->params['breadcrumbs'][] = ['label' => 'Traspasos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="traspaso-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
