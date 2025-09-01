<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Transferdevdocumentos $model */

$this->title = 'Create Transferdevdocumentos';
$this->params['breadcrumbs'][] = ['label' => 'Transferdevdocumentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transferdevdocumentos-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
