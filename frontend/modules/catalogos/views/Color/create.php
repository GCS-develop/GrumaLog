<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Color $model */

$this->title = 'Crear Color';
$this->params['breadcrumbs'][] = ['label' => 'Colors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="color-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
