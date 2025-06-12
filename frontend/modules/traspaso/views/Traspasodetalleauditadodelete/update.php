<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Traspasodetalleauditadodelete $model */

$this->title = 'Update Traspasodetalleauditadodelete: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Traspasodetalleauditadodeletes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="traspasodetalleauditadodelete-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
