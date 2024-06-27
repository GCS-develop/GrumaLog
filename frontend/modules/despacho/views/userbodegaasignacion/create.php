<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Userbodegaasignacion $model */

$this->title = 'Create Userbodegaasignacion';
$this->params['breadcrumbs'][] = ['label' => 'Userbodegaasignacions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="userbodegaasignacion-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
