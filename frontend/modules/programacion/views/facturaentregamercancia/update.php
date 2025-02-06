<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Facturaentregamercancia $model */

$this->title = 'Update Facturaentregamercancia: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Facturaentregamercancias', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="facturaentregamercancia-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
