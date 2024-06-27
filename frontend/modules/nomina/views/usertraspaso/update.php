<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Usertraspaso $model */

$this->title = 'Update Usertraspaso: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Usertraspasos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="usertraspaso-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
