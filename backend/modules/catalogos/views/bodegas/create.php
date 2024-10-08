<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Bodegas $model */

$this->title = 'Create Bodegas';
$this->params['breadcrumbs'][] = ['label' => 'Bodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodegas-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
