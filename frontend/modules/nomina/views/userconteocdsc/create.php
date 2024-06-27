<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Userconteocdsc $model */

$this->title = 'Create Userconteocdsc';
$this->params['breadcrumbs'][] = ['label' => 'Userconteocdscs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="userconteocdsc-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
