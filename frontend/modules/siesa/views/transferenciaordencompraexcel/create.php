<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Transferenciaordencompraexcel $model */

$this->title = 'Create Transferenciaordencompraexcel';
$this->params['breadcrumbs'][] = ['label' => 'Transferenciaordencompraexcels', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transferenciaordencompraexcel-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
