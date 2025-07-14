<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\SiesaConectorDocumento $model */

$this->title = 'Create Siesa Conector Documento';
$this->params['breadcrumbs'][] = ['label' => 'Siesa Conector Documentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="siesa-conector-documento-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
