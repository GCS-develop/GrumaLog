<?php
use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\Alert;
use common\models\MonachoParser;
use kartik\icons\Icon;

Icon::map($this, Icon::FAS);

$this->title = 'Vista Previa — Artículos y EAN13';
$this->params['breadcrumbs'][] = ['label' => 'Compras',         'url' => ['/compras/importacion/index']];
$this->params['breadcrumbs'][] = ['label' => 'Pedido Monacho',  'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Totales globales
$totalArts  = count($products);
$totalSubs  = 0;
$totalUds   = 0;
foreach ($products as $p) {
    foreach ($p['colores'] as $c) {
        foreach ($c['cantidades'] as $qty) {
            $totalSubs++;
            $totalUds += $qty;
        }
    }
}
$proveedor = $products[0]['proveedor'] ?? '-';

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
    .btn-export { background:#28a745; color:#fff; border:none; border-radius:6px;
                  padding:10px 24px; font-size:14px; font-weight:600; cursor:pointer; }
    .btn-export:hover { background:#1e7e34; }
    .oc-chip { background:#fffbeb; border:1px solid #ffc107; border-radius:6px;
               padding:4px 9px; font-size:12px; display:inline-block; }
    .styles-row { font-size:11px; color:#777; margin-top:3px; }
');
?>

<div class="monacho-preview">

    <?= Alert::widget() ?>

    <!-- KPIs -->
    <div class="kpi-row">
        <div class="kpi-box"><div class="num"><?= $totalArts ?></div><div class="lbl">Artículos</div></div>
        <div class="kpi-box"><div class="num"><?= $totalSubs ?></div><div class="lbl">Subartículos (EAN)</div></div>
        <div class="kpi-box"><div class="num"><?= number_format($totalUds) ?></div><div class="lbl">Unidades</div></div>
        <div class="kpi-box"><div class="num" style="font-size:13px;padding-top:5px"><?= Html::encode($proveedor) ?></div><div class="lbl">Proveedor</div></div>
    </div>

    <!-- Acciones -->
    <div class="d-flex flex-wrap gap-2 mb-3" style="gap:8px">
        <a href="<?= Url::to(['exportar-excel']) ?>" class="btn-export" id="btn-exp1">
            <i class="fas fa-file-excel mr-2"></i> Exportar Excel para SIESA
        </a>
        <button type="button" class="btn btn-primary font-weight-bold" id="btn-guardar" onclick="guardarEnBd()">
            <i class="fas fa-save mr-1"></i> Guardar Pedido en BD
        </button>
        <a href="<?= Url::to(['lista']) ?>" class="btn btn-outline-success">
            <i class="fas fa-list mr-1"></i> Ver Pedidos Guardados
        </a>
        <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-upload mr-1"></i> Cargar otro archivo
        </a>
        <a href="<?= Url::to(['limpiar']) ?>" class="btn btn-outline-danger"
           onclick="return confirm('¿Limpiar la sesión actual?')">
            <i class="fas fa-trash mr-1"></i> Limpiar
        </a>
    </div>

    <!-- Artículos -->
    <?php foreach ($products as $product): ?>
    <div class="prod-card">
        <div class="prod-head">
            <div>
                <div class="prod-title">
                    <span class="badge-code"><?= Html::encode($product['codigo']) ?></span>
                    &nbsp;<?= Html::encode($product['descripcion']) ?>
                </div>
                <div class="prod-meta">
                    Ref: <strong><?= Html::encode($product['referencia']) ?></strong>
                    &nbsp;·&nbsp;
                    Costo: <strong>$<?= number_format((float)$product['costo'], 0, ',', '.') ?></strong>
                    &nbsp;·&nbsp;
                    P.Vta: <strong>$<?= number_format((float)$product['precio_venta'], 0, ',', '.') ?></strong>
                    &nbsp;·&nbsp;
                    Rango: <strong><?= Html::encode($product['rango']) ?></strong>
                </div>
                <?php
                $estilos = array_filter([$product['estilo1'],$product['estilo2'],
                                         $product['estilo3'],$product['estilo4'],$product['estilo5']]);
                ?>
                <?php if ($estilos): ?>
                <div class="styles-row"><?= implode(' · ', array_map('htmlspecialchars', $estilos)) ?></div>
                <?php endif; ?>
            </div>
            <div class="text-right">
                <?php if (!empty($product['oc_siesa'])): ?>
                <div class="oc-chip mb-1">
                    <i class="fas fa-file-alt mr-1"></i> OC SIESA: <strong><?= Html::encode($product['oc_siesa']) ?></strong>
                </div>
                <?php endif; ?>
                <?php if (!empty($product['fecha_despacho'])): ?>
                <div class="prod-meta"><i class="fas fa-calendar-alt mr-1"></i> <?= Html::encode($product['fecha_despacho']) ?></div>
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
                    <?php foreach ($product['colores'] as $colorData): ?>
                    <?php foreach ($colorData['cantidades'] as $talla => $cantidad): ?>
                    <?php
                    $ean   = $product['ean_codes'][$colorData['nombre']][$talla] ?? '';
                    $valid = MonachoParser::validarEan13($ean);
                    ?>
                    <tr>
                        <td><span class="color-pill"><?= Html::encode($colorData['nombre']) ?></span></td>
                        <td style="text-align:center;font-weight:600"><?= Html::encode($talla) ?></td>
                        <td style="text-align:center"><?= number_format($cantidad) ?></td>
                        <td style="text-align:center">
                            <input type="text"
                                   class="ean-input <?= $valid ? 'ok' : 'err' ?>"
                                   value="<?= Html::encode($ean) ?>"
                                   maxlength="13"
                                   data-codigo="<?= $product['codigo'] ?>"
                                   data-color="<?= Html::encode($colorData['nombre']) ?>"
                                   data-talla="<?= Html::encode($talla) ?>"
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
                        <td style="text-align:center">
                            <?= number_format(array_sum(array_map(fn($c) => array_sum($c['cantidades']), $product['colores']))) ?>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Botones al final -->
    <div class="d-flex flex-wrap mb-5" style="gap:8px;margin-top:8px">
        <a href="<?= Url::to(['exportar-excel']) ?>" class="btn-export" id="btn-exp2">
            <i class="fas fa-file-excel mr-2"></i> Exportar Excel para SIESA
        </a>
        <button type="button" class="btn btn-primary font-weight-bold" onclick="guardarEnBd()">
            <i class="fas fa-save mr-1"></i> Guardar Pedido en BD
        </button>
        <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-upload mr-1"></i> Cargar otro archivo
        </a>
    </div>
</div>

<?php $this->registerJs('
    var updateUrl = "' . Url::to(['actualizar-ean']) . '";
    var csrf      = "' . Yii::$app->request->csrfToken . '";

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
        var ean   = inp.value.trim();
        var ok    = validarEan(ean);
        var icon  = inp.closest("tr").querySelector(".fa-check-circle, .fa-times-circle");

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
                "_csrf" : csrf,
                "codigo": inp.dataset.codigo,
                "color" : inp.dataset.color,
                "talla" : inp.dataset.talla,
                "ean"   : ean,
            })
        }).then(r => r.json()).then(data => {
            if (!data.success) {
                inp.classList.replace("ok", "err");
                console.warn(data.message);
            }
        }).catch(e => console.error(e));
    }

    // Solo dígitos
    document.querySelectorAll(".ean-input").forEach(function(i) {
        i.addEventListener("keypress", function(e) {
            if (!/[0-9]/.test(e.key)) e.preventDefault();
        });
    });

    // Loading en export
    ["btn-exp1","btn-exp2"].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener("click", function() {
            this.style.opacity = "0.7";
            this.innerHTML = "<i class=\"fas fa-spinner fa-spin mr-2\"></i> Generando...";
        });
    });

    // Guardar en BD
    var saveSessionUrl = "' . Url::to(['guardar-sesion']) . '";
    var listaUrl       = "' . Url::to(['lista']) . '";

    function guardarEnBd() {
        if (!confirm("¿Guardar este pedido en la base de datos?")) return;
        var btn = document.getElementById("btn-guardar");
        if (btn) { btn.disabled = true; btn.innerHTML = "<i class=\"fas fa-spinner fa-spin mr-1\"></i> Guardando..."; }

        fetch(saveSessionUrl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ "_csrf": csrf })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.href = listaUrl.replace("lista", "ver?id=" + data.id);
            } else {
                alert("Error: " + data.message);
                if (btn) { btn.disabled = false; btn.innerHTML = "<i class=\"fas fa-save mr-1\"></i> Guardar Pedido en BD"; }
            }
        })
        .catch(function(e) {
            alert("Error de red: " + e);
            if (btn) { btn.disabled = false; btn.innerHTML = "<i class=\"fas fa-save mr-1\"></i> Guardar Pedido en BD"; }
        });
    }
'); ?>
