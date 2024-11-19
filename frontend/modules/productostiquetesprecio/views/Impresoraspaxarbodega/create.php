<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Impresoraspaxarbodega $model */

$this->title = 'Create Impresoraspaxarbodega';
$this->params['breadcrumbs'][] = ['label' => 'Impresoraspaxarbodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="impresoraspaxarbodega-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
