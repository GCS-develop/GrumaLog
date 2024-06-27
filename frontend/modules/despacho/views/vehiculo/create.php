<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Vehiculo $model */

$this->title = 'Create Vehiculo';
$this->params['breadcrumbs'][] = ['label' => 'Vehiculos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vehiculo-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
