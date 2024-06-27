<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Factura $model */

$this->title = 'Update Factura: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Facturas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="factura-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
