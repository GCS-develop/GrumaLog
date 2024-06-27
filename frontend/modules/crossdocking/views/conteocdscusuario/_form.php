<?php

$this->registerCss('

    .btn-create {
        width: 300px;
    }

    .centrar {
        text-align: center;
    }
    
    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc; /* Color y grosor de la línea */
        margin: 10px 0; /* Espacio alrededor de la línea */
    }
    
');

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

use frontend\models\Userconteocdsc;

/** @var yii\web\View $this */
/** @var frontend\models\Conteocdscusuario $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conteocdscusuario-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-conteocdscusuario',
                    'enableAjaxValidation' => true,
                ]); 
    ?>

    <div class="row">
        <div class="col-lg-8">

            <?php if ($model->isNewRecord) { ?>
                <?= $form->field($model, 'idUserConteo')->widget(Select2::classname(), [
                        'data' => Userconteocdsc::getListaDataHabil(),
                        'options' => [
                            'placeholder' => 'Seleccionar Usuario ...', 
                            'multiple' => false,
                            'id' => 'iduserconteo',
                            'required' => 'required'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]);    
                ?>
            <?php } else { ?>
                <?= $form->field($model, 'idUserConteo')->widget(Select2::classname(), [
                        'data' => Userconteocdsc::getListaData(),
                        'options' => [
                            'placeholder' => 'Seleccionar Usuario ...', 
                            'multiple' => false,
                            'id' => 'iduserconteo',
                            'required' => 'required',
                            'disabled' => true
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]);    
                ?>
            <?php } ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'idEstado')->dropDownList(['1' => 'Programado', '0' => 'Finalizado'], 
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
