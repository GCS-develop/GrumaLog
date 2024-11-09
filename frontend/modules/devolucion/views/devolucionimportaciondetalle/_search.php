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
/** @var frontend\models\search\DevolucionimportaciondetalleSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="devolucionimportaciondetalle-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    
    <div class="row">
        
        <div class="col-lg-3">
            <?= $form->field($model, 'co') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'fecha') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'bodegaSalida') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'numeroDocumento') ?>
        </div>
    </div>

    <div class="row">
        
        <div class="col-lg-3">
            <?= $form->field($model, 'codigoBarras') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'itemResumen') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'item') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'referencia') ?>
        </div>
    </div>

    <div class="row">
        
        <div class="col-lg-3">
            <?= $form->field($model, 'proveedor') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'codigoBodegaSalida') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'categoria') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'notasDocumento') ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
