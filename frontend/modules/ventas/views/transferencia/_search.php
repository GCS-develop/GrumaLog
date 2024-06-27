<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\search\TransferenciaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transferencia-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index', 'idfactura' => $idfactura],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'codigoBarra')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'item')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'talla')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'color')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'referencia')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'descripcion')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'bodega')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'error')->dropDownList(
                                            [   //'0' => 'Registro OK', 
                                                //'1' => 'No Tiene Bodega',
                                                '2' => 'Valor Cero',
                                                '3' => 'No Tiene Código Barras'
                                            ], 
                    [   'prompt' => ' Seleccionar Opción ... ', 
                        'id' => 'error',
                        'required'=>false]);
            ?>
        </div>
    </div>


    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
