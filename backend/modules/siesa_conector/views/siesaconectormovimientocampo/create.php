<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\SiesaConectorMovimientoCampo $model */

$this->title = 'Create Siesa Conector Movimiento Campo';
$this->params['breadcrumbs'][] = ['label' => 'Siesa Conector Movimiento Campos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="siesa-conector-movimiento-campo-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
