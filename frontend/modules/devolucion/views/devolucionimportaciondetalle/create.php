<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Devolucionimportaciondetalle $model */

$this->title = 'Create Devolucionimportaciondetalle';
$this->params['breadcrumbs'][] = ['label' => 'Devolucionimportaciondetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="devolucionimportaciondetalle-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
