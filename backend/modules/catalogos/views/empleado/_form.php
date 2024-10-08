<?php

$this->registerCss('
    .mi-gridview {
        font-size: 12px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

    .btn-create {
        width: 300px;
    }

    .centrar {
        text-align: center;
    }
');

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use frontend\models\Cargo;
use frontend\models\Centrooperacion;
use frontend\models\Centrocostos;

/** @var yii\web\View $this */
/** @var frontend\models\Empleado $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="empleado-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-lg-4"> 
            <?= $form->field($model, 'identificacion')->textInput() ?>
        </div>

        <div class="col-lg-6">
            <?= $form->field($model, 'nombreEmpleado')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-2">
            <?= $form->field($model, 'ndc')->textInput() ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'idEstado')->dropDownList(['1' => 'Activo', '0' => 'Inactivo'], 
                    [   'prompt' => ' Seleccionar Opción ... ', 
                        'id' => 'idestado',
                        'required'=>true]);
            ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'idCargo')->dropDownList(Cargo::getListaData(), 
                                                ['prompt' => ' Seleccionar Cargo ... ',
                                                'id' => 'id-cargo',
                                                'required' => true
                                                ])
            ?>                    
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'idCO')->dropDownList(Centrooperacion::getListaData(), 
                                                ['prompt' => ' Seleccionar CO ... ',
                                                'id' => 'id-co',
                                                'required' => true
                                                ])
            ?>                    
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'idCC')->dropDownList(Centrocostos::getListaData(), 
                                                ['prompt' => ' Seleccionar CC ... ',
                                                'id' => 'id-cc',
                                                'required' => true
                                                ])
            ?>                    
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
