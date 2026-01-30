<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\DocumentoGasto $model */

$this->title = 'Update Documento Gasto: ' . $model->F350_ID_CO;
$this->params['breadcrumbs'][] = ['label' => 'Documento Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->F350_ID_CO, 'url' => ['view', 'F350_ID_CO' => $model->F350_ID_CO]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="documento-gasto-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
