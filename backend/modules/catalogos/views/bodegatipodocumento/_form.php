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
use frontend\models\Tipodocumento;


/** @var yii\web\View $this */
/** @var frontend\models\Bodegatipodocumento $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="bodegatipodocumento-form">

    <?php $form = ActiveForm::begin([
        'id' => 'modal-form-Bodegatipodocumentos',
        'enableAjaxValidation' => true,
    ]);
    ?>

    <div class="row">

        <div class="col-6">
            <?= $form->field($model, 'idBodega')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => ' Bodega... ',
                    'id' => 'id-bodega',
                    'required' => true
                ]
            ) ?>
        </div>

        <div class="col-6">
            <?= $form->field($model, 'idTipoDocumento')->dropDownList(
                Tipodocumento::getListaDataCodigo(),
                [
                    'prompt' => 'TipoDocumento... ',
                    'id' => 'id-TipoDocumento',
                    'required' => true
                ]
            ) ?>
        </div>

    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>