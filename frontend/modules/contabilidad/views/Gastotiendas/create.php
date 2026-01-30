<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\DocumentoGasto $model */

$this->title = 'Create Documento Gasto';
$this->params['breadcrumbs'][] = ['label' => 'Documento Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="documento-gasto-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
