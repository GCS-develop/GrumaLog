<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Transportadora $model */

$this->title = 'Create Transportadora';
$this->params['breadcrumbs'][] = ['label' => 'Transportadoras', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transportadora-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
