<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Devolucionimportacion $model */

$this->title = 'Update Devolucionimportacion: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Devolucionimportacions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="devolucionimportacion-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
