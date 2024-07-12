<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Facturadetalle $model */

$this->title = 'Actualizar';
$this->params['breadcrumbs'][] = ['label' => 'Items de Factura', 'url' => ['index', 'idfactura' => $model->idFactura]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="facturadetalle-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
