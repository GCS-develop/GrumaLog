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
/** @var frontend\models\Devoluciondocumentodetalle $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="card">
    <div class="card-body">

        <div class="row">
            <div class="col-12 titulo">
                <?= Html::encode($modeluser->username) . ' - ' . $modeluser->empleado->nombreEmpleado?>
            </div>
        </div>

        <div class="row">
            <div class="col-12 titulo">
                <?= Html::encode($modeldocumento->codigoBodegaSalida) . ' - ' . $modeldocumento->bodegasalida->nombre?>
            </div>
        </div>

        <div class="row">
            <div class="col-12 titulo">
                <?= Html::encode($modeldocumento->numeroDocumento) ?>
            </div>
        </div>

    </div>
</div>

<div class="devoluciondocumentodetalle-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card">
        <div class="card-body">

            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'cantidad')->textInput() ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'codigobarras')->textInput(['maxlength' => true, 'autofocus' => true]) ?>
                </div>
            </div>

            <div class="form-group centrar">
                <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>
