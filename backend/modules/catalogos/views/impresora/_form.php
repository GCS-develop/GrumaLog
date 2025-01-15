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
/** @var common\models\Impresora $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="impresora-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-bodega',
                    'enableAjaxValidation' => true,
                ]);             
    ?>
    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'ip')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'activa')->dropDownList(['T' => 'Activa', 'F' => 'No Activa'], 
                    [   'prompt' => ' Seleccionar Opción ... ', 
                        'id' => 'activa',
                        'required'=>true]);
            ?>
        </div>
        
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'descripcion')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
