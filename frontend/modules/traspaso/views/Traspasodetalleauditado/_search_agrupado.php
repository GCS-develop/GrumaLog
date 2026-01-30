<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/** @var \frontend\models\search\TraspasodetalleauditadoSearch $model */
/** @var int|null $idtraspaso */
?>
<div class="card mb-2">
    <div class="card-body">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['index-agrupado'],
            'options' => ['data-pjax' => 1],
        ]); ?>

        <div class="row g-2">
            <div class="col-md-2">
                <?= $form->field($model, 'idTraspaso')->textInput([
                    'placeholder' => 'ID Traspaso',
                    'type' => 'number',
                    'min' => 1,
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'consecutivoSiesa')->textInput([
                    'placeholder' => 'Consecutivo Siesa',
                ]) ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'serie')->textInput([
                    'placeholder' => 'Serie',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'creador')->textInput([
                    'placeholder' => 'Usuario creador',
                ]) ?>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="btn-group">
                    <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Limpiar', ['index-agrupado'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>