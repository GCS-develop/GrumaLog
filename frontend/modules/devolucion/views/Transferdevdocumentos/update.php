<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Transferdevdocumentos $model */

$this->title = 'Editar Documento #' . $model->id_transferencia;
$this->params['breadcrumbs'][] = ['label' => 'Transferencias ERP', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Detalle', 'url' => ['viewtransferenciaocerp', 'id' => $model->id_transferencia]];
$this->params['breadcrumbs'][] = 'Editar';
?>
<div class="transferdevdocumentos-update">

    <h3 class="mb-3"><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
