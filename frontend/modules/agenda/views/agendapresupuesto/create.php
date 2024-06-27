<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Agendapresupuesto $model */

$this->title = 'Create Agendapresupuesto';
$this->params['breadcrumbs'][] = ['label' => 'Agendapresupuestos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agendapresupuesto-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
