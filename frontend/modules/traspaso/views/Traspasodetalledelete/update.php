<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Traspasodetalledelete $model */

$this->title = 'Actualizar Traspaso detalle delete: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Traspasodetalledeletes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="traspasodetalledelete-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
