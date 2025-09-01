<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\date\DatePicker;

$form = ActiveForm::begin([
    'id' => 'form-registro',
    'enableAjaxValidation' => true,
]); ?>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="control-label">Proveedor</label>
            <div><?= Html::encode($devolucion->nombreProveedor) ?></div>
        </div>
        
        <?= $form->field($model, 'fechaDocumento')->widget(DatePicker::class, [
            'options' => ['placeholder' => 'Seleccione fecha...'],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd'
            ]
        ]) ?>
        



    </div>
    
    <div class="col-md-6">
        <?= $form->field($model, 'idTipoDocumento')->textInput() ?>
        
        <?= $form->field($model, 'consignacion')->dropDownList([
            1 => 'Sí',
            0 => 'No'
        ], ['prompt' => 'Seleccione...']) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
    </div>
</div>

<div class="form-group text-center">
    <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
</div>

<?php ActiveForm::end(); ?>

<script>
$(document).ready(function() {
    $('#form-registro').on('beforeSubmit', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: 'post',
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    $('#modalRegistro').modal('hide');
                    $.pjax.reload({container: '#pjax-grid-devoluciones', timeout: false});
                }
            }
        });
    });
});
</script>