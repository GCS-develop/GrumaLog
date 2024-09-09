<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Traspasouserbodega $model */

$this->title = 'Crear Traspaso user bodega';
$this->params['breadcrumbs'][] = ['label' => 'Traspasouserbodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="traspasouserbodega-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
