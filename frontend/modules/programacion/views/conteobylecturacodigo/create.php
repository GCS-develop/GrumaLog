<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conteobylecturacodigo $model */

$this->title = 'Create Conteobylecturacodigo';
$this->params['breadcrumbs'][] = ['label' => 'Conteobylecturacodigos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conteobylecturacodigo-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
