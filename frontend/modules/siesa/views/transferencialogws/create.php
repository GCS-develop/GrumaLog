<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Transferencialogws $model */

$this->title = 'Create Transferencialogws';
$this->params['breadcrumbs'][] = ['label' => 'Transferencialogws', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transferencialogws-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
