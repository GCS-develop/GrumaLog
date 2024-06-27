<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Estadoagenda $model */

$this->title = 'Create Estadoagenda';
$this->params['breadcrumbs'][] = ['label' => 'Estadoagendas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="estadoagenda-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
