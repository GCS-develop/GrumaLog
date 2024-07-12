<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Facturadetalle $model */

$this->title = 'Create Facturadetalle';
$this->params['breadcrumbs'][] = ['label' => 'Facturadetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="facturadetalle-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
