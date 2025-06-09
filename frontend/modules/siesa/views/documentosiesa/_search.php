<?php

$this->registerCss('
	
	.btn-search {
        width: 300px;
    }
');

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use frontend\models\Tipodocumento;

/** @var yii\web\View $this */
/** @var frontend\models\search\DocumentosiesaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="documentosiesa-search">

    <?php $form = ActiveForm::begin([
        'action' => [$action],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'tipoDocumento')->dropDownList(
                    Tipodocumento::getListaDataCodigo2(),
                    [
                        'prompt' => 'TipoDocumento... ',
                        'id' => 'id-TipoDocumento',
                        'required' => true
                    ]
            ) ?>
        </div>

        <div class="col-lg-6">
            <?= $form->field($model, 'numeroDocumento')->textInput(['required' => true]) ?>
        </div>
    </div>

    <?php // echo $form->field($model, 'f350_consec_docto') ?>

    <div class="form-group" align="center">
		<?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-search']) ?>
	</div>

    <?php ActiveForm::end(); ?>

</div>
