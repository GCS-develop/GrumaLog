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
/** @var frontend\modules\ventas\models\search\CotizacionprecioSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="cotizacionprecio-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'codigoProveedor') ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'nitProveedor') ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'razonSocial') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'item') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'descripcion') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'color') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'talla') ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
