<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Agendaentregamercancia $model */

$this->title = 'Agendar Entrega Mercancia';
$this->params['breadcrumbs'][] = ['label' => 'Agendamiento', 'url' => ['indexagendaperiodo']];
$this->params['breadcrumbs'][] = ['label' => 'Agenda Entrega Mercancia', 'url' => ['index', 'id' => $model->idAgenda]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agendaentregamercancia-create">

    <?= $this->render('_form_orden_compra', [
        'model' => $model,
    ]) ?>

</div>
