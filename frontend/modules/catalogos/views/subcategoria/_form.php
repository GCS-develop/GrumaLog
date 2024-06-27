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

use frontend\models\Categoria;

/** @var yii\web\View $this */
/** @var frontend\models\Subcategoria $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="subcategoria-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-subcategoria',
                    'enableAjaxValidation' => true,
                ]);             
    ?>

    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'idCategoria')->dropDownList(Categoria::getListaData(), 
                                                ['prompt' => ' Seleccionar Categoría ... ',
                                                'id' => 'id-categoria',
                                                'required' => true
                                                ])
            ?>                    
        </div>

        <div class="col-lg-5">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
        </div>
        
        <div class="col-lg-3">
            <?= $form->field($model, 'codigoERP')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>


    <?php ActiveForm::end(); ?>

</div>
