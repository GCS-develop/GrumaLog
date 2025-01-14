<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Ordendecompra $model */

$this->title = 'Create Ordendecompra';
$this->params['breadcrumbs'][] = ['label' => 'Ordendecompras', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ordendecompra-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
