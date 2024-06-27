<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Agendapresupuestosemana $model */

$this->title = 'Create Agendapresupuestosemana';
$this->params['breadcrumbs'][] = ['label' => 'Agendapresupuestosemanas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agendapresupuestosemana-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
