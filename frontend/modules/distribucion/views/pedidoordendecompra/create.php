<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Pedidoordendecompra $model */

$this->title = 'Registrar Orden de Compra';
$this->params['breadcrumbs'][] = ['label' => 'Pedidoordendecompras', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pedidoordendecompra-create">

    <?= $this->render('_form', [
        'model' => $model,
        'fileForm' => $fileForm,
    ]) ?>

</div>
