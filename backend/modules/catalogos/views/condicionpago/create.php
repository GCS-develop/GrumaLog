<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Condicionpago $model */

$this->title = 'Create Condicionpago';
$this->params['breadcrumbs'][] = ['label' => 'Condicionpagos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="condicionpago-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
