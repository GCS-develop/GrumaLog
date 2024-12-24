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

use frontend\models\Impresora;

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
        <div class="col-lg-12">
            <?= $form->field($model, 'idImpresora')->widget(Select2::classname(), [
                    'data' => Impresora::getListaData(),
                    'options' => [
                        'placeholder' => 'Impresora ...', 
                        'multiple' => false,
                        'id' => 'id-impresora',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>
        </div>

    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Imprimir', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
