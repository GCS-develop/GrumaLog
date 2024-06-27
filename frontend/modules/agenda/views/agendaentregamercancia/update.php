<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Agendaentregamercancia $model */

$this->title = 'Datos Cita';
$this->params['breadcrumbs'][] = ['label' => 'Agendamiento', 'url' => ['indexagendaperiodo']];
$this->params['breadcrumbs'][] = ['label' => 'Agenda Entrega Mercancía', 'url' => ['index', 'id' => $model->idAgenda]];
$this->params['breadcrumbs'][] = 'Datos Cita';
?>
<div class="agendaentregamercancia-update">

    <?= $this->render('_form', [
        'model' => $model,
        'dataProviderSubcategoria' => $dataProviderSubcategoria,
        'dataProviderPresupuestoSubcategoria' => $dataProviderPresupuestoSubcategoria,
        'categoria' => $categoria,
        'dataProviderDia' => $dataProviderDia,
        'dataByCrossDocking' => $dataByCrossDocking,
    ]) ?>

</div>
