<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conteoentregamercancia $model */

$this->title = 'Update Conteoentregamercancia: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Conteoentregamercancias', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="conteoentregamercancia-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
