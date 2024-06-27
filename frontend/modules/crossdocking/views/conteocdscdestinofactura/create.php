<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conteocdscdestinofactura $model */

$this->title = 'Create Conteocdscdestinofactura';
$this->params['breadcrumbs'][] = ['label' => 'Conteocdscdestinofacturas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conteocdscdestinofactura-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
