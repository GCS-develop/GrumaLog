<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Agendapresupuestosubcategoria $model */

$this->title = 'Create Agendapresupuestosubcategoria';
$this->params['breadcrumbs'][] = ['label' => 'Agendapresupuestosubcategorias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agendapresupuestosubcategoria-create">

    <?= $this->render('_form', [
        'model' => $model,
        'modelAgenda' => $modelAgenda,
    ]) ?>

</div>
