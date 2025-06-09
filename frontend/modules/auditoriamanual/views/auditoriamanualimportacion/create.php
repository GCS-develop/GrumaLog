<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Auditoriamanualimportacion $model */

$this->title = 'Create Auditoriamanualimportacion';
$this->params['breadcrumbs'][] = ['label' => 'Auditoriamanualimportacions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auditoriamanualimportacion-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
