<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Planillaembarque $model */

$this->title = 'Actualizar Planilla embarque: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Planilla embarques', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="planillaembarque-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
