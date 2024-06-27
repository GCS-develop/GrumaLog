<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conteoentregamercancia $model */

$this->title = 'Create Conteoentregamercancia';
$this->params['breadcrumbs'][] = ['label' => 'Conteoentregamercancias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conteoentregamercancia-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
