<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Facturaitem $model */

$this->title = 'Create Facturaitem';
$this->params['breadcrumbs'][] = ['label' => 'Facturaitems', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="facturaitem-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
