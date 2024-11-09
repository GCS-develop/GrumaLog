<?php

use yii\helpers\Html;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\Devoluciondocumentodetalle $model */

$this->title = 'Registrar';
$this->params['breadcrumbs'][] = ['label' => 'Devolución Detalle', 'url' => ['/devolucion/devoluciondocumentodetalle/index/', 'iddocumento' => $modeldocumento->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="devoluciondocumentodetalle-create">

    <?= Alert::widget() ?>

    <?= $this->render('_form', [
        'model' => $model,
        'modeldocumento' => $modeldocumento,
        'modeluser' => $modeluser
    ]) ?>

</div>
