<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Agendapresupuesto $model */

$this->title = 'Update Agendapresupuesto: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Agendapresupuestos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="agendapresupuesto-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
