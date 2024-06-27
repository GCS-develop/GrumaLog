<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Userbodega $model */

$this->title = 'Create Userbodega';
$this->params['breadcrumbs'][] = ['label' => 'Userbodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="userbodega-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
