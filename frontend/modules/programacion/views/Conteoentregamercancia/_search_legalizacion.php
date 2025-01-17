<?php

$this->registerCss('

    .btn-create {
        width: 300px;
    }
    
    .centrar {
        text-align: center;
    }

');

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\ConteoentregamercanciaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conteoentregamercancia-search">

    <?php $form = ActiveForm::begin([
        'action' => ['/programacion/conteoentregamercancia/indexlegalizacion'],
        'method' => 'get',
    ]); ?>

    <div class="row">

        <div class="col-lg-4">
            <?= $form->field($model, 'numeroOrdenCompra') ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'numeroFactura') ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
