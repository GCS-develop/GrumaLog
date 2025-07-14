<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\SiesaConectorDocumentoCampo $model */

$this->title = 'Update Siesa Conector Documento Campo: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Siesa Conector Documento Campos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="siesa-conector-documento-campo-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
