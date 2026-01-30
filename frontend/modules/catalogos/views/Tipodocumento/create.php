<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Tipodocumento $model */

$this->title = 'Create Tipodocumento';
$this->params['breadcrumbs'][] = ['label' => 'Tipodocumentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tipodocumento-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
