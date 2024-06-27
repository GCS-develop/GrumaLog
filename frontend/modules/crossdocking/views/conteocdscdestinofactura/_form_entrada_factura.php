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
use kartik\select2\Select2;

use frontend\models\Tipodocumento;

/** @var yii\web\View $this */
/** @var frontend\models\Conteocdscdestinofactura $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conteocdscdestinofactura-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-conteocdscdestinofactura',
                    'enableAjaxValidation' => true,
                ]); 
    ?>

    <div class="row">
        <div class="col-lg-9">
            <?= $form->field($model, 'idTipoDocumento')->widget(Select2::classname(), [
                    'data' => Tipodocumento::getListaDataCodigo(),
                    'options' => [
                        'placeholder' => 'Serie Entrada ...', 
                        'multiple' => false,
                        'id' => 'id-tipo-documento',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'numeroEntrada')->textInput() ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
