<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Agendapresupuestocategoria $model */

$this->title = 'Create Agendapresupuestocategoria';
$this->params['breadcrumbs'][] = ['label' => 'Agendapresupuestocategorias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agendapresupuestocategoria-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
