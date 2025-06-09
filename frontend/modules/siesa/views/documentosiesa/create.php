<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Documentosiesa $model */

$this->title = 'Create Documentosiesa';
$this->params['breadcrumbs'][] = ['label' => 'Documentosiesas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="documentosiesa-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
