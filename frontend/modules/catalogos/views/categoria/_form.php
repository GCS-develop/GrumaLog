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
/** @var frontend\models\Categoria $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="categoria-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-categoria',
                    'enableAjaxValidation' => true,
                ]);             
    ?>

    <div class="row">
        <div class="col-lg-8">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
        </div>
        
        <div class="col-lg-4">
            <?= $form->field($model, 'codigoERP')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
