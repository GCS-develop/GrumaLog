<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Estadoagenda $model */

$this->title = 'Update Estadoagenda: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Estadoagendas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="estadoagenda-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
