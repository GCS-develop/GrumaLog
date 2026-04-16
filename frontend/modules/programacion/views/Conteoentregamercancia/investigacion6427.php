<?php
use yii\helpers\Html;

$this->title = 'Investigación OC 2CA-6427';

function tbl($rows, $resaltarCero = false) {
    if (empty($rows)) { echo '<p style="color:#888;font-style:italic">Sin registros.</p>'; return; }
    echo '<div style="overflow-x:auto"><table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;font-size:12px;width:100%;background:#fff">';
    echo '<tr style="background:#34495e;color:#fff">';
    foreach (array_keys($rows[0]) as $k) echo '<th style="padding:6px 10px;white-space:nowrap">' . Html::encode($k) . '</th>';
    echo '</tr>';
    foreach ($rows as $r) {
        $es0 = $resaltarCero && isset($r['unidadesConteo']) && (int)$r['unidadesConteo'] === 0;
        echo '<tr style="' . ($es0 ? 'background:#fff3cd' : '') . '">';
        foreach ($r as $k => $v) {
            $td = Html::encode((string)$v);
            if ($k === 'unidadesConteo' && (int)$v === 0) $td = '<span style="color:red;font-weight:bold">0 ⚠</span>';
            echo '<td style="padding:4px 8px;white-space:nowrap">' . $td . '</td>';
        }
        echo '</tr>';
    }
    echo '</table></div>';
}
?>
<style>
body{font-size:13px}
h2{background:#c0392b;color:#fff;padding:10px;border-radius:4px}
h3{background:#2c3e50;color:#fff;padding:7px 10px;border-radius:4px;margin-top:25px}
.alerta{background:#fdecea;border:2px solid #c0392b;padding:10px;border-radius:4px;font-weight:bold;color:#c0392b;margin:10px 0}
.ok{color:green;font-weight:bold}
.warn{color:#e67e22;font-weight:bold}
</style>

<div class="container-fluid" style="padding:20px">

<h2>🔍 Investigación OC 2CA-6427 — <?= date('Y-m-d H:i:s') ?></h2>

<h3>1. OC 2CA-6427 — Datos generales y estado de legalización</h3>
<?php tbl($oc) ?>

<h3>2. Programaciones — quién las tiene y en qué estado</h3>
<?php tbl($programaciones) ?>

<h3>3. Conteos actuales <small>(amarillo = en 0)</small></h3>
<?php tbl($conteos, true) ?>

<h3>4. 🚨 Actividad del 7-abr-2026 en conteoentregamercancia</h3>
<?php if (empty($actividad7abril)): ?>
    <p class="ok">✅ Sin actividad el 7 de abril en conteoentregamercancia.</p>
<?php else: ?>
    <div class="alerta">⚠ <?= count($actividad7abril) ?> registros tocados el 7 de abril — VER COLUMNA "actualizadoPor"</div>
    <?php tbl($actividad7abril, true) ?>
<?php endif ?>

<h3>5. conteobylecturacodigo — escaneos físicos <small>(facturas: <?= Html::encode($facturasStr) ?>)</small></h3>
<?php if (empty($escaneos)): ?>
    <p style="color:#888;font-style:italic">Sin escaneos — el conteo fue ingresado <strong>manualmente</strong>, no por scanner.</p>
<?php else: ?>
    <p class="ok">✅ <?= count($escaneos) ?> escaneos encontrados.</p>
    <?php tbl($escaneos) ?>
<?php endif ?>

<h3>6. Actividad del 7-abr-2026 en conteobylecturacodigo</h3>
<?php if (empty($escaneos7abril)): ?>
    <p class="ok">✅ Sin actividad el 7 de abril en conteobylecturacodigo.</p>
<?php else: ?>
    <div class="alerta">⚠ <?= count($escaneos7abril) ?> registros el 7 de abril:</div>
    <?php tbl($escaneos7abril) ?>
<?php endif ?>

<h3>7. Resumen de fechas — ¿cuándo se contó originalmente?</h3>
<?php tbl($resumenFechas) ?>

<h3>8. Facturas — estado SIESA</h3>
<?php tbl($facturas) ?>

</div>
