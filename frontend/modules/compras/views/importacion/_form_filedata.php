<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;

$this->registerCss('
    .btn-create { width: 260px; }
    .centrar    { text-align: center; }
');
?>

<div class="compras-upload-form">

    <?php $form = ActiveForm::begin([
        'id'      => 'modal-form-compras-upload',
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'archivo')->widget(FileInput::class, [
                'options'       => ['multiple' => false],
                'language'      => 'es',
                'pluginOptions' => [
                    'showPreview'  => false,
                    'showUpload'   => false,
                    'browseLabel'  => 'Seleccionar Archivo (.xlsx / .xls)',
                    'removeLabel'  => '',
                    'removeTitle'  => 'Cancelar selección',
                    'allowedFileExtensions' => ['xlsx', 'xls'],
                ],
            ]) ?>
        </div>
    </div>

    <div class="form-group centrar mt-2">
        <?= Html::submitButton('<i class="fas fa-upload mr-1"></i> Cargar Archivo',
            ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
