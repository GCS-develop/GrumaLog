<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Facturaentregamercancia $model */

$this->title = 'Create Facturaentregamercancia';
$this->params['breadcrumbs'][] = ['label' => 'Facturaentregamercancias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="facturaentregamercancia-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
