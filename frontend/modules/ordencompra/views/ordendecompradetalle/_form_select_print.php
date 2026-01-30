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

use frontend\models\Impresoraspaxarbodega;

/** @var yii\web\View $this */
/** @var frontend\models\Selectimpresora $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="impresora-form">

    <?php $form = ActiveForm::begin([
        'id' => 'modal-form-impresora',
        'enableAjaxValidation' => true,
    ]);
    ?>

    <div class="row">

        <?php echo $form->field($model, 'cantidad_stickers')->textInput([
            'type' => 'number',
            'min' => 1
        ]); ?>





        <div class="col-lg-12">
            <?= $form->field($model, 'idImpresora')->widget(Select2::classname(), [
                'data' => Impresoraspaxarbodega::getListaData(),
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