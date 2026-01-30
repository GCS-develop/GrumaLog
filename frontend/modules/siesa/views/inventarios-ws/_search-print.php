<?php

$this->registerCss('
    .mi-gridview {
        font-size: 10px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }
	
	.btn-search {
        width: 300px;
    }
');

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\search\InventariosWsSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="inventarios-search mi-gridview">

    <?php $form = ActiveForm::begin([
        'action' => ['/siesa/inventarios-ws/index-print'], // <- explícito
        'method' => 'get',
    ]); ?>


    <div class="card">
        <div class="card-body">
            <div class="row d-flex justify-content-between align-items-lg-center">

                <div class="col-md-4">
                    <?= $form->field($model, 'Item')->textInput([
                        'placeholder' => 'Item',
                        'autocomplete' => 'off',
                    ]) ?>
                </div>
                <div class="col-lg-4">
                    <?= $form->field($model, 'EAN')->textInput([
                        'placeholder' => 'EAN',
                        'autocomplete' => 'off',
                    ])  ?>
                </div>

                <!-- 
                <div class="col-lg-4">
                    <?= $form->field($model, 'Extension1') ?>
                </div>

                <div class="col-lg-4">
                    <?= $form->field($model, 'Extension2') ?>
                </div> -->


                <div class="form-group mt-4">
                    <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-search']) ?>
                </div>
            </div>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>