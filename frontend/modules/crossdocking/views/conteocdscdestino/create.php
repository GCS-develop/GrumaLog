<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conteocdscdestino $model */

$this->title = 'Create Conteocdscdestino';
$this->params['breadcrumbs'][] = ['label' => 'Conteocdscdestinos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conteocdscdestino-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
