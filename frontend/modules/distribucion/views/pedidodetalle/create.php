<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Pedidodetalle $model */

$this->title = 'Create Pedidodetalle';
$this->params['breadcrumbs'][] = ['label' => 'Pedidodetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pedidodetalle-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
