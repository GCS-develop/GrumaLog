<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\SiesaConectorDocumento $model */

$this->title = 'Update Siesa Conector Documento: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Siesa Conector Documentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="siesa-conector-documento-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
