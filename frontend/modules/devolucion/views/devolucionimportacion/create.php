<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Devolucionimportacion $model */

$this->title = 'Create Devolucionimportacion';
$this->params['breadcrumbs'][] = ['label' => 'Devolucionimportacions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="devolucionimportacion-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
