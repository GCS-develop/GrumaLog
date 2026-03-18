<?php

use frontend\models\Bodegas;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\bootstrap5\Modal;
use yii\widgets\ActiveForm;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\InventarioSearch $searchModel */
/** @var array $skuRows */
/** @var array|null $totals */
/** @var bool $hasFilter */
/** @var array $bodegas */
/** @var bool $limited */

$this->title = 'Inventarios';
$this->params['breadcrumbs'][] = $this->title;

$hayFiltros = !empty($searchModel->codigoBodega)
    || !empty($searchModel->item)
    || !empty($searchModel->codigoBarras)
    || !empty($searchModel->color)
    || !empty($searchModel->talla);

// Pre-selección bodega para modal sync
$_bodega = $searchModel->codigoBodega;
$bodegaPresel = null;
if (is_array($_bodega) && count($_bodega) === 1) {
    $bodegaPresel = $_bodega[0];
} elseif (!empty($_bodega) && !is_array($_bodega)) {
    $bodegaPresel = $_bodega;
}

$bodegasParam     = is_array($searchModel->codigoBodega) ? $searchModel->codigoBodega : [];
$bodegasParamJson = Json::encode($bodegasParam);

$diffBruto = $totals ? abs($totals['grumaBruto'] - $totals['siesaBruto']) : 0;
$diffReal  = $totals ? abs($totals['grumaReal']  - $totals['siesaReal'])  : 0;

$syncUrl = Url::to(['inventario/sincronizar-inventario']);
$difUrl  = Url::to(['/catalogos/inventario/diferencias']);

$totalExistencia = array_sum(array_column($skuRows, 'existencia'));

$this->registerCss('
    .sku-expandable { cursor: pointer; }
    .sku-expandable:hover td { background: #e9ecef; }
    .sku-icon { transition: transform .2s; font-size: 11px; color: #6c757d; }
    .collapsed-row .sku-icon { transform: rotate(0deg); }
    .expanded-row  .sku-icon { transform: rotate(90deg); }
    .ean-sub-table { background: #f8f9fa; }
    .ean-sub-table td { padding: 3px 8px; font-size: 12px; }
    .total-card { border-left: 4px solid; }
    .total-card.gruma { border-color: #0d6efd; }
    .total-card.siesa { border-color: #198754; }
    #sync-spinner { display:none; text-align:center; padding:30px 0; }
    #sync-result  { display:none; }
    .diff-table   { font-size: 13px; }
    #modal-dif-body .nav-link { cursor: pointer; }
');

$csrfToken = Yii::$app->request->csrfToken;

$this->registerJs(<<<JS

// ── Sync AJAX ─────────────────────────────────────────────────────────────────
$('#form-sync-inventario').on('submit', function(e) {
    e.preventDefault();
    var bodega = $(this).find('[name="InventarioSearch[codigoBodega]"]').val();
    if (!bodega) { alert('Seleccione una bodega para sincronizar.'); return; }
    $('#sync-form-content').hide();
    $('#sync-spinner').show();
    $('#sync-result').hide();
    $.ajax({
        url: '{$syncUrl}', method: 'POST', data: $(this).serialize(), dataType: 'json',
        success: function(data) {
            $('#sync-spinner').hide();
            if (data.success) {
                $('#sync-result').html(
                    '<div class="alert alert-success"><i class="fas fa-check-circle"></i> <strong>Sincronización completada</strong>' +
                    (data.log ? '<pre class="mt-2 mb-2" style="max-height:250px;overflow:auto;font-size:12px;">' + data.log + '</pre>' : '') +
                    '<div class="text-end"><button class="btn btn-success btn-sm" id="btn-reload-sync"><i class="fas fa-sync"></i> Recargar</button>' +
                    ' <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button></div></div>'
                ).show();
            } else {
                $('#sync-result').html(
                    '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' +
                    (data.error || 'Error desconocido') + '</div>' +
                    '<button class="btn btn-secondary btn-sm" onclick="resetSyncModal()">← Volver</button>'
                ).show();
            }
        },
        error: function(xhr) {
            $('#sync-spinner').hide();
            $('#sync-result').html(
                '<div class="alert alert-danger">Error: ' + xhr.status + ' ' + xhr.statusText + '</div>' +
                '<button class="btn btn-secondary btn-sm" onclick="resetSyncModal()">← Volver</button>'
            ).show();
        }
    });
});
function resetSyncModal() {
    $('#sync-result').hide(); $('#sync-spinner').hide(); $('#sync-form-content').show();
}
$('#modalSyncInventario').on('hidden.bs.modal', function() { resetSyncModal(); });
$(document).on('click', '#btn-reload-sync', function() { location.reload(); });

// ── Diferencias AJAX ──────────────────────────────────────────────────────────
$('#btn-diferencias').on('click', function() {
    var bodegas = {$bodegasParamJson};
    var params  = bodegas.map(b => 'bodegas%5B%5D=' + encodeURIComponent(b)).join('&');
    var sep     = '{$difUrl}'.indexOf('?') !== -1 ? '&' : '?';
    var url     = '{$difUrl}' + (params ? sep + params : '');
    $('#modal-dif-body').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Analizando diferencias...</p></div>');
    $('#modalDiferencias').modal('show');
    $.getJSON(url, function(data) {
        $('#modal-dif-body').html(buildDifHtml(data));
    }).fail(function(xhr) {
        $('#modal-dif-body').html('<div class="alert alert-danger">Error: ' + xhr.statusText + '</div>');
    });
});

function buildDifHtml(d) {
    var t = d.totales;
    var fantasmaBadge = t.barcodesFantasma > 0 ? 'bg-danger' : 'bg-success';
    var html = '<ul class="nav nav-tabs mb-3">';
    html += '<li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-solo-gruma"><span class="badge bg-warning text-dark">' + t.soloGruma + '</span> Solo Gruma</a></li>';
    html += '<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-solo-siesa"><span class="badge bg-info">' + t.soloSiesa + '</span> Solo Siesa</a></li>';
    html += '<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-diferente"><span class="badge bg-danger">' + t.diferente + '</span> Exist. diferente</a></li>';
    html += '<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-fantasma">' +
            '<span class="badge ' + fantasmaBadge + '">' + t.barcodesFantasma + '</span> Barcodes dup.</a></li>';
    var splitBadge = t.skuSplits > 0 ? 'bg-danger' : 'bg-success';
    html += '<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-skusplit">' +
            '<span class="badge ' + splitBadge + '">' + t.skuSplits + '</span> SKU split ⚠</a></li>';
    html += '<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-nulos"><span class="badge ' + (t.itemsNulos > 0 ? 'bg-danger' : 'bg-success') + '">' + t.itemsNulos + '</span> Incompletos</a></li>';
    html += '</ul><div class="tab-content">';

    html += difTabTable('#tab-solo-gruma active', d.soloGruma, ['Código de barras','Existencia Gruma'], ['barras','existenciaGruma']);
    html += difTabTable('#tab-solo-siesa', d.soloSiesa, ['Código de barras','Existencia Siesa'], ['barras','existenciaSiesa']);

    // Tab existencia diferente
    html += '<div class="tab-pane" id="tab-diferente">';
    if (!d.diferente || d.diferente.length === 0) {
        html += '<p class="text-success mt-2"><i class="fas fa-check-circle"></i> Sin diferencias.</p>';
    } else {
        html += '<div style="max-height:380px;overflow:auto"><table class="table table-sm diff-table"><thead><tr><th>Código barras</th><th>Gruma</th><th>Siesa</th><th>Δ</th></tr></thead><tbody>';
        d.diferente.forEach(function(r) {
            var delta = r.gruma - r.siesa;
            html += '<tr><td>' + r.barras + '</td><td>' + r.gruma + '</td><td>' + r.siesa + '</td><td class="' + (delta > 0 ? 'text-danger' : 'text-success') + '">' + (delta > 0 ? '+' : '') + delta + '</td></tr>';
        });
        html += '</tbody></table></div>';
    }
    html += '</div>';

    // Tab barcodes fantasma ← NUEVO: causa del REAL desigual
    html += '<div class="tab-pane" id="tab-fantasma">';
    if (!d.barcodesFantasma || d.barcodesFantasma.length === 0) {
        html += '<p class="text-success mt-2"><i class="fas fa-check-circle"></i> ' +
                'No hay barcodes duplicados. Los totales REAL deberían coincidir.</p>';
    } else {
        html += '<div class="alert alert-warning py-2 mb-2 small">' +
                '<i class="fas fa-exclamation-triangle"></i> ' +
                'Estos códigos de barras están en <code>inventario</code> vinculados a <strong>más de un <code>idItem</code></strong>. ' +
                'El cálculo REAL los cuenta como SKUs separados y genera un total mayor al de Siesa. ' +
                'Para corregirlo hay que eliminar los registros duplicados de <code>inventario</code>.</div>';
        d.barcodesFantasma.forEach(function(bf) {
            var detail = d.detalleBarcodes && d.detalleBarcodes[bf.codigoBarras] ? d.detalleBarcodes[bf.codigoBarras] : [];
            html += '<div class="card mb-2"><div class="card-header py-1 bg-warning bg-opacity-25">' +
                    '<strong><code>' + bf.codigoBarras + '</code></strong>' +
                    ' &nbsp; Bodega: ' + bf.codigoBodega +
                    ' &nbsp; <span class="badge bg-danger">' + bf.totalItems + ' items distintos</span>' +
                    ' &nbsp; Existencia: ' + bf.existencia + '</div>';
            if (detail.length > 0) {
                html += '<div class="card-body p-0"><table class="table table-sm mb-0"><thead><tr class="table-secondary">' +
                        '<th>item (Siesa)</th><th>Color</th><th>Talla</th><th class="text-end">Existencia</th></tr></thead><tbody>';
                detail.forEach(function(row) {
                    html += '<tr><td>' + (row.item || '—') + '</td><td>' + (row.color || '—') + '</td>' +
                            '<td>' + (row.talla || '—') + '</td><td class="text-end">' + row.existencia + '</td></tr>';
                });
                html += '</tbody></table></div>';
            }
            html += '</div>';
        });
    }
    html += '</div>';

    // Tab SKU split ← LA CAUSA REAL de GrumaReal > SiesaReal
    html += '<div class="tab-pane" id="tab-skusplit">';
    if (!d.skuSplits || d.skuSplits.length === 0) {
        html += '<p class="text-success mt-2"><i class="fas fa-check-circle"></i> ' +
                'No hay item_exts de Siesa divididos en múltiples SKUs de Gruma. Los REAL deberían coincidir.</p>';
    } else {
        html += '<div class="alert alert-danger py-2 mb-2 small">' +
                '<i class="fas fa-exclamation-triangle"></i> ' +
                '<strong>Causa encontrada.</strong> Estos item_exts de Siesa (una sola fila en t400) ' +
                'están vinculados a barcodes que en Gruma pertenecen a <strong>SKUs con diferente ' +
                '(item / color / talla)</strong>. GrumaReal los suma por separado → sobreconteo.</div>';
        d.skuSplits.forEach(function(s) {
            var diffClass = s.diff > 0 ? 'text-danger' : 'text-success';
            html += '<div class="card mb-2 border-danger"><div class="card-header py-1 bg-danger bg-opacity-10">' +
                    '<strong>item_ext ' + s.item_ext_id + '</strong>' +
                    ' &nbsp; Siesa: <strong>' + s.siesaEx + '</strong>' +
                    ' &nbsp; GrumaReal: <strong>' + s.grumaReal + '</strong>' +
                    ' &nbsp; <span class="' + diffClass + '">Δ' + (s.diff > 0 ? '+' : '') + s.diff + '</span>' +
                    '<br><small class="text-muted">Barcodes: ' + s.barcodes.join(', ') + '</small>' +
                    '</div>';
            html += '<div class="card-body p-0"><table class="table table-sm mb-0">' +
                    '<thead><tr class="table-secondary"><th>Item Gruma</th><th>Color</th><th>Talla</th>' +
                    '<th class="text-end">Existencia en grupo</th></tr></thead><tbody>';
            s.grupos.forEach(function(g) {
                html += '<tr><td>' + (g.item || '—') + '</td><td>' + (g.color || '—') + '</td>' +
                        '<td>' + (g.talla || '—') + '</td><td class="text-end">' + g.existencia + '</td></tr>';
            });
            html += '</tbody></table></div></div>';
        });
    }
    html += '</div>';

    // Tab items incompletos
    html += '<div class="tab-pane" id="tab-nulos">';
    if (!d.itemsNulos || d.itemsNulos.length === 0) {
        html += '<p class="text-success mt-2"><i class="fas fa-check-circle"></i> Todos los items tienen datos completos.</p>';
    } else {
        html += '<div style="max-height:350px;overflow:auto"><table class="table table-sm diff-table"><thead><tr><th>Cód. Item</th><th>idColor</th><th>idTalla</th><th>Registros</th><th>Existencia</th></tr></thead><tbody>';
        d.itemsNulos.forEach(function(r) {
            html += '<tr class="table-warning"><td>' + (r.itemCodigo ?? '—') + '</td><td>' + (r.idColor ?? '<em class="text-danger">NULL</em>') + '</td><td>' + (r.idTalla ?? '<em class="text-danger">NULL</em>') + '</td><td>' + r.registros + '</td><td>' + r.existencia + '</td></tr>';
        });
        html += '</tbody></table></div>';
    }
    html += '</div>';

    html += '</div>'; // tab-content
    return html;
}

function difTabTable(cls, rows, headers, keys) {
    var isActive = cls.indexOf('active') !== -1;
    var id = cls.replace('active','').trim();
    var html = '<div class="tab-pane ' + (isActive ? 'active' : '') + '" id="' + id + '">';
    if (rows.length === 0) {
        html += '<p class="text-success mt-2"><i class="fas fa-check-circle"></i> Sin registros.</p>';
    } else {
        html += '<div style="max-height:380px;overflow:auto"><table class="table table-sm diff-table"><thead><tr>';
        headers.forEach(h => { html += '<th>' + h + '</th>'; });
        html += '</tr></thead><tbody>';
        rows.forEach(function(r) { html += '<tr>'; keys.forEach(k => { html += '<td>' + r[k] + '</td>'; }); html += '</tr>'; });
        html += '</tbody></table></div>';
    }
    html += '</div>';
    return html;
}

JS
);
?>

<link rel="stylesheet" href="css/shared.css">

<div class="inventario-index mb-3">

    <?php /* ── Barra de acciones ─── */ ?>
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">

        <?= Html::button('<i class="fas fa-sync-alt"></i> Sincronizar', [
            'class'          => 'btn btn-success',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#modalSyncInventario',
        ]) ?>

        <?= Html::button(
            '<i class="fas fa-not-equal"></i> Ver diferencias' .
            ($diffReal > 0 ? ' <span class="badge bg-danger ms-1">' . $diffReal . '</span>' : ''),
            [
                'id'    => 'btn-diferencias',
                'class' => 'btn btn-outline-danger',
                'title' => 'Comparar existencias Gruma vs Siesa',
            ]
        ) ?>

        <?= Html::button(
            '<i class="fas fa-filter"></i> Filtros' .
            ($hayFiltros ? ' <span class="badge bg-warning text-dark ms-1">activos</span>' : ''),
            [
                'class'          => 'btn btn-outline-secondary',
                'data-bs-toggle' => 'collapse',
                'data-bs-target' => '#panel-filtros',
                'aria-expanded'  => (!$hasFilter || $hayFiltros) ? 'true' : 'false',
            ]
        ) ?>

    </div>

    <?= Alert::widget() ?>

    <?php /* ── Panel de filtros ─── */ ?>
    <div id="panel-filtros" class="collapse <?= (!$hasFilter || $hayFiltros) ? 'show' : '' ?>">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>

    <?php if (!$hasFilter): ?>

        <?php /* ── Estado vacío ─── */ ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-warehouse fa-4x mb-3 d-block"></i>
            <h5>Seleccione una o más bodegas para ver el inventario</h5>
            <p class="mb-0">Use el panel de filtros de arriba para elegir las bodegas que desea consultar.</p>
        </div>

    <?php else: ?>

        <?php /* ── Tarjetas de totales ─── */ ?>
        <?php if ($totals): ?>
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="card total-card gruma h-100">
                    <div class="card-body py-2 px-3">
                        <div class="small text-muted">Gruma BRUTO</div>
                        <div class="fs-5 fw-bold"><?= number_format($totals['grumaBruto']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card total-card siesa h-100">
                    <div class="card-body py-2 px-3">
                        <div class="small text-muted">Siesa BRUTO</div>
                        <div class="fs-5 fw-bold"><?= number_format($totals['siesaBruto']) ?>
                            <?php if ($diffBruto === 0): ?>
                                <span class="text-success fs-6">✓</span>
                            <?php else: ?>
                                <span class="badge bg-danger fs-6">Δ<?= $diffBruto ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card total-card gruma h-100">
                    <div class="card-body py-2 px-3">
                        <div class="small text-muted">Gruma REAL (por SKU)</div>
                        <div class="fs-5 fw-bold"><?= number_format($totals['grumaReal']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card total-card siesa h-100">
                    <div class="card-body py-2 px-3">
                        <div class="small text-muted">Siesa REAL (por SKU)</div>
                        <div class="fs-5 fw-bold"><?= number_format($totals['siesaReal']) ?>
                            <?php if ($diffReal === 0): ?>
                                <span class="text-success fs-6">✓</span>
                            <?php else: ?>
                                <span class="badge bg-danger fs-6">Δ<?= $diffReal ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php /* ── Encabezado tabla ─── */ ?>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted small">
                <strong><?= count($skuRows) ?> SKUs</strong>
                <?php if ($limited): ?>
                    <span class="badge bg-warning text-dark ms-1">Límite 1000 — refine los filtros</span>
                <?php endif; ?>
                &nbsp;·&nbsp; Existencia total: <strong><?= number_format($totalExistencia) ?></strong>
                &nbsp;·&nbsp; <i class="fas fa-chevron-right text-muted" style="font-size:11px"></i> Haz clic en una fila para ver sus EANs
            </span>
        </div>

        <?php /* ── Tabla SKU ─── */ ?>
        <div class="table-responsive">
        <table class="table table-sm table-hover table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:28px"></th>
                    <th>Bodega</th>
                    <th>Item</th>
                    <th>Color</th>
                    <th>Talla</th>
                    <th class="text-end">Existencia</th>
                    <th class="text-end" title="Cantidad de códigos de barras para esta variante">EANs</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($skuRows as $idx => $sku):
                $nEan     = count($sku['eans']);
                $canExpand = $nEan > 0;
                $rowClass  = $canExpand ? 'sku-expandable collapsed-row' : '';
                $collapseId = 'ean-' . $idx;
            ?>
                <tr class="<?= $rowClass ?>"
                    <?= $canExpand ? 'data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '"' : '' ?>>
                    <td class="text-center">
                        <?php if ($canExpand): ?>
                            <i class="fas fa-chevron-right sku-icon"></i>
                        <?php endif; ?>
                    </td>
                    <td><?= Html::encode($sku['codigoBodega']) ?></td>
                    <td><?= Html::encode($sku['item'] ?? '—') ?></td>
                    <td><?= Html::encode($sku['color'] ?? '—') ?></td>
                    <td><?= Html::encode($sku['talla'] ?? '—') ?></td>
                    <td class="text-end fw-semibold"><?= number_format((float)$sku['existencia']) ?></td>
                    <td class="text-end">
                        <?= $nEan > 1
                            ? Html::tag('span', $nEan, ['class' => 'badge bg-warning text-dark'])
                            : $nEan ?>
                    </td>
                </tr>
                <?php if ($canExpand): ?>
                <tr class="p-0">
                    <td colspan="7" class="p-0" style="border-top:none">
                        <div class="collapse" id="<?= $collapseId ?>">
                            <table class="table table-sm ean-sub-table mb-0 ms-4"
                                   style="width:calc(100% - 1.5rem)">
                                <thead>
                                    <tr class="table-secondary">
                                        <th>Código de barras</th>
                                        <th class="text-end">Existencia</th>
                                        <th>Última actualización</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sku['eans'] as $ean): ?>
                                    <tr>
                                        <td><code><?= Html::encode($ean['codigoBarras']) ?></code></td>
                                        <td class="text-end"><?= number_format((float)$ean['existencia']) ?></td>
                                        <td class="text-muted"><?= Html::encode($ean['fechaUltimaActualizacion'] ?? '—') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

    <?php endif; ?>

</div>


<?php /* ── Modal Sincronizar ─── */ ?>
<?php Modal::begin([
    'title' => '<strong><i class="fas fa-sync-alt"></i> Sincronizar inventario por bodega</strong>',
    'id'    => 'modalSyncInventario',
    'size'  => Modal::SIZE_DEFAULT,
]); ?>
<?php $form = ActiveForm::begin([
    'id'     => 'form-sync-inventario',
    'action' => ['inventario/sincronizar-inventario'],
    'method' => 'post',
]); ?>
<div id="sync-form-content">
    <?= Html::label('Bodega a sincronizar', 'sync-bodega', ['class' => 'form-label fw-semibold']) ?>
    <?= Html::dropDownList('InventarioSearch[codigoBodega]', $bodegaPresel, Bodegas::getListaDataCodigo(), [
        'id'     => 'sync-bodega',
        'prompt' => 'Seleccione una bodega...',
        'class'  => 'form-select mb-3',
    ]) ?>
    <div class="d-flex justify-content-end gap-2">
        <?= Html::button('Cancelar', ['class' => 'btn btn-secondary', 'data-bs-dismiss' => 'modal']) ?>
        <?= Html::submitButton('<i class="fas fa-play"></i> Ejecutar', ['class' => 'btn btn-primary']) ?>
    </div>
</div>
<div id="sync-spinner">
    <div class="spinner-border text-success" style="width:3rem;height:3rem;"></div>
    <p class="mt-3 text-muted">Sincronizando... esto puede tardar unos minutos.</p>
</div>
<div id="sync-result"></div>
<?php ActiveForm::end(); ?>
<?php Modal::end(); ?>


<?php /* ── Modal Diferencias ─── */ ?>
<?php Modal::begin([
    'title' => '<strong><i class="fas fa-not-equal"></i> Diferencias Gruma vs Siesa</strong>',
    'id'    => 'modalDiferencias',
    'size'  => Modal::SIZE_EXTRA_LARGE,
]); ?>
<div id="modal-dif-body"><p class="text-muted">Cargando...</p></div>
<?php Modal::end(); ?>
