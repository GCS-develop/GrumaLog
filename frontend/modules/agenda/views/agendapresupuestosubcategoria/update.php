<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Agendapresupuestosubcategoria $model */

$this->title = 'Update Agendapresupuestosubcategoria: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Agendapresupuestosubcategorias', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="agendapresupuestosubcategoria-update">

    <?= $this->render('_form', [
        'model' => $model,
        'modelAgenda' => $modelAgenda,
    ]) ?>

</div>
