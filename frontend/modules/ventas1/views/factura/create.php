<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Factura $model */

$this->title = 'Registrar Factura';
$this->params['breadcrumbs'][] = ['label' => 'Facturas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="factura-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
