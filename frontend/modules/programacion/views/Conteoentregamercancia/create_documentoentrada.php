<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conteoentregamercancia $model */

$this->title = 'Documento Entrada SIESA';
$this->params['breadcrumbs'][] = ['label' => 'Conteoentregamercancias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="documentoentrada-create">

    <?= $this->render('_form_documentoentrada', [
        'model' => $model,
    ]) ?>

</div>
