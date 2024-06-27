<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conteocdscusuario $model */

$this->title = 'Update Conteocdscusuario: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Conteocdscusuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="conteocdscusuario-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
