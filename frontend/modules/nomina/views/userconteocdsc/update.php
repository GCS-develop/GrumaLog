<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Userconteocdsc $model */

$this->title = 'Update Userconteocdsc: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Userconteocdscs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="userconteocdsc-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
