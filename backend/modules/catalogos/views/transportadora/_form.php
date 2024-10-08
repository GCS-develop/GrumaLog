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
/** @var frontend\models\Transportadora $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transportadora-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-transportadora',
                    'enableAjaxValidation' => true,
                ]);             
    ?>

    <div class="row">
        <div class="col-lg-8">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
        </div>
        
        <div class="col-lg-4">
            <?= $form->field($model, 'idEstado')->dropDownList(['1' => 'Activo', '0' => 'Inactivo'], 
                    [   'prompt' => ' Seleccionar Opción ... ', 
                        'id' => 'idestado',
                        'required'=>true]);
            ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
