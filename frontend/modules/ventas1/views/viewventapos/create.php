<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Viewventapos $model */

$this->title = 'Create Viewventapos';
$this->params['breadcrumbs'][] = ['label' => 'Viewventapos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="viewventapos-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
