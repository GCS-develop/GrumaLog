<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Userconteo $model */

$this->title = 'Create Userconteo';
$this->params['breadcrumbs'][] = ['label' => 'Userconteos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="userconteo-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
