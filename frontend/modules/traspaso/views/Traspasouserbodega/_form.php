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
use frontend\models\Bodegas;
use frontend\models\Usertraspaso;


/** @var yii\web\View $this */
/** @var frontend\models\Traspasouserbodega $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="traspasouserbodega-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">

        <div class="col-12 col-lg-5">
            <?=
                $form->field($model, 'idUserTraspaso')->dropDownList(
                    Usertraspaso::getListaData(),
                    [
                        'prompt' => 'Empleado... ',
                        'id' => 'id-empleado',
                        'required' => true
                    ]

                ) ?>
        </div>

        <div class="col-12 col-lg-5">
            <?= $form->field($model, 'idBodega')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => ' Bodega... ',
                    'id' => 'id-bodega',
                    'required' => true
                ]
            ) ?>
        </div>

        <div class="col-12 col-lg-2">
            <?= $form->field($model, 'idEstado')->textInput() ?>
        </div>

    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>