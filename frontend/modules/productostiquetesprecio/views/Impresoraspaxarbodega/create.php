<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Impresoraspaxarbodega $model */

$this->title = 'Crear Impresora paxar segun la bodega';
$this->params['breadcrumbs'][] = ['label' => 'Impresoras paxar bodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="impresoraspaxarbodega-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
