<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Planillaembarque $model */

$this->title = 'Create Planilla embarque';
$this->params['breadcrumbs'][] = ['label' => 'Planilla embarque', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="planillaembarque-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
