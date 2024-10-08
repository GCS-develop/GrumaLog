<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Planillaembarque $model */

$this->title = 'Create Planillaembarque';
$this->params['breadcrumbs'][] = ['label' => 'Planillaembarques', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="planillaembarque-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
