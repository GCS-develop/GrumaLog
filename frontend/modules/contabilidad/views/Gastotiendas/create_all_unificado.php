<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use frontend\models\GrupoConcepto;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $doc frontend\models\DocumentoGasto */
/* @var $rows frontend\models\MovimientoGasto[] */
/* @var $auxiliares array */
/* @var $ccostos array */
/* @var $ciaFija int */
/* @var $unFija string */
/* @var $sugeridoConsec int|null */

$this->title = 'Registro de Gasto';
$this->params['breadcrumbs'][] = ['label' => 'Documento Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$grupos = ArrayHelper::map(GrupoConcepto::find()->orderBy('nombre')->all(), 'id', 'nombre');
?>

<div class="form-card">
  <h3 class="mb-4"><i class="fas fa-receipt"></i> <?= Html::encode($this->title) ?></h3>

  <?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
  <?php endif; ?>

  <?php $form = ActiveForm::begin([
      'options' => ['class' => 'form-horizontal'],
      'fieldConfig' => [
          'errorOptions' => ['class' => 'help-block text-danger'], // errores en rojo
      ],
  ]); ?>

  <!-- ========== CABECERA ========== -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <i class="fas fa-file-alt"></i> Cabecera del Documento
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-2">
          <?= $form->field($doc, 'F350_ID_CO')
              ->textInput(['maxlength'=>50,'placeholder'=>'Ej: 011'])
              ->label('C.O') ?>
        </div>
        <div class="col-md-3">
          <?= $form->field($doc, 'F350_FECHA')
              ->input('date')
              ->label('Fecha') ?>
        </div>
        <div class="col-md-7">
          <?= $form->field($doc, 'F350_ID_TERCERO')
              ->textInput(['maxlength'=>50])
              ->label('Tercero') ?>
        </div>
      </div>
      <?= $form->field($doc, 'F350_NOTAS')
          ->textInput(['maxlength'=>1000,'placeholder'=>'Opcional'])
          ->label('Notas') ?>
    </div>
  </div>

  <!-- Fijos de cabecera -->
  <?= Html::activeHiddenInput($doc, 'F350_ID_TIPO_DOCTO', ['value'=>'22']) ?>
  <?= Html::activeHiddenInput($doc, 'F350_CONSEC_DOCTO', ['value'=>$doc->F350_CONSEC_DOCTO]) ?>

  <?= Html::activeHiddenInput($doc, 'F350_IND_ESTADO', ['value'=>'1']) ?>

  <!-- ========== DETALLE ========== -->
  <div class="card mb-4">
    <div class="card-header bg-secondary text-white">
      <i class="fas fa-list"></i> Detalle de Movimientos
    </div>
    <div class="card-body">
      <div id="detalle-container">
        <?php foreach ($rows as $i => $row): ?>
          <div class="card mb-3 detalle-linea">
            <div class="card-body row align-items-end">

              <div class="col-md-3">
                <?= $form->field($row, "[$i]grupo_id")->dropDownList(
                  $grupos,
                  ['prompt'=>'Seleccionar grupo','class'=>'form-control grupo-select']
                ) ?>
              </div>

              <div class="col-md-4">
                <?= $form->field($row, "[$i]F351_ID_AUXILIAR")->dropDownList(
                  [],
                  ['prompt'=>'Seleccionar cuenta','class'=>'form-control cuenta-select']
                ) ?>
              </div>

              <div class="col-md-3">
                <?= $form->field($row, "[$i]valor")->textInput(['class'=>'form-control valor-input']) ?>
                <?= Html::activeHiddenInput($row, "[$i]naturaleza", ['class'=>'naturaleza-hidden']) ?>

              </div>

              

              <div class="col-md-2 text-right">
                <button type="button" class="btn btn-sm btn-danger btn-remove-line">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
                  
              <!-- Constantes ocultos -->
              <?= Html::activeHiddenInput($row, "[$i]F351_ID_CO_MOV", ['value'=>'002']) ?>
              <?= Html::activeHiddenInput($row, "[$i]F351_ID_CCOSTO", ['value'=>'04102']) ?>
              <?= Html::activeHiddenInput($row, "[$i]F351_ID_FE", ['value'=>'']) ?>
              <?= Html::activeHiddenInput($row, "[$i]F351_DOCTO_BANCO", ['value'=>'0']) ?>
              <?= Html::activeHiddenInput($row, "[$i]F351_NRO_DOCTO_BANCO", ['value'=>'0']) ?>
              <?= Html::activeHiddenInput($row, "[$i]F351_NOTAS", ['value'=>$doc->F350_NOTAS]) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>


<!-- Total general alineado a la derecha -->
<div class="row">
  <div class="col-12 text-right mt-2">
    <strong>Total:</strong> <span id="total-valor">$0.00</span>
  </div>
</div>

    
      <div class="mb-3">
        <button type="button" class="btn btn-sm btn-primary" id="btn-add-linea">
          <i class="fas fa-plus"></i> Añadir cuenta
        </button>
      </div>

      
  </div>

  <!-- Botones de acción -->
  <div class="form-group text-right">
    <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class'=>'btn btn-success']) ?>
    <?= Html::a('<i class="fas fa-times"></i> Cancelar', ['index'], ['class'=>'btn btn-outline-secondary']) ?>
  </div>

  <?php ActiveForm::end(); ?>
</div>

<?php
$urlCuentasPorGrupo   = Url::to(['gastotiendas/cuentas-por-grupo']);
$urlNaturalezaCuenta = Url::to(['gastotiendas/naturaleza-cuenta']);
$script = <<<JS
let index = $('#detalle-container .detalle-linea').length;

function calcularTotal() {
    let total = 0;
    $('.valor-input').each(function() {
        const val = parseFloat($(this).val()) || 0;
        total += val;
    });
    $('#total-valor').text('$' + total.toFixed(2));
}

$(document).on('change', '.grupo-select', function() {
    const grupoId = $(this).val();
    const cuentaSelect = $(this).closest('.detalle-linea').find('.cuenta-select');
    cuentaSelect.html('<option>Cargando...</option>');
    $.getJSON('$urlCuentasPorGrupo', { id: grupoId }, function(data) {
        let options = '<option value="">Seleccionar cuenta</option>';
        data.forEach(function(cuenta) {
            options += '<option value="' + cuenta.id + '">' + cuenta.text + '</option>';
        });
        cuentaSelect.html(options);
    });
});

$(document).on('change', '.cuenta-select', function() {
    const cuenta = $(this).val();
    const \$row = $(this).closest('.detalle-linea');
    const naturalezaInput = \$row.find('.naturaleza-hidden');

    if (!cuenta) { naturalezaInput.val(''); return; }

    $.getJSON('$urlNaturalezaCuenta', { cuenta: cuenta }, function(data) {
        if (data.success) {
            naturalezaInput.val(data.naturaleza);
        } else {
            naturalezaInput.val('');
        }
    });
});

$('#btn-add-linea').on('click', function() {
    const template = $('#detalle-container .detalle-linea:first').clone();
    template.find('select, input').each(function() {
        const name = $(this).attr('name');
        if (name) {
            const newName = name.replace(/\\[\\d+\\]/, '[' + index + ']');
            $(this).attr('name', newName).attr('id', newName);
            if (!$(this).hasClass('naturaleza-hidden') && !$(this).attr('type') === 'hidden') {
                $(this).val('');
            }
        }
    });
    template.find('.naturaleza-hidden').val('');
    template.find('[name$="[F351_ID_CCOSTO]"]').val('04102');
    $('#detalle-container').append(template);
    index++;
    calcularTotal();
});

$(document).on('click', '.btn-remove-line', function() {
    if ($('.detalle-linea').length > 1) {
        $(this).closest('.detalle-linea').remove();
        calcularTotal();
    }
});

$(document).on('input', '.valor-input', calcularTotal);
calcularTotal();
JS;
$this->registerJs($script);
?>

<style>
.has-error .form-control {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220,53,69,.25);
}
.help-block {
    font-size: 0.9em;
    font-weight: 500;
}
</style>
