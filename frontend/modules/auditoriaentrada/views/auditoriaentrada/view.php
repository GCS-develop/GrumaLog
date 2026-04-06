<?php
use common\widgets\Alert;
use frontend\models\Auditoriaentrada;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Auditoriaentrada $model */
/** @var array $comparativo */
/** @var array $porUsuario */
/** @var array $porUsuarioSku */
/** @var array $contadoresProgramacion */
/** @var array $conteoEntradaSku */
/** @var array $conteoEntradaSkuUser */

$this->title = 'Auditoría OC ' . $model->tipoDocumentoOrdenCompra . '-' . $model->consecutivoOrdenCompra;
$this->params['breadcrumbs'][] = ['label' => 'Auditoría de Entradas', 'url' => ['/auditoriaentrada/auditoriaentrada/index']];
$this->params['breadcrumbs'][] = $this->title;

$totalEsperado   = array_sum(array_column($comparativo, 'esperado'));
$totalEscaneado  = array_sum(array_column($comparativo, 'escaneado'));
$totalDiferencia = $totalEscaneado - $totalEsperado;
$pctTotal        = $totalEsperado > 0 ? min(100, round($totalEscaneado / $totalEsperado * 100)) : 0;
$totalEntrada    = !empty($conteoEntradaSku) ? array_sum($conteoEntradaSku) : 0;

// Ranking: max unidades para barra de progreso
$maxUnd = !empty($porUsuario) ? max(array_column($porUsuario, 'totalUnidades')) : 1;

$this->registerCss('
    .view-header {
        background: linear-gradient(135deg,#1a252f 0%,#2c3e50 100%);
        color:#fff; border-radius:12px; padding:16px 20px; margin-bottom:16px;
    }
    .view-header .oc-num { font-size:22px; font-weight:bold; color:#f39c12; }
    .view-header .meta   { font-size:12px; opacity:.8; margin-top:3px; }
    .kpi-box { background:#fff; border-radius:10px; padding:14px; box-shadow:0 1px 6px rgba(0,0,0,.08); text-align:center; margin-bottom:14px; }
    .kpi-box .val { font-size:26px; font-weight:bold; }
    .kpi-box .lbl { font-size:11px; color:#888; }
    .progress-aud { height:10px; border-radius:5px; background:#eee; overflow:hidden; margin:8px 0; }
    .progress-aud .bar { height:100%; border-radius:5px; }
    .bar-green  { background:#27ae60; }
    .bar-orange { background:#e67e22; }
    .bar-red    { background:#e74c3c; }
    .ranking-row { display:flex; align-items:center; background:#fff; border-radius:8px;
                   padding:10px 14px; margin-bottom:8px; box-shadow:0 1px 4px rgba(0,0,0,.07); }
    .ranking-row .medal { font-size:22px; min-width:36px; }
    .ranking-row .info  { flex:1; }
    .ranking-row .name  { font-weight:bold; font-size:14px; color:#2c3e50; }
    .ranking-row .detail { font-size:11px; color:#777; margin-top:2px; }
    .ranking-row .unds  { font-size:20px; font-weight:bold; color:#2980b9; min-width:60px; text-align:right; }
    .ranking-bar { height:4px; border-radius:2px; background:#eee; margin-top:4px; overflow:hidden; }
    .ranking-bar .fill { height:100%; border-radius:2px; background:#2980b9; }
    .tbl-cmp { width:100%; border-collapse:collapse; font-size:12px; }
    .tbl-cmp th { background:#2c3e50; color:#fff; padding:7px 10px; text-align:center; }
    .tbl-cmp td { padding:6px 10px; border-bottom:1px solid #eee; text-align:center; vertical-align:middle; }
    .tbl-cmp tr:last-child td { border-bottom:none; }
    .row-ok   { background:#eafaf1; }
    .row-exc  { background:#fef9e7; }
    .row-err  { background:#fdf2f2; }
    .row-nd   { background:#f8f9fa; color:#aaa; }
    .badge-ok  { background:#27ae60; color:#fff; border-radius:10px; padding:1px 8px; font-size:11px; }
    .badge-exc { background:#e67e22; color:#fff; border-radius:10px; padding:1px 8px; font-size:11px; }
    .badge-err { background:#e74c3c; color:#fff; border-radius:10px; padding:1px 8px; font-size:11px; }
    .badge-nd  { background:#95a5a6; color:#fff; border-radius:10px; padding:1px 8px; font-size:11px; }
    .section-title { font-size:14px; font-weight:bold; color:#2c3e50; margin:14px 0 8px; }
    .btn-fin  { background:#e74c3c; color:#fff; border:none; border-radius:7px; padding:8px 18px; font-size:13px; font-weight:600; }
    .btn-reab { background:#95a5a6; color:#fff; border:none; border-radius:7px; padding:6px 14px; font-size:12px; }
');
?>
<div class="auditoriaentrada-view">

    <?= Alert::widget() ?>

    <!-- Cabecera -->
    <div class="view-header">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div class="oc-num">OC <?= Html::encode($model->tipoDocumentoOrdenCompra . '-' . $model->consecutivoOrdenCompra) ?></div>
                <div class="meta">
                    Transferencia <?= $model->idTransferenciaerp ?>
                    &nbsp;|&nbsp; Creado <?= date('d/m/Y H:i', strtotime($model->created_at)) ?>
                    &nbsp;|&nbsp;
                    <span style="font-weight:bold; color:<?= $model->idestado == Auditoriaentrada::ESTADO_ABIERTA ? '#2ecc71' : '#bdc3c7' ?>;">
                        <?= $model->estadoLabel ?>
                    </span>
                </div>
                <?php if (!empty($contadoresProgramacion)): ?>
                <div class="meta" style="margin-top:4px;">
                    <i class="fa fa-barcode"></i> <strong>Conteo entrada:</strong>
                    <?= Html::encode(implode(', ', $contadoresProgramacion)) ?>
                </div>
                <?php endif; ?>
            </div>
            <div style="text-align:right;display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
                <?php if ($model->idestado == Auditoriaentrada::ESTADO_ABIERTA): ?>
                    <?= Html::a('<i class="fa fa-lock"></i> Finalizar Auditoría',
                        ['/auditoriaentrada/auditoriaentrada/finalizar', 'id' => $model->id],
                        ['class' => 'btn-fin',
                         'data'  => ['confirm' => '¿Está seguro de finalizar esta auditoría? Los operarios ya no podrán registrar nuevos escaneos.', 'method' => 'post']]) ?>
                <?php else: ?>
                    <?= Html::a('<i class="fa fa-lock-open"></i> Anular / Reabrir',
                        ['/auditoriaentrada/auditoriaentrada/reabrir', 'id' => $model->id],
                        ['class' => 'btn-reab',
                         'data'  => ['confirm' => '¿Reabrir esta auditoría para permitir nuevos escaneos?', 'method' => 'post']]) ?>
                <?php endif; ?>
                <?php if (!empty($conteoEntradaSku)): ?>
                    <?= Html::a('<i class="fa fa-undo"></i> Anular Conteo de Entrada',
                        ['/auditoriaentrada/auditoriaentrada/anular-conteo-entrada', 'id' => $model->id],
                        ['class' => 'btn-reab',
                         'style' => 'background:#e67e22;border-color:#e67e22;font-size:11px;',
                         'data'  => [
                             'confirm' => '¿Anular el conteo de entrada de ' . implode(', ', $contadoresProgramacion) . '? Se borrarán todos sus registros y podrán recontar.',
                             'method'  => 'post'
                         ]]) ?>
                <?php endif; ?>
            </div>
        </div>
        <!-- Barra de progreso global -->
        <div style="margin-top:10px; font-size:12px; opacity:.9;">
            <?= number_format($totalEscaneado) ?> uds escaneadas de <?= number_format($totalEsperado) ?> UNDentradas
            &nbsp;(<strong><?= $pctTotal ?>%</strong>)
        </div>
        <div class="progress-aud">
            <div class="bar <?= $pctTotal >= 100 ? 'bar-orange' : ($pctTotal >= 80 ? 'bar-green' : 'bar-red') ?>"
                 style="width:<?= $pctTotal ?>%;"></div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row">
        <div class="col-md-3 col-xs-6">
            <div class="kpi-box">
                <div class="val" style="color:#3498db;"><?= number_format($totalEsperado) ?></div>
                <div class="lbl">UNDentradas</div>
            </div>
        </div>
        <?php if ($totalEntrada > 0): ?>
        <div class="col-md-3 col-xs-6">
            <div class="kpi-box">
                <div class="val" style="color:#e67e22;"><?= number_format($totalEntrada) ?></div>
                <div class="lbl">Conteo Entrada</div>
            </div>
        </div>
        <?php endif; ?>
        <div class="col-md-3 col-xs-6">
            <div class="kpi-box">
                <div class="val" style="color:#27ae60;"><?= number_format($totalEscaneado) ?></div>
                <div class="lbl">Auditoría (uds)</div>
            </div>
        </div>
        <div class="col-md-3 col-xs-6">
            <div class="kpi-box">
                <div class="val" style="color:<?= $totalDiferencia == 0 ? '#27ae60' : ($totalDiferencia < 0 ? '#e74c3c' : '#e67e22') ?>;">
                    <?= ($totalDiferencia > 0 ? '+' : '') . number_format($totalDiferencia) ?>
                </div>
                <div class="lbl">Diferencia</div>
            </div>
        </div>
        <div class="col-md-3 col-xs-6">
            <div class="kpi-box">
                <div class="val" style="color:#9b59b6;"><?= count($porUsuario) ?></div>
                <div class="lbl">Operarios aud.</div>
            </div>
        </div>
    </div>

    <!-- Ranking operarios -->
    <?php if (!empty($porUsuario)): ?>
    <div class="section-title"><i class="fa fa-trophy"></i> Ranking de operarios</div>
    <?php
    $rankMedals = ['🥇','🥈','🥉'];
    foreach ($porUsuario as $ri => $u):
        $pctU = $maxUnd > 0 ? round($u['totalUnidades'] / $maxUnd * 100) : 0;
    ?>
    <div class="ranking-row">
        <div class="medal"><?= $rankMedals[$ri] ?? '#' . ($ri+1) ?></div>
        <div class="info">
            <div class="name"><?= Html::encode($u['username']) ?></div>
            <div class="detail">
                <?= number_format($u['totalEscaneos']) ?> escaneos
                &nbsp;|&nbsp; <?= number_format($u['totalPaquetes']) ?> paquetes
                <?php if ($u['ultimoEscaneo']): ?>
                    &nbsp;|&nbsp; Último: <?= date('d/m H:i', strtotime($u['ultimoEscaneo'])) ?>
                <?php endif; ?>
            </div>
            <div class="ranking-bar"><div class="fill" style="width:<?= $pctU ?>%;"></div></div>
        </div>
        <div class="unds"><?= number_format($u['totalUnidades']) ?><br><span style="font-size:10px;color:#aaa;">uds</span></div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Filtro comparativo -->
    <div class="section-title"><i class="fa fa-balance-scale"></i> Comparativo por SKU</div>
    <div style="margin-bottom:10px;">
        <button onclick="filtrarCmp('all')"  class="btn btn-default btn-xs" id="f-all">Todos</button>
        <button onclick="filtrarCmp('err')"  class="btn btn-danger  btn-xs" id="f-err">Faltantes</button>
        <button onclick="filtrarCmp('exc')"  class="btn btn-warning btn-xs" id="f-exc">Excedentes</button>
        <button onclick="filtrarCmp('ok')"   class="btn btn-success btn-xs" id="f-ok">OK</button>
        <button onclick="filtrarCmp('nd')"   class="btn btn-default btn-xs" id="f-nd">Sin escanear</button>
    </div>

    <div class="table-responsive">
        <table class="tbl-cmp" id="tbl-cmp">
            <thead>
                <tr>
                    <th style="text-align:left;">Item</th>
                    <th>Color</th>
                    <th>Talla</th>
                    <th>UNDentradas</th>
                    <?php if ($totalEntrada > 0): ?><th style="background:#d35400;">Cto. Entrada</th><?php endif; ?>
                    <th>Paquetes</th>
                    <th>Auditoría</th>
                    <th>Dif.</th>
                    <th>Op. Aud.</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comparativo as $row):
                    if ($row['diferencia'] == 0 && $row['esperado'] > 0) { $cls = 'row-ok'; $badge = '<span class="badge-ok">OK</span>'; $dt = 'ok'; }
                    elseif ($row['esperado'] == 0)  { $cls = 'row-exc'; $badge = '<span class="badge-exc">Extra</span>'; $dt = 'exc'; }
                    elseif ($row['escaneado'] == 0) { $cls = 'row-nd';  $badge = '<span class="badge-nd">Sin escanear</span>'; $dt = 'nd'; }
                    elseif ($row['diferencia'] < 0) { $cls = 'row-err'; $badge = '<span class="badge-err">' . $row['diferencia'] . '</span>'; $dt = 'err'; }
                    else                            { $cls = 'row-exc'; $badge = '<span class="badge-exc">+' . $row['diferencia'] . '</span>'; $dt = 'exc'; }
                    $skuKey         = trim($row['item']) . '|' . trim($row['color']) . '|' . trim($row['talla']);
                    $skuUsers       = $porUsuarioSku[$skuKey] ?? [];
                    $ctoEntrada     = $conteoEntradaSku[$skuKey] ?? null;
                    $ctoEntradaUsers= $conteoEntradaSkuUser[$skuKey] ?? [];
                    $difEntrada     = ($ctoEntrada !== null) ? ($row['escaneado'] - $ctoEntrada) : null;
                ?>
                <tr class="<?= $cls ?>" data-tipo="<?= $dt ?>">
                    <td style="text-align:left;font-weight:bold;"><?= Html::encode($row['item']) ?></td>
                    <td><?= Html::encode($row['color']) ?></td>
                    <td><?= Html::encode($row['talla']) ?></td>
                    <td><?= number_format($row['esperado']) ?></td>
                    <?php if ($totalEntrada > 0): ?>
                    <td style="background:#fef5ec;font-size:11px;text-align:left;">
                        <?php if ($ctoEntrada !== null): ?>
                            <?php foreach ($ctoEntradaUsers as $eu): ?>
                                <div><strong><?= Html::encode($eu['username']) ?></strong>: <?= number_format($eu['unidades']) ?> uds</div>
                            <?php endforeach; ?>
                            <?php if ($difEntrada != 0): ?>
                                <div style="color:<?= $difEntrada < 0 ? '#e74c3c' : '#27ae60' ?>;font-weight:bold;margin-top:2px;">
                                    <?= ($difEntrada > 0 ? '+' : '') . number_format($difEntrada) ?> vs aud.
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#bbb;">—</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <td><?= number_format($row['paquetes']) ?></td>
                    <td><strong><?= number_format($row['escaneado']) ?></strong></td>
                    <td><?= ($row['diferencia'] > 0 ? '+' : '') . number_format($row['diferencia']) ?></td>
                    <td style="font-size:11px;text-align:left;">
                        <?php foreach ($skuUsers as $u): ?>
                            <div><strong><?= Html::encode($u['username']) ?></strong>: <?= number_format($u['unidades']) ?> uds</div>
                        <?php endforeach; ?>
                    </td>
                    <td><?= $badge ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f0f0f0;font-weight:bold;">
                    <td style="text-align:left;" colspan="3">Total</td>
                    <td><?= number_format($totalEsperado) ?></td>
                    <?php if ($totalEntrada > 0): ?>
                    <td style="background:#fef5ec;color:#d35400;"><?= number_format($totalEntrada) ?></td>
                    <?php endif; ?>
                    <td></td>
                    <td><?= number_format($totalEscaneado) ?></td>
                    <td style="color:<?= $totalDiferencia == 0 ? '#27ae60' : ($totalDiferencia < 0 ? '#e74c3c' : '#e67e22') ?>;">
                        <?= ($totalDiferencia > 0 ? '+' : '') . number_format($totalDiferencia) ?>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div style="font-size:11px;color:#888;margin-top:8px;line-height:1.8;">
        Nota: "UNDentradas" viene de cantidadBase en la OC. "Unidades" = paquetes escaneados × equivalencia por unidad de empaque.
    </div>
</div>

<script>
function filtrarCmp(tipo) {
    var rows = document.querySelectorAll('#tbl-cmp tbody tr');
    rows.forEach(function(r) {
        r.style.display = (tipo === 'all' || r.dataset.tipo === tipo) ? '' : 'none';
    });
}
</script>
