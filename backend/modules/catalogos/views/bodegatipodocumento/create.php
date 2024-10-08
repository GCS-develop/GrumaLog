<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Bodegatipodocumento $model */

$this->title = 'Crear bodega tipo documento';
$this->params['breadcrumbs'][] = ['label' => 'Bodegatipodocumentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodegatipodocumento-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
