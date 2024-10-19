<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\models\Estadodespacho;

/** @var yii\web\View $this */
/** @var frontend\models\search\PlanillaembarquetraspasoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<link rel="stylesheet" href="css/shared.css">

<div class="planillaembarquetraspaso-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-2">
            <?= $form->field($model, 'codAlmacenOrigen') ?>
        </div>

        <div class="col-4">
            <?= $form->field($model, 'almacenOrigen') ?>
        </div>

        <div class="col-2">
            <?= $form->field($model, 'codAlmacenDestino') ?>
        </div>

        <div class="col-4">
            <?= $form->field($model, 'almacenDestino') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-2">
        <?php echo $form->field($model, 'idEstado')->dropDownList(
                Estadodespacho::getListaData(),
                [
                    'prompt' => ' Seleccionar estado ... ',
                    'id' => 'idEstado',
                ]
            );
            ?>

        </div>

        <div class="col-4">
            <?= $form->field($model, 'usuarioRecibido') ?>
        </div>

        <div class="col-2">
            <?= $form->field($model, 'tipoDocumento') ?>
        </div>

        <div class="col-4">
            <?= $form->field($model, 'consecutivoDocumento') ?>
        </div>
    </div>


    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
        <!-- <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary btn-lg btn-create']) ?> -->
    </div>

    <?php ActiveForm::end(); ?>

</div>