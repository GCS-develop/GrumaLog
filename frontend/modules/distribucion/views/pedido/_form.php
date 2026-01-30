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
use kartik\date\DatePicker;

/** @var yii\web\View $this */
/** @var frontend\models\Pedido $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="pedido-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-pedido',
                    'enableAjaxValidation' => false,
                ]); 
    ?>

    <div class="row">

        <div class="col-lg-12">
            <?= 
                $form->field($model, 'fecha')->widget(DatePicker::className(),[
                    'name' => 'fecha', 
                    'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                    'language'=>'es',
                    'options' => [  'placeholder' => 'Fecha Pedido ...',
                                    'id' => 'fecha-pedido',
                                    'required' => true
                    ],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]) 
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'observaciones')->textInput(['id' => 'observaciones']) ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>
                
    <?php ActiveForm::end(); ?>

</div>
