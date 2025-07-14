<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\SiesaConectorDocumentoCampo $model */

$this->title = 'Create Siesa Conector Documento Campo';
$this->params['breadcrumbs'][] = ['label' => 'Siesa Conector Documento Campos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="siesa-conector-documento-campo-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
