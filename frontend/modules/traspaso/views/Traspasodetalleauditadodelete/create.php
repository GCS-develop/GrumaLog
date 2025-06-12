<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Traspasodetalleauditadodelete $model */

$this->title = 'Create Traspasodetalleauditadodelete';
$this->params['breadcrumbs'][] = ['label' => 'Traspasodetalleauditadodeletes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="traspasodetalleauditadodelete-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
