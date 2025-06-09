<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Auditoriamanualimportaciondetalle $model */

$this->title = 'Create Auditoriamanualimportaciondetalle';
$this->params['breadcrumbs'][] = ['label' => 'Auditoriamanualimportaciondetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auditoriamanualimportaciondetalle-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
