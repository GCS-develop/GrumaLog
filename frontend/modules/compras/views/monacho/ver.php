<?php
use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\Alert;
use common\models\MonachoParser;
use kartik\icons\Icon;

Icon::map($this, Icon::FAS);

$this->title = 'Pedido Monacho #' . $pedido->id;
$this->params['breadcrumbs'][] = ['label' => 'Compras',       'url' => ['/compras/importacion/index']];
$this->params['breadcrumbs'][] = ['label' => 'Pedido Monacho','url' => ['lista']];
$this->params['breadcrumbs'][] = $this->title;

// Totales
$totalArts = count($articulos);
$totalSubs = 0;
$totalUds  = 0;
foreach ($articulos as $art) {
    foreach ($art->detalles as $d) {
        $totalSubs++;
        $totalUds += $d->cantidad;
    }
}

$this->registerCss('
    .kpi-row { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
    .kpi-box { flex:1; min-width:130px; background:#fff; border-radius:8px;
               box-shadow:0 2px 10px rgba(0,0,0,.07); padding:14px 16px; text-align:center; }
    .kpi-box .num { font-size:24px; font-weight:700; color:#1a6b3a; }
    .kpi-box .lbl { font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.4px; }
    .prod-card { border:1px solid #dde5d8; border-radius:8px; margin-bottom:16px; overflow:hidden; }
    .prod-head { background:#edf5ee; padding:10px 14px; display:flex; justify-content:space-between;
                 align-items:flex-start; flex-wrap:wrap; gap:6px; }
    .prod-title { font-weight:600; font-size:14px; color:#0d4a28; }
    .prod-meta  { font-size:12px; color:#555; margin-top:2px; }
    .badge-code { background:#1a6b3a; color:#fff; border-radius:20px;
                  padding:2px 10px; font-size:12px; font-weight:600; }
    .ean-tbl { width:100%; font-size:13px; border-collapse:collapse; }
    .ean-tbl th { background:#1a6b3a; color:#fff; padding:7px 12px; font-weight:500; text-align:center; }
    .ean-tbl td { padding:6px 12px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
    .ean-tbl tr:last-child td { border:none; }
    .ean-tbl tr:nth-child(even) td { background:#f7fbf8; }
    .ean-tbl .total-row td { background:#edf5ee; font-weight:600; }
    .color-pill { background:#1a6b3a; color:#fff; border-radius:20px;
                  padding:2px 9px; font-size:12px; display:inline-block; }
    .ean-input { font-family:monospace; font-size:13px; letter-spacing:1px;
                 width:155px; text-align:center; padding:4px 6px;
                 border:1px solid #ccc; border-radius:4px; }
    .ean-input.ok  { border-color:#28a745; background:#f0fff4; }
    .ean-input.err { border-color:#dc3545; background:#fff5f5; }
    .oc-chip { background:#fffbeb; border:1px solid #ffc107; border-radius:6px;
               padding:4px 9px; font-size:12px; display:inline-block; }
    .estado-chip { border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; display:inline-block; }
    .estado-BORRADOR  { background:#e9ecef; color:#495057; }
    .estado-ENVIADO   { background:#d4edda; color:#155724; }
    .estado-APROBADO  { background:#cce5ff; color:#004085; }
');
?>

<div class="monacho-ver">

    <?= Alert::widget() ?>

    <!-- Cabecera del pedido -->
    <div class="card mb-3" style="border:1px solid #dde5d8;border-radius:8px;overflow:hidden;">
        <div class="card-header" style="background:#edf5ee;border:none;padding:12px 16px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px">
                <div>
                    <h5 style="margin:0;font-weight:700;color:#0d4a28">
                        <i class="fas fa-clipboard-check mr-2"></i>
                        Pedido #<?= $pedido->id ?> — <?= Html::encode($pedido->proveedor) ?>
                    </h5>
                    <small class="text-muted">
                        <?= $pedido->created_at ? 'Creado ' . date('d/m/Y H:i', strtotime($pedido->created_at)) : '' ?>
                        <?php if ($pedido->usuariocrea): ?>
                            · <?= Html::encode($pedido->usuariocrea->username) ?>
                        <?php endif; ?>
                    </small>
                </div>
                <span class="estado-chip estado-<?= $pedido->estado ?>">
                    <?php
                    if ($pedido->estado === 'ENVIADO')   echo '✓ Enviado';
                    elseif ($pedido->estado === 'APROBADO') echo '✔ Aprobado';
                    else echo '✏ Borrador';
                    ?>
                </span>
            </div>
        </div>
        <div class="card-body py-2 px-3">
            <div class="row">
                <?php if ($pedido->oc_siesa): ?>
                <div class="col-auto">
                    <span class="oc-chip"><i class="fas fa-file-alt mr-1"></i> OC SIESA: <strong><?= Html::encode($pedido->oc_siesa) ?></strong></span>
                </div>
                <?php endif; ?>
                <?php if ($pedido->oc_icg): ?>
                <div class="col-auto">
                    <span class="oc-chip"><i class="fas fa-file-alt mr-1"></i> OC ICG: <strong><?= Html::encode($pedido->oc_icg) ?></strong></span>
                </div>
                <?php endif; ?>
                <?php if ($pedido->fecha_despacho): ?>
                <div class="col-auto">
                    <span class="prod-meta"><i class="fas fa-calendar-alt mr-1"></i> Despacho: <strong><?= date('d/m/Y', strtotime($pedido->fecha_despacho)) ?></strong></span>
                </div>
                <?php endif; ?>
                <?php if ($pedido->notas): ?>
                <div class="col-12 mt-1">
                    <small class="text-muted"><i class="fas fa-sticky-note mr-1"></i><?= Html::encode($pedido->notas) ?></small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-row">
        <div class="kpi-box"><div class="num"><?= $totalArts ?></div><div class="lbl">Artículos</div></div>
        <div class="kpi-box"><div class="num"><?= $totalSubs ?></div><div class="lbl">Subartículos (EAN)</div></div>
        <div class="kpi-box"><div class="num"><?= number_format($totalUds) ?></div><div class="lbl">Unidades</div></div>
        <div class="kpi-box"><div class="num" style="font-size:13px;padding-top:5px"><?= Html::encode($pedido->proveedor) ?></div><div class="lbl">Proveedor</div></div>
    </div>

    <!-- Acciones -->
    <div class="d-flex flex-wrap mb-3" style="gap:8px">
        <?php if ($pedido->estado !== 'APROBADO'): ?>
        <a href="<?= Url::to(['editar','id'=>$pedido->id]) ?>" class="btn btn-warning">
            <i class="fas fa-edit mr-1"></i> Editar Pedido
        </a>
        <?php endif; ?>

        <a href="<?= Url::to(['pdf','id'=>$pedido->id]) ?>" target="_blank" class="btn btn-danger">
            <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
        </a>

        <button type="button" class="btn btn-info" onclick="document.getElementById('modal-email').style.display='flex'">
            <i class="fas fa-envelope mr-1"></i> Enviar al Proveedor
        </button>

        <a href="<?= Url::to(['exportar-excel-pedido','id'=>$pedido->id]) ?>" class="btn btn-success">
            <i class="fas fa-file-excel mr-1"></i> Excel SIESA
        </a>

        <?php if ($pedido->estado === 'BORRADOR'): ?>
        <a href="<?= Url::to(['aprobar','id'=>$pedido->id]) ?>"
           class="btn btn-primary ml-auto"
           onclick="return confirm('¿Aprobar el pedido #<?= $pedido->id ?>? No podrá editarlo después.')">
            <i class="fas fa-check-circle mr-1"></i> Aprobar Pedido
        </a>
        <?php elseif ($pedido->estado === 'APROBADO'): ?>
        <button type="button" class="btn btn-dark ml-auto" onclick="asignarCodigos(<?= $pedido->id ?>)">
            <i class="fas fa-barcode mr-1"></i> Generar Códigos + EAN
        </button>
        <?php endif; ?>

        <a href="<?= Url::to(['lista']) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>

        <a href="<?= Url::to(['eliminar','id'=>$pedido->id]) ?>"
           class="btn btn-outline-danger"
           onclick="return confirm('¿Eliminar pedido #<?= $pedido->id ?>?')">
            <i class="fas fa-trash mr-1"></i> Eliminar
        </a>
    </div>

    <!-- Modal enviar email -->
    <div id="modal-email" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:8px;padding:24px;width:480px;max-width:95%;">
            <h5 style="margin:0 0 16px 0"><i class="fas fa-envelope mr-2 text-info"></i>Enviar Pedido al Proveedor</h5>
            <form method="POST" action="<?= Url::to(['enviar-email','id'=>$pedido->id]) ?>">
                <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                <div class="form-group">
                    <label><strong>Email del Proveedor *</strong></label>
                    <input type="email" name="email_destino" class="form-control" required placeholder="proveedor@ejemplo.com">
                </div>
                <div class="form-group">
                    <label>Mensaje</label>
                    <textarea name="email_cuerpo" class="form-control" rows="4"><?= "Estimados,\n\nAdjunto encontrarán el Pedido Monacho #{$pedido->id} — {$pedido->proveedor}.\n\nSaludos,\nGRUMALog" ?></textarea>
                </div>
                <div class="d-flex justify-content-end" style="gap:8px">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-email').style.display='none'">Cancelar</button>
                    <button type="submit" class="btn btn-info"><i class="fas fa-paper-plane mr-1"></i> Enviar PDF por Email</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Artículos -->
    <?php foreach ($articulos as $art): ?>
    <?php
    // Agrupar detalles por color
    $coloresAgrupados = [];
    foreach ($art->detalles as $d) {
        $coloresAgrupados[$d->color][] = $d;
    }
    // Calcular total del artículo
    $artTotal = array_sum(array_map(function($d) { return $d->cantidad; }, $art->detalles));
    ?>
    <div class="prod-card">
        <div class="prod-head">
            <div>
                <div class="prod-title">
                    <span class="badge-code"><?= Html::encode($art->codigo) ?></span>
                    &nbsp;<?= Html::encode($art->descripcion) ?>
                </div>
                <div class="prod-meta">
                    <?php if ($art->referencia): ?>Ref: <strong><?= Html::encode($art->referencia) ?></strong> &nbsp;·&nbsp; <?php endif; ?>
                    Costo: <strong>$<?= number_format((float)$art->costo, 0, ',', '.') ?></strong>
                    &nbsp;·&nbsp;
                    P.Vta: <strong>$<?= number_format((float)$art->precio_venta, 0, ',', '.') ?></strong>
                    <?php if ($art->rango): ?>&nbsp;·&nbsp; Rango: <strong><?= Html::encode($art->rango) ?></strong><?php endif; ?>
                </div>
                <?php
                $estilos = array_filter([$art->estilo1,$art->estilo2,$art->estilo3,$art->estilo4,$art->estilo5]);
                if ($estilos): ?>
                <div style="font-size:11px;color:#777;margin-top:3px"><?= implode(' · ', array_map('htmlspecialchars', $estilos)) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div style="padding:0 14px 14px">
            <table class="ean-tbl mt-3">
                <thead>
                    <tr>
                        <th style="text-align:left">Color</th>
                        <th>Talla</th>
                        <th>Cantidad</th>
                        <th>EAN13</th>
                        <th>✓</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coloresAgrupados as $color => $detalles): ?>
                    <?php foreach ($detalles as $d): ?>
                    <?php $valid = $d->isValid(); ?>
                    <tr>
                        <td><span class="color-pill"><?= Html::encode($color) ?></span></td>
                        <td style="text-align:center;font-weight:600"><?= Html::encode($d->talla) ?></td>
                        <td style="text-align:center"><?= number_format($d->cantidad) ?></td>
                        <td style="text-align:center">
                            <input type="text"
                                   class="ean-input <?= $valid ? 'ok' : 'err' ?>"
                                   value="<?= Html::encode($d->ean13) ?>"
                                   maxlength="13"
                                   data-detalle-id="<?= $d->id ?>"
                                   onchange="actualizarEan(this)">
                        </td>
                        <td style="text-align:center">
                            <?php if ($valid): ?>
                                <i class="fas fa-check-circle text-success"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle text-danger"></i>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="2">TOTAL</td>
                        <td style="text-align:center"><?= number_format($artTotal) ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Botones al final -->
    <div class="d-flex flex-wrap mb-5" style="gap:8px;margin-top:8px">
        <a href="<?= Url::to(['pdf', 'id' => $pedido->id]) ?>" target="_blank" class="btn btn-danger">
            <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
        </a>
        <a href="<?= Url::to(['exportar-excel-pedido', 'id' => $pedido->id]) ?>" class="btn btn-success">
            <i class="fas fa-file-excel mr-1"></i> Exportar Excel SIESA
        </a>
        <a href="<?= Url::to(['lista']) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Lista
        </a>
    </div>

</div>

<?php $this->registerJs('
    var updateUrl      = "' . Url::to(['editar-ean-pedido']) . '";
    var asignarUrl     = "' . Url::to(['asignar-codigos']) . '";
    var csrf           = "' . Yii::$app->request->csrfToken . '";

    function validarEan(ean) {
        if (ean.length !== 13 || !/^\d{13}$/.test(ean)) return false;
        var sum = 0;
        for (var i = 0; i < 12; i++) {
            var d = parseInt(ean[i]);
            sum += (i%2===0) ? d : d*3;
        }
        return ((10-(sum%10))%10) === parseInt(ean[12]);
    }

    function actualizarEan(inp) {
        var ean  = inp.value.trim();
        var ok   = validarEan(ean);
        var icon = inp.closest("tr").querySelector(".fa-check-circle, .fa-times-circle");

        inp.classList.toggle("ok",  ok);
        inp.classList.toggle("err", !ok);
        if (icon) {
            icon.className = ok ? "fas fa-check-circle text-success"
                               : "fas fa-times-circle text-danger";
        }
        if (!ok) return;

        fetch(updateUrl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                "_csrf"     : csrf,
                "detalle_id": inp.dataset.detalleId,
                "ean"       : ean,
            })
        }).then(r => r.json()).then(data => {
            if (!data.success) {
                inp.classList.replace("ok", "err");
                console.warn(data.message);
            }
        }).catch(e => console.error(e));
    }

    document.querySelectorAll(".ean-input").forEach(function(i) {
        i.addEventListener("keypress", function(e) {
            if (!/[0-9]/.test(e.key)) e.preventDefault();
        });
    });

    function asignarCodigos(pedidoId) {
        if (!confirm("¿Asignar códigos consecutivos de SIESA y generar EAN13 para todos los artículos sin código?")) return;
        var btn = event.target;
        btn.disabled = true;
        btn.innerHTML = "<i class=\"fas fa-spinner fa-spin mr-1\"></i> Procesando...";
        fetch(asignarUrl + "?id=" + pedidoId, {method:"POST", headers:{"X-Csrf-Token":csrf}})
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) { alert(d.message); location.reload(); }
                else { alert("Error: " + d.message); btn.disabled = false; btn.innerHTML = "<i class=\"fas fa-barcode mr-1\"></i> Generar Códigos + EAN"; }
            })
            .catch(function(e) { alert("Error de red: " + e); btn.disabled = false; btn.innerHTML = "<i class=\"fas fa-barcode mr-1\"></i> Generar Códigos + EAN"; });
    }
'); ?>
