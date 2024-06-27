<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Bodegas $model */

$this->title = 'Create Bodegas';
$this->params['breadcrumbs'][] = ['label' => 'Bodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodegas-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
