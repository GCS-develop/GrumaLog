<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Empleadologistica $model */

$this->title = 'Create Empleadologistica';
$this->params['breadcrumbs'][] = ['label' => 'Empleadologisticas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="empleadologistica-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
