<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Planillaembarquetraspaso $model */

$this->title = 'Update Planillaembarquetraspaso: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Planillaembarquetraspasos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="planillaembarquetraspaso-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
