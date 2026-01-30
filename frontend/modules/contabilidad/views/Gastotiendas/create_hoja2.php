<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use frontend\models\GrupoConcepto;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $doc frontend\models\DocumentoGasto */
/* @var $rows frontend\models\MovimientoGasto[] */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Crear Movimiento de Gasto';
$this->params['breadcrumbs'][] = ['label' => 'Documento Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$grupos = ArrayHelper::map(GrupoConcepto::find()->orderBy('nombre')->all(), 'id', 'nombre');
?>

<div class="card">
  <div class="header">
    <h3><?= Html::encode($this->title) ?></h3>
  </div>

  <?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
  <?php endif; ?>

  <?php $form = ActiveForm::begin(); ?>

  <div class="mb-3">
    <?= $form->field($doc, 'F350_FECHA')->input('date') ?>
    <?= $form->field($doc, 'F350_ID_TERCERO')->textInput() ?>
    <?= $form->field($doc, 'F350_NOTAS')->textarea(['rows' => 2]) ?>
  </div>

  <h4>Detalle de Movimientos</h4>

  <div class="card mb-3 detalle-linea">
  <div class="card-body row align-items-end">
    <div class="col-md-3">
      <?= $form->field($row, "[$i]grupo_id")->dropDownList(
        $grupos,
        ['prompt' => 'Seleccionar grupo', 'class' => 'form-control grupo-select']
      ) ?>
    </div>

    <div class="col-md-4">
      <?= $form->field($row, "[$i]F351_ID_AUXILIAR")->dropDownList(
        [],
        ['prompt' => 'Seleccionar cuenta', 'class' => 'form-control cuenta-select']
      ) ?>
    </div>

    <div class="col-md-3">
      <?= $form->field($row, "[$i]valor")->textInput(['class' => 'form-control valor-input']) ?>
      <?= Html::activeHiddenInput($row, "[$i]naturaleza", ['class' => 'naturaleza-hidden']) ?>
    </div>

    <div class="col-md-2">
      <button type="button" class="btn btn-danger btn-remove-line"><i class="fas fa-trash"></i></button>
    </div>

    <!-- ✅ Campos ocultos para que se guarden en BD -->
    <?= Html::activeHiddenInput($row, "[$i]F351_ID_CO", ['value' => '011']) ?>
    <?= Html::activeHiddenInput($row, "[$i]F351_ID_TIPO_DOCTO", ['value' => '22']) ?>
    <?= Html::activeHiddenInput($row, "[$i]F351_CONSEC_DOCTO", ['value' => '2010']) ?>
    <?= Html::activeHiddenInput($row, "[$i]F351_ID_UN", ['value' => '01']) ?>
    <?= Html::activeHiddenInput($row, "[$i]F351_ID_FE", ['value' => '01']) ?>
    <?= Html::activeHiddenInput($row, "[$i]F351_DOCTO_BANCO", ['value' => '']) ?>
    <?= Html::activeHiddenInput($row, "[$i]F351_NRO_DOCTO_BANCO", ['value' => '']) ?>
    <?= Html::activeHiddenInput($row, "[$i]F351_NOTAS", ['value' => '']) ?>
    <?= Html::activeHiddenInput($row, "[$i]F351_ID_CCOSTO", ['value' => '04102']) ?>
  </div>
</div>


  <div class="mb-3">
    <button type="button" class="btn btn-primary" id="btn-add-linea">+ Añadir cuenta</button>
  </div>

  <div class="mb-3">
    <strong>Total:</strong> <span id="total-valor">$0.00</span>
  </div>

  <div class="form-group">
    <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
  </div>

  <?php ActiveForm::end(); ?>
</div>

<?php
$urlCuentasPorGrupo = Url::to(['gastotiendas/cuentas-por-grupo']);
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
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error('Error al cargar cuentas:', textStatus, errorThrown);
        cuentaSelect.html('<option value="">Error al cargar</option>');
    });
});

$(document).on('change', '.cuenta-select', function() {
    const cuenta = $(this).val();
    const \$row = $(this).closest('.detalle-linea');
    const naturalezaInput = \$row.find('.naturaleza-hidden');

    if (!cuenta) {
        naturalezaInput.val('');
        return;
    }

    $.getJSON('$urlNaturalezaCuenta', { cuenta: cuenta }, function(data) {
        if (data.success) {
            naturalezaInput.val(data.naturaleza);
        } else {
            console.error('Error al obtener naturaleza:', data.error);
            naturalezaInput.val('');
        }
    }).fail(function() {
        console.error('Fallo la solicitud AJAX para naturaleza');
        naturalezaInput.val('');
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

// Inicializar el total al cargar
calcularTotal();
JS;

$this->registerJs($script);
?>
