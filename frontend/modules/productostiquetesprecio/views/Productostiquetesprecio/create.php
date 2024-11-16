<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Productostiquetesprecio $model */

$this->title = 'Create Productostiquetesprecio';
$this->params['breadcrumbs'][] = ['label' => 'Productostiquetesprecios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="productostiquetesprecio-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
