<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Userdespacho $model */

$this->title = 'Create Userdespacho';
$this->params['breadcrumbs'][] = ['label' => 'Userdespachos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="userdespacho-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
