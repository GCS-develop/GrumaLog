<?php
use frontend\models\Auditoriaentrada;
use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Auditoriaentrada[] $auditorias */
/** @var array $statsMap */
/** @var array $espMap */
/** @var array $creatorMap */
/** @var array $operariosMap */
/** @var array $conteoProgramacionMap */
/** @var int $totalAbiertas */
/** @var int $totalFinalizadas */
/** @var string $filtroEstado */
/** @var string $filtroConsecutivo */
/** @var string $filtroDesde */
/** @var string $filtroHasta */

$this->title = 'Auditoría de Entradas';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
    .page-header-aud {
        background: linear-gradient(135deg,#1a252f 0%,#2c3e50 100%);
        color:#fff; border-radius:12px; padding:18px 22px;
        margin-bottom:18px; display:flex; align-items:center; justify-content:space-between;
    }
    .page-header-aud h4 { margin:0; font-size:20px; font-weight:700; }
    .stat-box {
        background:#fff; border-radius:10px; padding:14px 18px;
        box-shadow:0 1px 6px rgba(0,0,0,.08); text-align:center; margin-bottom:16px;
    }
    .stat-box .num { font-size:28px; font-weight:bold; }
    .stat-box .lbl { font-size:12px; color:#888; margin-top:2px; }
    .filter-bar {
        background:#fff; border-radius:10px; padding:12px 16px;
        box-shadow:0 1px 4px rgba(0,0,0,.06); margin-bottom:16px;
        display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;
    }
    .filter-bar label { font-size:12px; font-weight:600; color:#555; margin-bottom:3px; display:block; }
    .card-aud {
        background:#fff; border-radius:10px;
        box-shadow:0 2px 8px rgba(0,0,0,.07); margin-bottom:12px;
        border-left:4px solid #ddd;
    }
    .card-aud.abierta    { border-left-color:#27ae60; }
    .card-aud.finalizada { border-left-color:#95a5a6; }
    .card-aud .card-body { padding:14px 16px; }
    .oc-label { font-size:16px; font-weight:bold; color:#2c3e50; }
    .estado-badge { display:inline-block; border-radius:20px; font-size:11px; font-weight:700; padding:2px 10px; margin-left:8px; }
    .estado-abierta    { background:#d4edda; color:#155724; }
    .estado-finalizada { background:#e2e3e5; color:#383d41; }
    .meta-row { font-size:12px; color:#666; margin-top:4px; }
    .progress-slim { height:6px; border-radius:3px; background:#eee; margin-top:6px; overflow:hidden; }
    .progress-slim .bar { height:100%; border-radius:3px; }
    .bar-ok   { background:#27ae60; }
    .bar-over { background:#e67e22; }
    .bar-low  { background:#e74c3c; }
    .stats-mini { display:flex; gap:12px; margin-top:8px; flex-wrap:wrap; }
    .stats-mini span { font-size:12px; color:#555; }
    .stats-mini strong { color:#2c3e50; }
    .btn-ver { background:#2980b9; color:#fff; border:none; border-radius:6px; padding:6px 14px; font-size:13px; }
    .empty-msg { text-align:center; padding:40px 20px; color:#aaa; font-size:14px; }
');
?>
<div class="auditoriaentrada-index">
    <div class="page-header-aud">
        <div>
            <h4><i class="fa fa-clipboard-check"></i> Auditoría de Entradas</h4>
            <small>Control de recepción vs escaneo de Órdenes de Compra</small>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-xs-4">
            <div class="stat-box">
                <div class="num" style="color:#2980b9;"><?= $totalAbiertas + $totalFinalizadas ?></div>
                <div class="lbl">Total</div>
            </div>
        </div>
        <div class="col-md-4 col-xs-4">
            <div class="stat-box">
                <div class="num" style="color:#27ae60;"><?= $totalAbiertas ?></div>
                <div class="lbl">Abiertas</div>
            </div>
        </div>
        <div class="col-md-4 col-xs-4">
            <div class="stat-box">
                <div class="num" style="color:#7f8c8d;"><?= $totalFinalizadas ?></div>
                <div class="lbl">Finalizadas</div>
            </div>
        </div>
    </div>

    <form method="get" action="<?= Url::to(['/index.php']) ?>">
        <input type="hidden" name="r" value="auditoriaentrada/auditoriaentrada/index">
        <div class="filter-bar">
            <div>
                <label>Estado</label>
                <select name="estado" class="form-control" style="min-width:130px;">
                    <option value="" <?= $filtroEstado === '' ? 'selected' : '' ?>>Todos</option>
                    <option value="1" <?= $filtroEstado === '1' ? 'selected' : '' ?>>Abiertas</option>
                    <option value="2" <?= $filtroEstado === '2' ? 'selected' : '' ?>>Finalizadas</option>
                </select>
            </div>
            <div>
                <label>Consecutivo OC</label>
                <input type="number" name="consecutivo" class="form-control" style="width:130px;"
                       placeholder="Ej: 12345" value="<?= Html::encode($filtroConsecutivo) ?>">
            </div>
            <div>
                <label>Desde</label>
                <?= DatePicker::widget([
                    'name'          => 'desde',
                    'value'         => $filtroDesde,
                    'language'      => 'es',
                    'options'       => ['class' => 'form-control', 'style' => 'width:120px;', 'placeholder' => 'aaaa-mm-dd'],
                    'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayHighlight' => true],
                ]) ?>
            </div>
            <div>
                <label>Hasta</label>
                <?= DatePicker::widget([
                    'name'          => 'hasta',
                    'value'         => $filtroHasta,
                    'language'      => 'es',
                    'options'       => ['class' => 'form-control', 'style' => 'width:120px;', 'placeholder' => 'aaaa-mm-dd'],
                    'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayHighlight' => true],
                ]) ?>
            </div>
            <div style="padding-top:20px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filtrar</button>
                <?php if ($filtroEstado !== '' || $filtroConsecutivo !== '' || $filtroDesde !== '' || $filtroHasta !== ''): ?>
                    <?= Html::a('<i class="fa fa-times"></i> Limpiar', ['/auditoriaentrada/auditoriaentrada/index'], ['class' => 'btn btn-default btn-sm']) ?>
                <?php endif; ?>
                <?= Html::a('<i class="fa fa-file-excel-o"></i> Descargar Excel',
                    array_filter([
                        '/auditoriaentrada/auditoriaentrada/exportar',
                        'estado'      => $filtroEstado      !== '' ? $filtroEstado      : null,
                        'consecutivo' => $filtroConsecutivo !== '' ? $filtroConsecutivo : null,
                        'desde'       => $filtroDesde       !== '' ? $filtroDesde       : null,
                        'hasta'       => $filtroHasta       !== '' ? $filtroHasta       : null,
                    ]),
                    ['class' => 'btn btn-success btn-sm']
                ) ?>
            </div>
        </div>
    </form>

    <?php if (empty($auditorias)): ?>
        <div class="empty-msg"><i class="fa fa-inbox" style="font-size:32px;display:block;margin-bottom:10px;"></i>No hay auditorías que coincidan.</div>
    <?php endif; ?>

    <?php
    $medals = ['🥇','🥈','🥉'];
    foreach ($auditorias as $i => $aud):
        $stats        = $statsMap[$aud->id] ?? [];
        $esperado     = $espMap[$aud->idTransferenciaerp] ?? 0;
        $escaneado    = isset($stats['totalUnidades']) ? (int)$stats['totalUnidades'] : 0;
        $pct          = $esperado > 0 ? min(100, round($escaneado / $esperado * 100)) : 0;
        $barCls       = $pct >= 100 ? 'bar-over' : ($pct >= 80 ? 'bar-ok' : 'bar-low');
        $isAbierta    = $aud->idestado == Auditoriaentrada::ESTADO_ABIERTA;
        $auditor              = $creatorMap[$aud->id] ?? '—';
        $operarios            = $operariosMap[$aud->id] ?? [];
        $contadoresProg       = $conteoProgramacionMap[$aud->id] ?? [];
    ?>
    <div class="card-aud <?= $isAbierta ? 'abierta' : 'finalizada' ?>">
        <div class="card-body">
            <div class="row">
                <div class="col-xs-8 col-md-9">
                    <div>
                        <?php if ($i < 3 && !empty($stats)): ?><span style="font-size:16px;"><?= $medals[$i] ?></span><?php endif; ?>
                        <span class="oc-label">OC <?= Html::encode($aud->tipoDocumentoOrdenCompra . '-' . $aud->consecutivoOrdenCompra) ?></span>
                        <span class="estado-badge <?= $isAbierta ? 'estado-abierta' : 'estado-finalizada' ?>"><?= $aud->estadoLabel ?></span>
                    </div>
                    <div class="meta-row">
                        Transf. <?= $aud->idTransferenciaerp ?> &nbsp;|&nbsp; <?= date('d/m/Y H:i', strtotime($aud->created_at)) ?>
                    </div>
                    <div class="meta-row">
                        <i class="glyphicon glyphicon-eye-open"></i> <strong>Auditor:</strong> <?= Html::encode($auditor) ?>
                        <?php if (!empty($operarios)): ?>
                            &nbsp;|&nbsp; <i class="glyphicon glyphicon-user"></i> <strong>Conteo aud.:</strong> <?= Html::encode(implode(', ', $operarios)) ?>
                        <?php endif; ?>
                        <?php if (!empty($contadoresProg)): ?>
                            &nbsp;|&nbsp; <i class="glyphicon glyphicon-barcode"></i> <strong>Conteo entrada:</strong> <?= Html::encode(implode(', ', $contadoresProg)) ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($stats)): ?>
                    <div class="stats-mini">
                        <span><i class="glyphicon glyphicon-barcode"></i> <strong><?= number_format($stats['totalEscaneos']) ?></strong> escaneos</span>
                        <span><i class="glyphicon glyphicon-th"></i> <strong><?= number_format($escaneado) ?></strong> uds</span>
                        <?php if ($esperado > 0): ?>
                            <span><strong><?= $pct ?>%</strong> de <?= number_format($esperado) ?> unidades entradas</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($esperado > 0): ?>
                    <div class="progress-slim"><div class="bar <?= $barCls ?>" style="width:<?= $pct ?>%;"></div></div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="meta-row" style="color:#bbb;font-style:italic;">Sin escaneos aún</div>
                    <?php endif; ?>
                </div>
                <div class="col-xs-4 col-md-3" style="text-align:right;padding-top:8px;">
                    <?= Html::a('<i class="glyphicon glyphicon-eye-open"></i> Ver detalle',
                        ['/auditoriaentrada/auditoriaentrada/view', 'id' => $aud->id],
                        ['class' => 'btn-ver']) ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
