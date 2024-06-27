<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Userconteo $model */

$this->title = 'Update Userconteo: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Userconteos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="userconteo-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
