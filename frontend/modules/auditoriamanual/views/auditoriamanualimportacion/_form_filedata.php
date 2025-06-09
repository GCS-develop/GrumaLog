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
use kartik\file\FileInput;

/** @var yii\web\View $this */
/** @var frontend\models\Agendapresupuesto $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="carpeta-form">

    <?php $form = ActiveForm::begin([
            'id' => 'modal-form-horasextras',
            //'enableAjaxValidation' => true,
            'options'=>['enctype'=>'multipart/form-data']
            ]); ?>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'archivo')->widget(FileInput::classname(), [
                                            'options' => ['multiple' => false,],
                                            'language' => 'es' ,
                                            'pluginOptions'=>[
                                                'showPreview' => false,
                                                'showUpload' => false,
                                                'browseLabel' => 'Seleccionar Archivo',
                                                'removeLabel' => '',
                                                'removeTitle' => 'Cancelar selección',
                                            ],
            ]);
            ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
