<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conteocdscdestinodetalle $model */

$this->title = 'Create Conteocdscdestinodetalle';
$this->params['breadcrumbs'][] = ['label' => 'Conteocdscdestinodetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conteocdscdestinodetalle-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
