<?php

// Definir el estilo CSS directamente en la vista
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
/** @var frontend\models\Bodegas $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="bodegas-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-bodega',
                    'enableAjaxValidation' => true,
                ]);             
    ?>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'codigo')->textInput(['maxlength' => true]) ?>
        </div>
        
        <div class="col-lg-6">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'cedi')->dropDownList(['1' => 'SI', '0' => 'NO'],
                    [   'prompt' => ' Seleccionar Opción ... ',
                        'id' => 'cedi',
                        'required'=>true]);
            ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'zona')->textInput([
                'type' => 'number',
                'min' => 1,
                'placeholder' => 'Sin zona',
                'id' => 'zona',
            ])->hint('Agrupa tiendas para cambio de bodega en PDA. Dejar vacío si no aplica.') ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
