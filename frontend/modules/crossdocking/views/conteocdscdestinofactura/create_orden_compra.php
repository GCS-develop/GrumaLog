<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conteocdscdestinofactura $model */

$this->title = 'Registrar';
$this->params['breadcrumbs'][] = ['label' => 'Factura CDSC', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conteocdscdestinofactura-create">

    <?= $this->render('_form_orden_compra', [
        'model' => $model,
    ]) ?>

</div>
