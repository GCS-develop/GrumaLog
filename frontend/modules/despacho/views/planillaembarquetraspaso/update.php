<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Planillaembarquetraspaso $model */

$this->title = 'Actualizar Planillaembarque traspaso: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Planillaembarque traspasos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="planillaembarquetraspaso-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
