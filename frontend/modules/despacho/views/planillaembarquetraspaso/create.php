<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Planillaembarquetraspaso $model */

$this->title = 'Create Planillaembarquetraspaso';
$this->params['breadcrumbs'][] = ['label' => 'Planillaembarquetraspasos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="planillaembarquetraspaso-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
