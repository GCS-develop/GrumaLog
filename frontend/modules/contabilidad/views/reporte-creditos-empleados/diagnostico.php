<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Diagnóstico Crédito Empleado';
$this->params['breadcrumbs'][] = ['label' => 'Reporte Créditos Empleados', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

function fmtVal($v) {
    return number_format((float)$v, 2, ',', '.');
}
?>
<div class="diagnostico-credito">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Buscador -->
    <div class="well">
        <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['diagnostico']]); ?>
        <div class="row">
            <div class="col-sm-3">
                <?= Html::label('Cédula / NIT', 'nit') ?>
                <?= Html::input('text', 'nit', $nit, ['class' => 'form-control', 'placeholder' => 'Ingrese cédula o NIT', 'id' => 'nit']) ?>
            </div>
            <div class="col-sm-2" style="margin-top:25px;">
                <?= Html::submitButton('Consultar', ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php if ($nit && isset($resultado)): ?>

    <!-- 1. Tercero en t200 -->
    <h4>1. Tercero en t200_mm_terceros</h4>
    <?php if (empty($resultado['tercero'])): ?>
        <div class="alert alert-danger">NIT <strong><?= Html::encode($nit) ?></strong> NO existe en t200_mm_terceros.</div>
    <?php else: ?>
        <table class="table table-bordered table-condensed" style="font-size:13px; width:auto;">
            <tr style="background:#f0f0f0;"><th>rowid</th><th>NIT</th><th>Razón Social</th><th>id_cia</th></tr>
            <?php foreach ($resultado['tercero'] as $r): ?>
            <tr>
                <td><?= $r['f200_rowid'] ?></td>
                <td><?= Html::encode($r['f200_nit']) ?></td>
                <td><?= Html::encode($r['f200_razon_social']) ?></td>
                <td><?= $r['f200_id_cia'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- 2. Cliente en t201 -->
    <h4>2. Datos comerciales t201_mm_clientes</h4>
    <?php if (empty($resultado['cliente'])): ?>
        <div class="alert alert-warning">No tiene registro en t201_mm_clientes (sin datos comerciales).</div>
    <?php else: ?>
        <table class="table table-bordered table-condensed" style="font-size:13px; width:auto;">
            <tr style="background:#f0f0f0;"><th>cia</th><th>Sucursal</th><th>Cupo Crédito</th><th>Cond. Pago</th><th>Activo</th><th>Bloqueado</th></tr>
            <?php foreach ($resultado['cliente'] as $r): ?>
            <tr>
                <td><?= $r['f201_id_cia'] ?></td>
                <td><?= Html::encode($r['f201_id_sucursal']) ?></td>
                <td style="text-align:right;">$ <?= fmtVal($r['f201_cupo_credito']) ?></td>
                <td><?= Html::encode($r['f201_id_cond_pago']) ?></td>
                <td style="text-align:center; color:<?= $r['f201_ind_estado_activo'] ? '#5cb85c' : '#d9534f' ?>; font-weight:bold;">
                    <?= $r['f201_ind_estado_activo'] ? 'Sí' : 'No' ?>
                </td>
                <td style="text-align:center; color:<?= $r['f201_ind_estado_bloqueado'] ? '#d9534f' : '#5cb85c' ?>; font-weight:bold;">
                    <?= $r['f201_ind_estado_bloqueado'] ? 'Sí' : 'No' ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- 2b. Nombres de auxiliares -->
    <!-- 5. Auditoría -->
    <h4>5. Auditoría — ¿Quién creó / modificó este tercero?</h4>

    <h5 style="margin-top:10px;">t200_mm_terceros — último cambio</h5>
    <?php if (!empty($resultado['auditoria_t200'])): ?>
    <table class="table table-bordered table-condensed" style="font-size:13px; width:auto;">
        <tr style="background:#f0f0f0;">
            <th>rowid</th><th>NIT</th><th>Razón Social</th><th>cia</th><th>Último cambio (f200_ts)</th>
        </tr>
        <?php foreach ($resultado['auditoria_t200'] as $r): ?>
        <tr>
            <td><?= $r['f200_rowid'] ?></td>
            <td><?= Html::encode($r['f200_nit']) ?></td>
            <td><?= Html::encode($r['f200_razon_social']) ?></td>
            <td><?= $r['f200_id_cia'] ?></td>
            <td style="white-space:nowrap;"><?= $r['f200_ts'] ?? '—' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <h5 style="margin-top:10px;">t201_mm_clientes — tipo cliente + fechas</h5>
    <?php if (!empty($resultado['auditoria_t201'])): ?>
    <table class="table table-bordered table-condensed" style="font-size:13px; width:auto;">
        <tr style="background:#f0f0f0;">
            <th>cia</th><th>Sucursal</th><th>Tipo cliente</th>
            <th>Fecha ingreso</th><th>Fecha cupo</th><th>Último cambio (f201_ts)</th>
        </tr>
        <?php foreach ($resultado['auditoria_t201'] as $r): ?>
        <tr>
            <td><?= $r['f201_id_cia'] ?></td>
            <td><?= Html::encode($r['f201_id_sucursal']) ?></td>
            <?php $tipo = trim($r['f201_id_tipo_cli']); ?>
            <td style="font-weight:bold; color:<?= $tipo === '04' ? '#155724' : '#856404' ?>;">
                <?= Html::encode($tipo) ?>
                <?= $tipo === '04' ? ' ✔ EMPLEADOS' : ' ⚠ REVISAR' ?>
            </td>
            <td style="white-space:nowrap;"><?= $r['f201_fecha_ingreso'] ?? '—' ?></td>
            <td style="white-space:nowrap;"><?= $r['f201_fecha_cupo'] ?? '—' ?></td>
            <td style="white-space:nowrap;"><?= $r['f201_ts'] ?? '—' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <h5 style="margin-top:10px;">Log de cambios — t2005_mm_terceros_log_unif (últimos 20)</h5>
    <?php if (isset($resultado['log_cambios']['error'])): ?>
        <div class="alert alert-warning">La tabla de log no está disponible: <?= Html::encode($resultado['log_cambios']['error']) ?></div>
    <?php elseif (empty($resultado['log_cambios'])): ?>
        <div class="alert alert-info">Sin registros de log para este tercero.</div>
    <?php else: ?>
    <table class="table table-bordered table-condensed" style="font-size:12px;">
        <?php foreach ($resultado['log_cambios'] as $i => $r): ?>
            <?php if ($i === 0): ?>
            <tr style="background:#f0f0f0;"><?php foreach (array_keys($r) as $k): ?><th><?= Html::encode($k) ?></th><?php endforeach; ?></tr>
            <?php endif; ?>
            <tr><?php foreach ($r as $v): ?><td style="white-space:nowrap;"><?= Html::encode((string)$v) ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <h4>2b. Tablas de auxiliares en la BD y nombre de los auxiliares 20805 / 1323</h4>
    <?php if (!empty($resultado['nombres_auxiliares'])): ?>
        <p style="color:#666;">Tablas con "auxiliar" en el nombre:
            <?= implode(', ', array_column($resultado['nombres_auxiliares'], 'TABLE_NAME')) ?>
        </p>
    <?php else: ?>
        <p style="color:#aaa;">No se encontraron tablas con "auxiliar" en el nombre.</p>
    <?php endif; ?>
    <?php if (!empty($resultado['nombre_auxiliar_detalle'])): ?>
        <p>Tabla usada: <strong><?= $resultado['nombre_auxiliar_detalle']['tabla'] ?></strong></p>
        <table class="table table-bordered table-condensed" style="font-size:13px; width:auto;">
            <?php foreach ($resultado['nombre_auxiliar_detalle']['rows'] as $i => $r): ?>
                <?php if ($i === 0): ?>
                <tr style="background:#f0f0f0;"><?php foreach (array_keys($r) as $k): ?><th><?= Html::encode($k) ?></th><?php endforeach; ?></tr>
                <?php endif; ?>
                <tr><?php foreach ($r as $v): ?><td><?= Html::encode((string)$v) ?></td><?php endforeach; ?></tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No se pudo resolver el nombre de los auxiliares automáticamente. Ver tablas listadas arriba.</div>
    <?php endif; ?>

    <!-- 3. Auxiliares en t353 -->
    <h4>3. Auxiliares encontrados en t353_co_saldo_abierto <small style="color:#888;">(sin filtro de fecha)</small></h4>
    <?php if (empty($resultado['auxiliares'])): ?>
        <div class="alert alert-danger">Este NIT NO tiene ningún registro en t353_co_saldo_abierto.</div>
    <?php else: ?>
        <table class="table table-bordered table-condensed" style="font-size:13px; width:auto;">
            <tr style="background:#f0f0f0;"><th>rowid_auxiliar</th><th>Total registros</th><th>Primera fecha</th><th>Última fecha</th><th>Suma débito</th></tr>
            <?php foreach ($resultado['auxiliares'] as $r): ?>
            <tr style="<?= $r['f353_rowid_auxiliar'] == 20805 ? 'background:#d4edda; font-weight:bold;' : '' ?>">
                <td><?= $r['f353_rowid_auxiliar'] ?><?= $r['f353_rowid_auxiliar'] == 20805 ? ' ✔ (esperado)' : '' ?></td>
                <td style="text-align:center;"><?= $r['total'] ?></td>
                <td><?= $r['primera_fecha'] ?></td>
                <td><?= $r['ultima_fecha'] ?></td>
                <td style="text-align:right;">$ <?= fmtVal($r['suma_db']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
        $auxiliaresIds = array_column($resultado['auxiliares'], 'f353_rowid_auxiliar');
        if (!in_array('20805', $auxiliaresIds) && !in_array(20805, $auxiliaresIds)):
        ?>
        <div class="alert alert-danger">
            <strong>Causa encontrada:</strong> Este empleado NO tiene registros con auxiliar <strong>20805</strong>.
            El reporte filtra únicamente por ese auxiliar. Los auxiliares que tiene son: <strong><?= implode(', ', $auxiliaresIds) ?></strong>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- 4. Registros con auxiliar 20805 -->
    <h4>4. Registros con auxiliar 20805 (sin filtro de fecha)</h4>
    <?php if (empty($resultado['con_auxiliar_20805'])): ?>
        <div class="alert alert-warning">Sin registros con auxiliar 20805 para este NIT.</div>
    <?php else: ?>
        <table class="table table-bordered table-condensed" style="font-size:13px; width:auto;">
            <tr style="background:#f0f0f0;"><th>Tipo Doc.</th><th>N° Doc.</th><th>N° Cuota</th><th>Fecha</th><th>Débito</th><th>Crédito</th><th>Anticipo</th><th>cia</th></tr>
            <?php foreach ($resultado['con_auxiliar_20805'] as $r): ?>
            <tr>
                <td><?= Html::encode($r['f353_id_tipo_docto_cruce']) ?></td>
                <td style="font-weight:bold;"><?= $r['F353_CONSEC_DOCTO_CRUCE'] ?></td>
                <td style="text-align:center;"><?= $r['F353_NRO_CUOTA_CRUCE'] ?></td>
                <td><?= substr($r['f353_fecha'], 0, 10) ?></td>
                <td style="text-align:right;">$ <?= fmtVal($r['f353_total_db']) ?></td>
                <td style="text-align:right; color:<?= $r['f353_total_cr'] > 0 ? '#5cb85c' : '#aaa' ?>;">$ <?= fmtVal($r['f353_total_cr']) ?></td>
                <td style="text-align:center;"><?= $r['f353_ind_anticipo'] ? 'Sí' : 'No' ?></td>
                <td><?= $r['f353_id_cia'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php endif; ?>
</div>
