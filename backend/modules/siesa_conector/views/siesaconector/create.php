<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Siesaconector $model */

$this->title = 'Create Siesaconector';
$this->params['breadcrumbs'][] = ['label' => 'Siesaconectors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="siesaconector-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
