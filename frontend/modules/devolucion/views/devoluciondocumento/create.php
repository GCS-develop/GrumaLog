<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Devoluciondocumento $model */

$this->title = 'Registrar';
$this->params['breadcrumbs'][] = ['label' => 'Devolución Documentos', 'url' => ['register']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="devoluciondocumento-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
