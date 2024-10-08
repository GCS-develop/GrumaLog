<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Transferenciaerperror $model */

$this->title = 'Create Transferenciaerperror';
$this->params['breadcrumbs'][] = ['label' => 'Transferenciaerperrors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transferenciaerperror-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
