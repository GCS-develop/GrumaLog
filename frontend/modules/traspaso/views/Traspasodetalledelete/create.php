<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Traspasodetalledelete $model */

$this->title = 'Create Traspaso detalle delete';
$this->params['breadcrumbs'][] = ['label' => 'Traspasodetalledeletes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="traspasodetalledelete-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
