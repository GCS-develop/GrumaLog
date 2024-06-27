<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Traspasodetalle $model */

$this->title = 'Create Traspasodetalle';
$this->params['breadcrumbs'][] = ['label' => 'Traspasodetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="traspasodetalle-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
