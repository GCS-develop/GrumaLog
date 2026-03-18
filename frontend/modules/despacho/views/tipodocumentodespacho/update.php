<?php

/** @var yii\web\View $this */
/** @var frontend\models\Tipodocumentodespacho $model */

$this->title = 'Editar Tipo Documento Despacho #' . $model->id;
?>

<div class="tipodocumentodespacho-update">
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
