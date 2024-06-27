<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Usertraspaso $model */

$this->title = 'Create Usertraspaso';
$this->params['breadcrumbs'][] = ['label' => 'Usertraspasos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="usertraspaso-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
