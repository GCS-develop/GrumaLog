<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Empleado $model */

$this->title = 'Actualizar Empleado: ' . $model->identificacion;
$this->params['breadcrumbs'][] = ['label' => 'Empleados', 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="empleado-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
