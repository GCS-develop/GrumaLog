<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Crossdocking $model */

$this->title = 'Create Crossdocking';
$this->params['breadcrumbs'][] = ['label' => 'Crossdockings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="crossdocking-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
