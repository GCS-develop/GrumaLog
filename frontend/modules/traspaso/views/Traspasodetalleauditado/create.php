<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Traspasodetalleauditado $model */

$this->title = 'Crear Traspasodetalleauditado';
$this->params['breadcrumbs'][] = ['label' => 'Traspasodetalleauditados', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="traspasodetalleauditado-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
