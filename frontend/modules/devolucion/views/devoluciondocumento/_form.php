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


use frontend\models\Bodegas;

/** @var yii\web\View $this */
/** @var frontend\models\Devoluciondocumento $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="devoluciondocumento-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">

        <div class="col-12">
            <?= $form->field($model, 'idBodega')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => ' Bodega... ',
                    'id' => 'id-bodega',
                    'required' => true
                ]
            ) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?= $form->field($model, 'numeroDocumento')->textInput(['maxlength' => true]) ?>
        </div>

    </div>

    

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
