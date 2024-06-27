<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Horasextras $model */

$this->title = 'Create Horasextras';
$this->params['breadcrumbs'][] = ['label' => 'Horasextras', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="horasextras-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
