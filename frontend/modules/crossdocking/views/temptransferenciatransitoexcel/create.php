<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Temptransferenciatransitoexcel $model */

$this->title = 'Create Temptransferenciatransitoexcel';
$this->params['breadcrumbs'][] = ['label' => 'Temptransferenciatransitoexcels', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="temptransferenciatransitoexcel-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
