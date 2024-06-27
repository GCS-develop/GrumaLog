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
/** @var frontend\models\Estadoagenda $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="estadoagenda-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-estadoagenda',
                    'enableAjaxValidation' => true,
                ]);             
    ?>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'codigo')->textInput(['maxlength' => true]) ?>
        </div>
        
        <div class="col-lg-6">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
