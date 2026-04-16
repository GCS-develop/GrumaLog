<?php
/** @var yii\web\View $this */
/** @var frontend\models\Calificacionproveedor $model */
/** @var array $proveedoresMap */
/** @var array $subcategorias  filas con subcategoria, categoria, unidades_ordenadas, unidades_entregadas */
/** @var array $yaCalificadas  [subcategoria => Calificacionproveedor] */
/** @var frontend\models\Calificacionincumplimiento[] $incumplimientos */

use frontend\models\Calificacionproveedor;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

$isNew  = $model->isNewRecord;
$this->title = $isNew ? 'Nueva Calificación' : 'Editar Calificación #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Calificación Proveedores', 'url' => ['/calificacion/calificacion/index']];
$this->params['breadcrumbs'][] = $this->title;

$tieneSubcats       = !empty($subcategorias) && count($subcategorias) > 1;
$tieneOc            = !empty($model->id_ordendecompra);
$subcatDataJson     = json_encode(array_column($subcategorias, null, 'subcategoria'));
$urlSubcatData      = \yii\helpers\Url::to(['/calificacion/calificacion/subcategoria-data',
    'id_oc' => $model->id_ordendecompra]);
$urlPonderado       = $model->id_ordendecompra
    ? \yii\helpers\Url::to(['/calificacion/calificacion/ponderado', 'id_oc' => $model->id_ordendecompra])
    : null;
$urlAddIncumplimiento = \yii\helpers\Url::to(['/calificacion/calificacion/add-incumplimiento']);
$numIncumplimientos   = count($incumplimientos ?? []);

// Opciones 1-5 para criterios
$opciones = [1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'];
?>

<?php $this->registerCss('
    .criterio-card { border-left: 4px solid #007bff; }
    .criterio-card-info { border-left: 4px solid #6c757d; }
    .criterio-producto { border-left: 4px solid #28a745; background: #f8fff9; }
    .score-btn { min-width: 42px; }
    .score-group .btn { border-radius: 4px !important; }
    .badge-peso { font-size: 0.7rem; background: #6c757d; }
    .section-title { font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; color:#6c757d; font-weight:600; }
'); ?>

<?php $this->registerJs("
$(document).on('change', '.score-select', function() {
    recalcularCalidad();
    recalcularTotal();
});

$(document).on('change', '#calificacionproveedor-calidad_producto', function() {
    actualizarFormulaPreview();
    recalcularTotal();
});

function actualizarFormulaPreview() {
    var cpRaw  = parseInt(\$('#calificacionproveedor-calidad_producto').val());
    var nIncum = parseInt(\$('#campo-num-incumplimientos').val()) || 0;
    if (isNaN(cpRaw) || cpRaw < 1 || nIncum === 0) {
        \$('#formula-preview').text('');
        return;
    }
    var ef = ((cpRaw + nIncum) / (1 + nIncum)).toFixed(2);
    \$('#formula-preview').text(' → (' + cpRaw + ' + ' + nIncum + ') / ' + (1 + nIncum) + ' = ' + ef);
}

function recalcularCalidad() {
    var pesos = {
        'caja_bulto': 0.05, 'calibre': 0.05, 'rotulo': 0.05,
        'contiene_documentos': 0.05, 'separa_tallas': 0.05, 'separa_color': 0.05,
        'separa_referencia': 0.05, 'etiquetado': 0.05, 'error_tiqueteo': 0.20,
        'homologacion': 0.05, 'precio': 0.05, 'novedad': 0.15,
        'factura': 0.10, 'orden_compra_doc': 0.05
    };
    var suma = 0, completo = true;
    $.each(pesos, function(campo, peso) {
        var val = parseInt(\$('#calificacionproveedor-' + campo).val());
        if (isNaN(val) || val < 1) { completo = false; return false; }
        suma += val * peso;
    });
    if (completo) {
        var letra = puntajeALetra(suma);
        var color = letra === 'A' ? 'success' : (letra === 'B' ? 'warning' : 'danger');
        \$('#preview-calidad').html('<span class=\"badge badge-' + color + ' badge-pill px-3 py-2\" style=\"font-size:1.1rem\">' + letra + ' (' + suma.toFixed(2) + '/5)</span>');
        \$('#preview-calidad-val').text(suma.toFixed(4));
    } else {
        \$('#preview-calidad').html('—');
        \$('#preview-calidad-val').text('');
    }
}

function recalcularTotal() {
    var oportVal  = parseFloat(\$('#preview-oportunidad-val').text());
    var cantVal   = parseFloat(\$('#preview-cantidad-val').text());
    var calVal    = parseFloat(\$('#preview-calidad-val').text());
    var cpRaw     = parseInt(\$('#calificacionproveedor-calidad_producto').val());
    var nIncum    = parseInt(\$('#campo-num-incumplimientos').val()) || 0;

    if (isNaN(oportVal) || isNaN(cantVal) || isNaN(calVal) || isNaN(cpRaw) || cpRaw < 1) {
        \$('#preview-total').html('<span class=\"text-muted small\">Complete todos los campos</span>');
        return;
    }

    // Calidad producto efectiva: (score + N×1) / (1 + N)
    var cpEfectivo = nIncum > 0 ? (cpRaw + nIncum) / (1 + nIncum) : cpRaw;

    // Pesos: oportunidad*10% + cantidad*30% + calidad_criterios*30% + calidad_producto_efectiva*30%
    var total = oportVal * 0.10 + cantVal * 0.30 + calVal * 0.30 + cpEfectivo * 0.30;
    var letra = puntajeALetra(total);
    var color = letra === 'A' ? 'success' : (letra === 'B' ? 'warning' : 'danger');
    \$('#preview-total').html('<span class=\"badge badge-' + color + ' badge-pill px-3 py-2\" style=\"font-size:1.3rem\">' + letra + ' (' + total.toFixed(2) + '/5)</span>');

    // Preview calidad_producto: mostrar score ingresado y efectivo si hay incumplimientos
    var cpLetra = puntajeALetra(cpEfectivo);
    var cpColor = cpLetra === 'A' ? 'success' : (cpLetra === 'B' ? 'warning' : 'danger');
    var cpHtml  = '<span class=\"badge badge-' + cpColor + '\">' + cpLetra + ' (' + cpEfectivo.toFixed(2) + ')</span>';
    if (nIncum > 0) {
        cpHtml += ' <small class=\"text-danger ml-1\">ingresado: ' + cpRaw + '/5, ' + nIncum + ' incumpl.</small>';
    }
    \$('#preview-calidad-producto').html(cpHtml);
}

function puntajeALetra(score) {
    var pct = score / 5;
    if (pct < 0.80)  return 'C';
    if (pct <= 0.90) return 'B';
    return 'A';
}

// Recalcular oportunidad al cambiar fechas o incumplimientos
\$('#calificacionproveedor-fecha_entrega_cita, #calificacionproveedor-fecha_entrega_oc').on('change', function() {
    calcularOportunidadPreview();
    recalcularTotal();
});

// Calcular cantidad en tiempo real
\$('#calificacionproveedor-unidades_ordenadas, #calificacionproveedor-unidades_entregadas').on('input', function() {
    calcularCantidadPreview();
    recalcularTotal();
});

function calcularCantidadPreview() {
    var ord  = parseInt(\$('#calificacionproveedor-unidades_ordenadas').val());
    if (!ord || isNaN(ord)) return;
    var ent  = parseInt(\$('#calificacionproveedor-unidades_entregadas').val()) || 0;
    var pct   = ent / ord;
    var score = pct >= 1.0 ? 5 : (pct >= 0.80 ? 4 : 1);
    var pctStr = (pct * 100).toFixed(1) + '%';
    var color = score === 5 ? 'success' : (score === 4 ? 'warning' : 'danger');
    \$('#preview-cantidad').html('<span class=\"badge badge-' + color + '\">' + pctStr + ' → ' + score + '/5</span>');
    \$('#preview-cantidad-val').text(score);
}

function calcularOportunidadPreview() {
    var cita = \$('#calificacionproveedor-fecha_entrega_cita').val();
    var oc   = \$('#calificacionproveedor-fecha_entrega_oc').val();
    if (!cita || !oc) return;

    var citaDate = new Date(cita);
    var ocDate   = new Date(oc);
    if (isNaN(citaDate) || isNaN(ocDate)) return;

    // Score binario de la entrega actual: 5 (a tiempo) | 1 (tarde)
    var scoreActual = (ocDate <= citaDate) ? 5 : 1;

    // Incumplimientos previos de esta OC: cada uno aporta 1 al pool
    var n     = parseInt(\$('#campo-num-incumplimientos').val()) || 0;
    var score = n > 0 ? (n + scoreActual) / (n + 1) : scoreActual;

    var letra = puntajeALetra(score);
    var color = letra === 'A' ? 'success' : (letra === 'B' ? 'warning' : 'danger');
    var lbl   = letra + ' (' + score.toFixed(2) + ')';
    if (n > 0) {
        lbl += ' <small class=\"text-muted font-weight-normal\">· ' + (n + 1) + ' intentos</small>';
    }
    \$('#preview-oportunidad').html('<span class=\"badge badge-' + color + '\">' + lbl + '</span>');
    \$('#preview-oportunidad-val').text(score.toFixed(4));
}

// Calcular todo al cargar la página (campos pre-llenados desde OC)
\$(document).ready(function() {
    calcularOportunidadPreview();
    calcularCantidadPreview();
    recalcularCalidad();
    actualizarFormulaPreview();
    recalcularTotal();
});

// Agregar incumplimiento vía AJAX
\$('#btn-add-incumplimiento').on('click', function() {
    var desc = \$('#nuevo-incumplimiento').val().trim();
    if (!desc) {
        \$('#nuevo-incumplimiento').addClass('is-invalid').focus();
        return;
    }
    \$('#nuevo-incumplimiento').removeClass('is-invalid');
    \$(this).prop('disabled', true);

    \$.post('<?= $urlAddIncumplimiento ?>', {
        '_csrf':       yii.getCsrfToken(),
        'id_oc':       '<?= (int)$model->id_ordendecompra ?>',
        'numero_oc':   '<?= addslashes($model->numero_oc ?? '') ?>',
        'descripcion': desc
    }, function(data) {
        if (data.success) {
            \$('#badge-incumplimientos').text(data.total);
            \$('#campo-num-incumplimientos').val(data.total);
            \$('#sin-incumplimientos').hide();
            var html = '<div class=\"alert alert-warning py-1 px-2 mb-1 small d-flex justify-content-between align-items-start\">'
                     + '<span><i class=\"fas fa-times-circle text-danger mr-1\"></i>' + \$('<div>').text(desc).html() + '</span>'
                     + '<small class=\"text-muted ml-2 text-nowrap\">' + (data.fecha || '') + '</small>'
                     + '</div>';
            \$('#lista-incumplimientos').append(html);
            \$('#nuevo-incumplimiento').val('');
            actualizarFormulaPreview();
            calcularOportunidadPreview();
            recalcularTotal();
        } else {
            alert('Error: ' + (data.error || 'No se pudo guardar'));
        }
    }).always(function() {
        \$('#btn-add-incumplimiento').prop('disabled', false);
    });
});

// Selector de subcategorías (modo con OC)
\$(document).on('click', '.btn-subcatsel', function() {
    var sub = \$(this).data('sub');
    var cat = \$(this).data('cat');
    var ord = \$(this).data('ord');
    var ent = \$(this).data('ent');

    \$('.btn-subcatsel').removeClass('btn-warning').each(function() {
        var wasCalif = \$(this).find('.fa-check-circle').length > 0;
        \$(this).addClass(wasCalif ? 'btn-outline-success' : 'btn-outline-secondary');
    });
    \$(this).removeClass('btn-outline-secondary btn-outline-success').addClass('btn-warning');

    \$('#campo-subcategoria').val(sub);
    \$('#campo-categoria').val(cat);
    \$('#calificacionproveedor-unidades_ordenadas').val(ord);
    \$('#calificacionproveedor-unidades_entregadas').val(ent);

    calcularCantidadPreview();
    recalcularTotal();
});
"); ?>

<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-star text-warning"></i> <?= Html::encode($this->title) ?></h4>
        <div>
            <?php if ($urlPonderado): ?>
                <?= Html::a('<i class="fas fa-chart-bar"></i> Ver Ponderado OC', $urlPonderado, ['class' => 'btn btn-info mr-2']) ?>
            <?php endif ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['/calificacion/calificacion/index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <?php if ($tieneSubcats): ?>
    <!-- Panel selector de subcategorías -->
    <div class="card border-warning shadow-sm mb-3">
        <div class="card-header bg-warning text-dark py-2">
            <i class="fas fa-layer-group"></i>
            <strong>Seleccione la subcategoría a calificar</strong>
            <span class="badge badge-dark ml-2"><?= count($subcategorias) ?> subcategorías en esta OC</span>
        </div>
        <div class="card-body p-2">
            <div class="row">
            <?php foreach ($subcategorias as $sc): ?>
                <?php
                    $sub   = $sc['subcategoria'];
                    $calif = $yaCalificadas[$sub] ?? null;
                    $activa = $model->subcategoria === $sub;
                ?>
                <div class="col-md-3 col-sm-6 mb-2">
                    <button type="button"
                            class="btn btn-block btn-subcatsel <?= $activa ? 'btn-warning' : ($calif ? 'btn-outline-success' : 'btn-outline-secondary') ?>"
                            data-sub="<?= Html::encode($sub) ?>"
                            data-cat="<?= Html::encode($sc['categoria']) ?>"
                            data-ord="<?= (int)$sc['unidades_ordenadas'] ?>"
                            data-ent="<?= (int)$sc['unidades_entregadas'] ?>"
                            style="font-size:0.82rem; padding:6px 8px;">
                        <?php if ($calif): ?>
                            <i class="fas fa-check-circle text-success"></i>
                        <?php else: ?>
                            <i class="fas fa-circle text-secondary"></i>
                        <?php endif ?>
                        <?= Html::encode($sub) ?>
                        <?php if ($calif): ?>
                            <br><span class="badge badge-<?= Calificacionproveedor::letraClase(Calificacionproveedor::puntajeALetra($calif->puntaje_total)) ?>">
                                <?= Calificacionproveedor::puntajeALetra($calif->puntaje_total) ?>
                                (<?= number_format((float)$calif->puntaje_total, 2) ?>)
                            </span>
                        <?php endif ?>
                    </button>
                </div>
            <?php endforeach ?>
            </div>
        </div>
    </div>
    <?php endif ?>

    <?php $form = ActiveForm::begin(['id' => 'form-calificacion']); ?>

    <div class="row">

        <!-- ======== COL IZQUIERDA: Datos OC ======== -->
        <div class="col-md-4">

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <i class="fas fa-file-alt"></i> Datos de la Orden de Compra
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'numero_oc')->textInput(['maxlength' => 50, 'readonly' => $tieneOc, 'style' => $tieneOc ? 'background:#f8f9fa;' : '']) ?>
                    <?php if ($tieneOc): ?>
                    <div class="form-group">
                        <label class="control-label">Proveedor</label>
                        <p class="form-control-plaintext font-weight-bold" style="font-size:0.9rem; padding-top:4px;">
                            <i class="fas fa-building text-secondary mr-1"></i>
                            <?= Html::encode($model->proveedor ?: '—') ?>
                        </p>
                        <?= Html::hiddenInput('Calificacionproveedor[id_proveedor]', $model->id_proveedor, ['id' => 'calificacionproveedor-id_proveedor']) ?>
                    </div>
                    <?php else: ?>
                    <?= $form->field($model, 'id_proveedor')->widget(Select2::class, [
                        'data'          => $proveedoresMap,
                        'options'       => ['placeholder' => 'Seleccione proveedor...', 'id' => 'sel-proveedor'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                    <?php endif ?>
                    <?php /* Categoría y subcategoría siempre readonly cuando viene de OC */ ?>
                    <div class="form-group">
                        <label class="control-label">Categoría</label>
                        <input type="text" id="campo-categoria" class="form-control form-control-sm"
                               name="Calificacionproveedor[categoria]"
                               value="<?= Html::encode($model->categoria) ?>"
                               readonly style="background:#f8f9fa; cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Subcategoría</label>
                        <input type="text" id="campo-subcategoria" class="form-control form-control-sm"
                               name="Calificacionproveedor[subcategoria]"
                               value="<?= Html::encode($model->subcategoria) ?>"
                               readonly style="background:#f8f9fa; cursor:not-allowed;">
                    </div>
                    <?= $form->field($model, 'tipo_mercancia')->textInput(['maxlength' => 100]) ?>
                    <?= $form->field($model, 'producto')->textInput(['maxlength' => 200]) ?>
                    <?= $form->field($model, 'transportadora')->textInput(['maxlength' => 100]) ?>
                    <?= $form->field($model, 'revisado_por')
                        ->textInput([
                            'maxlength'   => 500,
                            'placeholder' => 'Ej: juan.perez, maria.garcia...',
                        ])
                        ->hint('<i class="fas fa-users text-secondary"></i> Puede ingresar varios nombres separados por coma.') ?>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <i class="fas fa-boxes"></i> Unidades y Fechas
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <?= $form->field($model, 'unidades_ordenadas')->textInput(['type' => 'number', 'min' => 0]) ?>
                        </div>
                        <div class="col-6">
                            <?= $form->field($model, 'unidades_entregadas')
                                ->textInput([
                                    'type'     => 'number',
                                    'min'      => 0,
                                    'readonly' => true,
                                    'style'    => 'background:#f8f9fa; cursor:not-allowed;',
                                ])
                                ->hint('<i class="fas fa-lock text-secondary"></i> Se actualiza al legalizar el conteo de la OC.') ?>
                        </div>
                    </div>
                    <?= $form->field($model, 'fecha_entrega_cita')->widget(DatePicker::class, [
                        'language'      => 'es',
                        'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd'],
                    ]) ?>
                    <?= $form->field($model, 'fecha_entrega_oc')->widget(DatePicker::class, [
                        'language'      => 'es',
                        'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd'],
                    ]) ?>
                </div>
            </div>

            <!-- Incumplimientos de la OC -->
            <?php if ($tieneOc): ?>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header py-2" style="background:#f8d7da; color:#721c24;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Incumplimientos</strong>
                    <span class="badge badge-danger ml-1" id="badge-incumplimientos"><?= $numIncumplimientos ?></span>
                    <small class="ml-2" style="color:#721c24;">Penalizan el puntaje de Calidad del Producto</small>
                </div>
                <div class="card-body p-2">
                    <?= Html::hiddenInput(
                        'Calificacionproveedor[num_incumplimientos]',
                        $numIncumplimientos,
                        ['id' => 'campo-num-incumplimientos']
                    ) ?>
                    <div id="lista-incumplimientos">
                        <?php if (empty($incumplimientos)): ?>
                            <div id="sin-incumplimientos" class="text-muted small py-1">
                                <i class="fas fa-check-circle text-success mr-1"></i>Sin incumplimientos registrados.
                            </div>
                        <?php else: ?>
                            <div id="sin-incumplimientos" style="display:none;"></div>
                            <?php foreach ($incumplimientos as $inc): ?>
                            <div class="alert alert-warning py-1 px-2 mb-1 small d-flex justify-content-between align-items-start">
                                <span><i class="fas fa-times-circle text-danger mr-1"></i><?= Html::encode($inc->descripcion) ?></span>
                                <small class="text-muted ml-2 text-nowrap"><?= substr($inc->created_at ?? '', 0, 10) ?></small>
                            </div>
                            <?php endforeach ?>
                        <?php endif ?>
                    </div>
                    <div class="mt-2 border-top pt-2">
                        <div class="input-group input-group-sm">
                            <input type="text" id="nuevo-incumplimiento" class="form-control"
                                   placeholder="Descripción del incumplimiento a registrar...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-danger" id="btn-add-incumplimiento">
                                    <i class="fas fa-plus"></i> Registrar
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">
                            Fórmula: (puntaje + <?= $numIncumplimientos ?> incumpl.) / (1 + <?= $numIncumplimientos ?>)
                            <span id="formula-preview" class="font-weight-bold text-danger"></span>
                        </small>
                    </div>
                </div>
            </div>
            <?php endif ?>

            <!-- Preview scores calculados -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-info text-white py-2">
                    <i class="fas fa-calculator"></i> Puntajes Calculados (preview)
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm mb-0" style="font-size:0.82rem;">
                        <tr>
                            <td class="text-muted align-middle" style="width:55%">
                                Oportunidad
                                <span class="badge badge-secondary ml-1">10%</span>
                            </td>
                            <td class="align-middle" id="preview-oportunidad">
                                <span class="text-muted">—</span>
                            </td>
                            <td style="display:none" id="preview-oportunidad-val"></td>
                        </tr>
                        <tr>
                            <td class="text-muted align-middle">
                                Cantidad
                                <span class="badge badge-secondary ml-1">30%</span>
                            </td>
                            <td class="align-middle" id="preview-cantidad">
                                <span class="text-muted">—</span>
                            </td>
                            <td style="display:none" id="preview-cantidad-val"></td>
                        </tr>
                        <tr>
                            <td class="text-muted align-middle">
                                Calidad Criterios
                                <span class="badge badge-secondary ml-1">30%</span>
                            </td>
                            <td class="align-middle">
                                <span id="preview-calidad"><span class="text-muted">—</span></span>
                                <span style="display:none" id="preview-calidad-val"></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted align-middle">
                                Calidad Producto
                                <span class="badge badge-dark ml-1">30%</span>
                            </td>
                            <td class="align-middle" id="preview-calidad-producto">
                                <span class="text-muted">—</span>
                            </td>
                        </tr>
                        <tr style="background:#e9ecef; border-top:2px solid #dee2e6;">
                            <td class="align-middle font-weight-bold" style="font-size:0.9rem;">
                                <i class="fas fa-star text-warning mr-1"></i> PUNTAJE TOTAL
                            </td>
                            <td class="align-middle" id="preview-total">
                                <span class="text-muted">—</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>

        <!-- ======== COL DERECHA: Criterios ======== -->
        <div class="col-md-8">

            <!-- CALIDAD DEL PRODUCTO (destacado) -->
            <div class="card shadow-sm border-0 mb-3 criterio-producto">
                <div class="card-header py-2" style="background:#d4edda; color:#155724;">
                    <i class="fas fa-box-open"></i>
                    <strong>Calidad del Producto</strong>
                    <span class="badge badge-success ml-1">30% del puntaje total</span>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        <strong>Escala:</strong> 1=Muy malo &nbsp;|&nbsp; 2=Malo &nbsp;|&nbsp; 3=Regular &nbsp;|&nbsp; 4=Bueno &nbsp;|&nbsp; 5=Excelente
                    </p>
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <?= $form->field($model, 'calidad_producto', ['template' => '{label}{input}{error}'])
                                ->dropDownList(
                                    ['' => '-- Seleccione --'] + $opciones,
                                    ['class' => 'form-control form-control-lg',
                                     'id'    => 'calificacionproveedor-calidad_producto',
                                     'style' => 'font-size:1.1rem; font-weight:bold;']
                                ) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Criterios de calidad con peso -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <i class="fas fa-clipboard-check"></i> Criterios de Calidad (calificación 1 a 5) <span class="badge badge-secondary ml-1">30% del puntaje total</span>
                </div>
                <div class="card-body">

                    <p class="text-muted small mb-3">
                        <strong>Escala:</strong> 1=Muy malo &nbsp;|&nbsp; 2=Malo &nbsp;|&nbsp; 3=Regular &nbsp;|&nbsp; 4=Bueno &nbsp;|&nbsp; 5=Excelente
                    </p>

                    <?php
                    $criterios = [
                        ['caja_bulto',          '5%',  'Caja / Bulto'],
                        ['calibre',             '5%',  'Calibre'],
                        ['rotulo',              '5%',  'Rótulo'],
                        ['contiene_documentos', '5%',  'Contiene Documentos'],
                        ['separa_tallas',       '5%',  'Separa Tallas'],
                        ['separa_color',        '5%',  'Separa Color'],
                        ['separa_referencia',   '5%',  'Separa Referencia'],
                        ['etiquetado',          '5%',  'Etiquetado'],
                        ['error_tiqueteo',      '20%', 'Error Tiqueteo'],
                        ['homologacion',        '5%',  'Homologación'],
                        ['precio',              '5%',  'Precio'],
                        ['novedad',             '15%', 'Novedad'],
                        ['factura',             '10%', 'Factura'],
                        ['orden_compra_doc',    '5%',  'Orden de Compra (doc)'],
                    ];
                    ?>

                    <div class="row">
                    <?php foreach ($criterios as [$campo, $peso, $etiqueta]): ?>
                        <div class="col-md-6 mb-2">
                            <div class="card criterio-card p-2 h-100" <?= $peso === '20%' || $peso === '15%' ? 'style="border-left-color:#dc3545"' : '' ?>>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span style="font-size:0.82rem; font-weight:600;"><?= $etiqueta ?></span>
                                    <span class="badge badge-secondary"><?= $peso ?></span>
                                </div>
                                <?= $form->field($model, $campo, ['template' => '{input}{error}'])
                                    ->dropDownList(
                                        ['' => '-- Seleccione --'] + $opciones,
                                        ['class' => 'form-control form-control-sm score-select',
                                         'id'    => 'calificacionproveedor-' . $campo]
                                    )->label(false) ?>
                            </div>
                        </div>
                    <?php endforeach ?>
                    </div>

                </div>
            </div>

            <!-- Criterios informativos (gancho / tallero) -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-secondary text-white py-2">
                    <i class="fas fa-info-circle"></i> Criterios Informativos
                    <small class="ml-2 text-light">(opcionales — no afectan el puntaje)</small>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Estos criterios se registran solo como referencia. Déjelos vacíos si no aplican.
                    </p>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="card criterio-card-info p-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span style="font-size:0.82rem; font-weight:600;">Gancho</span>
                                    <span class="badge badge-light text-secondary border">Informativo</span>
                                </div>
                                <?= $form->field($model, 'gancho', ['template' => '{input}{error}'])
                                    ->dropDownList(
                                        ['' => '-- No aplica --'] + $opciones,
                                        ['class' => 'form-control form-control-sm',
                                         'id'    => 'calificacionproveedor-gancho']
                                    )->label(false) ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="card criterio-card-info p-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span style="font-size:0.82rem; font-weight:600;">Tallero</span>
                                    <span class="badge badge-light text-secondary border">Informativo</span>
                                </div>
                                <?= $form->field($model, 'tallero', ['template' => '{input}{error}'])
                                    ->dropDownList(
                                        ['' => '-- No aplica --'] + $opciones,
                                        ['class' => 'form-control form-control-sm',
                                         'id'    => 'calificacionproveedor-tallero']
                                    )->label(false) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <i class="fas fa-comment-alt"></i> Observación
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'observacion', ['template' => '{input}{error}'])
                        ->textarea(['rows' => 4, 'placeholder' => 'Novedades o comentarios...'])
                        ->label(false) ?>
                </div>
            </div>

            <div class="text-right">
                <?= Html::submitButton(
                    '<i class="fas fa-save"></i> ' . ($isNew ? 'Guardar Calificación' : 'Actualizar'),
                    ['class' => 'btn btn-success btn-lg']
                ) ?>
            </div>

        </div>
    </div><!-- /row -->

    <?php ActiveForm::end(); ?>

</div>
