<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conductor $model */

$this->title = 'Create Conductor';
$this->params['breadcrumbs'][] = ['label' => 'Conductors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conductor-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
