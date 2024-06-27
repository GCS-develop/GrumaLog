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

use frontend\models\Bodegas;

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
            <?= $form->field($model, 'idCentroOperacion')->widget(Select2::classname(), [
                    'data' => Bodegas::getListaData(),
                    'options' => [
                        'placeholder' => 'Almacén ...', 
                        'multiple' => false,
                        'id' => 'id-centro-operacion',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'observacion')->textInput() ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
