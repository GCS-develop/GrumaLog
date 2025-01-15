<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Impresora $model */

$this->title = 'Create Impresora';
$this->params['breadcrumbs'][] = ['label' => 'Impresoras', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="impresora-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
