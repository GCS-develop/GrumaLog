<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Inventario $model */

$this->title = 'Create Inventario';
$this->params['breadcrumbs'][] = ['label' => 'Inventarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="inventario-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
