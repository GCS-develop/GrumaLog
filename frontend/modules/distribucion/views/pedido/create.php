<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Pedido $model */

$this->title = 'Registrar Pedido';
$this->params['breadcrumbs'][] = ['label' => 'Pedidos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pedido-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
