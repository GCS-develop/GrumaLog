<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Pedido $model */

$this->title = 'Create Pedido';
$this->params['breadcrumbs'][] = ['label' => 'Pedidos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pedido-create">

    <?= $this->render('_form_archivo', [
        'model' => $model,
    ]) ?>

</div>
