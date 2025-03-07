<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Transferenciatransitoexcel $model */

$this->title = 'Create Transferenciatransitoexcel';
$this->params['breadcrumbs'][] = ['label' => 'Transferenciatransitoexcels', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transferenciatransitoexcel-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
