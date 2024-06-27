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

use common\models\User;

/** @var yii\web\View $this */
/** @var frontend\models\Empleadologistica $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="empleadologistica-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-empleadologistica',
                    'enableAjaxValidation' => true,
                ]); ?>

    <div class="row">
        <div class="col-lg-12">

            <?= $form->field($model, 'idUser')->widget(Select2::classname(), [
                    'data' => User::getListaDataNoConteo(),
                    'options' => [
                        'placeholder' => 'Seleccionar Colaborador ...', 
                        'multiple' => false,
                        'id' => 'id-empleado',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
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
