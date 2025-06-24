<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Traspasodetalletienda $model */

$this->title = 'Create Traspasodetalletienda';
$this->params['breadcrumbs'][] = ['label' => 'Traspasodetalletiendas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="traspasodetalletienda-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
