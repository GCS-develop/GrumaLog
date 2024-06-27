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

/** @var yii\web\View $this */
/** @var frontend\models\Conteoentregamercancia $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conteoentregamercancia-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-conteoentregamercancia',
                    'enableAjaxValidation' => true,
                ]); 
    ?>

    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'numeroFactura')->textInput() ?>
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
