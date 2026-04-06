<?php
/** @var \frontend\models\MonachoPedido   $pedido */
/** @var \frontend\models\MonachoArticulo[] $articulos */

use common\models\MonachoParser;

$totalUds  = 0;
$totalSubs = 0;
foreach ($articulos as $art) {
    foreach ($art->detalles as $d) {
        $totalSubs++;
        $totalUds += $d->cantidad;
    }
}

$verde       = '#1a6b3a';
$verdeClaro  = '#edf5ee';
$borderColor = '#c3dbc8';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 11px; color: #222; }

/* ─── Cabecera del documento ─────────────────────────── */
.doc-header {
    background: <?= $verde ?>;
    color: #fff;
    padding: 10px 14px;
    margin-bottom: 12px;
    border-radius: 4px;
}
.doc-header h1 { font-size: 16px; margin: 0 0 2px 0; }
.doc-header .sub { font-size: 10px; opacity: .85; }

/* ─── Info del pedido ────────────────────────────────── */
.pedido-info {
    border: 1px solid <?= $borderColor ?>;
    border-radius: 4px;
    padding: 8px 12px;
    margin-bottom: 14px;
    background: <?= $verdeClaro ?>;
}
.pedido-info table { width: 100%; border-collapse: collapse; }
.pedido-info td { padding: 2px 8px; font-size: 11px; }
.pedido-info .lbl { font-weight: 700; color: <?= $verde ?>; width: 120px; }

/* ─── KPIs ───────────────────────────────────────────── */
.kpis { display: table; width: 100%; margin-bottom: 14px; }
.kpi  { display: table-cell; text-align: center; border: 1px solid <?= $borderColor ?>;
        padding: 6px 4px; background: #fff; }
.kpi .num { font-size: 18px; font-weight: 700; color: <?= $verde ?>; }
.kpi .lbl { font-size: 9px; color: #888; text-transform: uppercase; }

/* ─── Artículo card ──────────────────────────────────── */
.art-card  { border: 1px solid <?= $borderColor ?>; border-radius: 4px; margin-bottom: 12px; page-break-inside: avoid; }
.art-head  { background: <?= $verdeClaro ?>; padding: 6px 10px; border-bottom: 1px solid <?= $borderColor ?>; }
.art-title { font-size: 12px; font-weight: 700; color: #0d4a28; }
.art-meta  { font-size: 10px; color: #555; margin-top: 2px; }
.code-chip { background: <?= $verde ?>; color: #fff; border-radius: 10px;
             padding: 1px 8px; font-size: 11px; font-weight: 700; }
.estilos   { font-size: 10px; color: #777; margin-top: 2px; }

/* ─── Tabla EAN ──────────────────────────────────────── */
.ean-tbl   { width: 100%; border-collapse: collapse; font-size: 10px; }
.ean-tbl th { background: <?= $verde ?>; color: #fff; padding: 4px 8px; text-align: center; font-weight: 600; }
.ean-tbl th.left { text-align: left; }
.ean-tbl td { padding: 4px 8px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
.ean-tbl tr:last-child td { border: none; }
.ean-tbl tr.even td { background: #f7fbf8; }
.ean-tbl .total-row td { background: <?= $verdeClaro ?>; font-weight: 700; }
.color-pill { background: <?= $verde ?>; color: #fff; border-radius: 10px;
              padding: 1px 7px; font-size: 10px; }
.ean-code  { font-family: "Courier New", monospace; font-size: 10px; letter-spacing: 1px; }
.ok-icon   { color: #28a745; font-weight: 700; }
.err-icon  { color: #dc3545; font-weight: 700; }
</style>
</head>
<body>

<!-- Cabecera -->
<?php
$logoPath = Yii::getAlias('@frontend') . '/web/img/herpo_logo.png';
$tieneLogoHerpo = file_exists($logoPath);
?>
<div class="doc-header">
    <table width="100%" style="border-collapse:collapse;">
        <tr>
            <td style="vertical-align:middle; padding:0;">
                <h1 style="margin:0 0 3px 0;">PEDIDO MONACHO #<?= $pedido->id ?></h1>
                <div class="sub">Generado el <?= date('d/m/Y H:i') ?> · Sistema GRUMALog</div>
            </td>
            <td align="right" style="vertical-align:middle; padding:0; width:160px;">
                <?php if ($tieneLogoHerpo): ?>
                <div style="background:#ffffff; border-radius:6px; padding:5px 8px; display:inline-block;">
                    <img src="<?= $logoPath ?>" style="height:44px; width:auto;">
                </div>
                <?php else: ?>
                <div style="background:#fff; border-radius:6px; padding:6px 12px; display:inline-block; line-height:1.1;">
                    <span style="font-size:22px; font-weight:900; color:#00AACC;">Herpo</span><span style="font-size:22px; font-weight:900; color:#F5A623;">.</span><br>
                    <span style="font-size:9px; color:#555;">¡Nos viste bien!</span>
                </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<!-- Info del pedido -->
<div class="pedido-info">
    <table>
        <tr>
            <td class="lbl">Proveedor:</td>
            <td><?= htmlspecialchars($pedido->proveedor) ?></td>
            <?php if ($pedido->oc_siesa): ?>
            <td class="lbl">OC SIESA:</td>
            <td><?= htmlspecialchars($pedido->oc_siesa) ?></td>
            <?php else: ?><td colspan="2"></td><?php endif; ?>
        </tr>
        <tr>
            <?php if ($pedido->oc_icg): ?>
            <td class="lbl">OC ICG:</td>
            <td><?= htmlspecialchars($pedido->oc_icg) ?></td>
            <?php else: ?><td colspan="2"></td><?php endif; ?>
            <?php if ($pedido->fecha_despacho): ?>
            <td class="lbl">Fecha Despacho:</td>
            <td><?= date('d/m/Y', strtotime($pedido->fecha_despacho)) ?></td>
            <?php else: ?><td colspan="2"></td><?php endif; ?>
        </tr>
        <?php if ($pedido->notas): ?>
        <tr>
            <td class="lbl">Notas:</td>
            <td colspan="3"><?= htmlspecialchars($pedido->notas) ?></td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<!-- KPIs -->
<div class="kpis">
    <div class="kpi"><div class="num"><?= count($articulos) ?></div><div class="lbl">Artículos</div></div>
    <div class="kpi"><div class="num"><?= $totalSubs ?></div><div class="lbl">Subartículos</div></div>
    <div class="kpi"><div class="num"><?= number_format($totalUds) ?></div><div class="lbl">Unidades</div></div>
    <div class="kpi"><div class="num"><?= $pedido->estado === 'ENVIADO' ? '✓ ENVIADO' : 'BORRADOR' ?></div><div class="lbl">Estado</div></div>
</div>

<!-- Artículos -->
<?php $rowNum = 0; foreach ($articulos as $art): ?>
<?php
// Agrupar detalles por color
$coloresAgrupados = [];
foreach ($art->detalles as $d) {
    $coloresAgrupados[$d->color][] = $d;
}
$artTotal = array_sum(array_map(function($d) { return $d->cantidad; }, $art->detalles));
$estilos  = array_filter([$art->estilo1,$art->estilo2,$art->estilo3,$art->estilo4,$art->estilo5]);
?>
<div class="art-card">
    <div class="art-head">
        <div class="art-title">
            <span class="code-chip"><?= htmlspecialchars($art->codigo) ?></span>
            &nbsp;<?= htmlspecialchars($art->descripcion) ?>
        </div>
        <div class="art-meta">
            <?php if ($art->referencia): ?>Ref: <strong><?= htmlspecialchars($art->referencia) ?></strong> &nbsp;·&nbsp; <?php endif; ?>
            Costo: <strong>$<?= number_format((float)$art->costo, 0, ',', '.') ?></strong>
            &nbsp;·&nbsp;
            P.Vta: <strong>$<?= number_format((float)$art->precio_venta, 0, ',', '.') ?></strong>
            <?php if ($art->rango): ?>&nbsp;·&nbsp; Rango: <strong><?= htmlspecialchars($art->rango) ?></strong><?php endif; ?>
        </div>
        <?php if ($estilos): ?>
        <div class="estilos"><?= implode(' · ', array_map('htmlspecialchars', $estilos)) ?></div>
        <?php endif; ?>
    </div>

    <table class="ean-tbl">
        <thead>
            <tr>
                <th class="left">Color</th>
                <th>Talla</th>
                <th>Cantidad</th>
                <th>EAN13</th>
                <th>✓</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($coloresAgrupados as $color => $detalles): ?>
            <?php foreach ($detalles as $d): ?>
            <?php $rowNum++; $valid = $d->isValid(); ?>
            <tr class="<?= $rowNum % 2 === 0 ? 'even' : '' ?>">
                <td><span class="color-pill"><?= htmlspecialchars($color) ?></span></td>
                <td style="text-align:center;font-weight:700"><?= htmlspecialchars($d->talla) ?></td>
                <td style="text-align:center"><?= number_format($d->cantidad) ?></td>
                <td style="text-align:center">
                    <span class="ean-code"><?= htmlspecialchars($d->ean13 ?: '—') ?></span>
                </td>
                <td style="text-align:center">
                    <?php if ($valid): ?>
                        <span class="ok-icon">✓</span>
                    <?php else: ?>
                        <span class="err-icon">✗</span>
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
<?php endforeach; ?>

</body>
</html>
