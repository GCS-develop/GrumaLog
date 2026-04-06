<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $cuotas array */
/* @var $nit string */
/* @var $tipo string */
/* @var $num int */
/* @var $backParams array */

// Datos del encabezado desde la primera cuota
$info = !empty($cuotas) ? $cuotas[0] : [];

$this->title = 'Factura ' . $tipo . '-' . $num;
$this->params['breadcrumbs'][] = ['label' => 'Reporte Créditos Empleados (Saldos Abiertos)', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Historial Facturas', 'url' => array_merge(['historial-facturas'], $backParams)];
$this->params['breadcrumbs'][] = $this->title;

// Calcular totales
$totalValor    = array_sum(array_column($cuotas, 'VALOR'));
$totalPendiente = 0;
$cuotasPagadas  = 0;
$cuotasPend     = 0;
foreach ($cuotas as $c) {
    if ($c['ESTADO'] === 'Pendiente') {
        $totalPendiente += (float)$c['VALOR'];
        $cuotasPend++;
    } else {
        $cuotasPagadas++;
    }
}
$fmt = Yii::$app->formatter;
?>
<div class="historial-factura-detalle">

    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Botón volver -->
    <?= Html::a(
        '&larr; Volver al Historial',
        array_merge(['historial-facturas'], $backParams),
        ['class' => 'btn btn-warning', 'style' => 'margin-bottom:15px;']
    ) ?>

    <!-- Encabezado de la factura -->
    <?php if (!empty($info)): ?>
    <div class="panel panel-default" style="border-radius:6px;">
        <div class="panel-heading" style="background:#f5f5f5; font-weight:bold; font-size:14px;">
            Información de la Factura
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-3">
                    <small style="color:#888; text-transform:uppercase;">Empleado</small><br>
                    <strong><?= Html::encode($info['RAZON_SOCIAL']) ?></strong><br>
                    <span style="color:#666;"><?= Html::encode($info['ID_TERCERO']) ?></span>
                </div>
                <div class="col-sm-2">
                    <small style="color:#888; text-transform:uppercase;">Documento</small><br>
                    <strong><?= Html::encode($tipo . '-' . $num) ?></strong>
                </div>
                <div class="col-sm-2">
                    <small style="color:#888; text-transform:uppercase;">Sucursal</small><br>
                    <strong><?= Html::encode($info['SUCURSAL_CLIENTE']) ?></strong>
                </div>
                <div class="col-sm-2">
                    <small style="color:#888; text-transform:uppercase;">Condición Pago</small><br>
                    <strong><?= Html::encode($info['CONDICION_PAGO']) ?></strong>
                </div>
                <div class="col-sm-3">
                    <small style="color:#888; text-transform:uppercase;">Cuotas</small><br>
                    <span style="color:#5cb85c; font-weight:bold;"><?= $cuotasPagadas ?> pagadas</span>
                    &nbsp;/&nbsp;
                    <span style="color:#d9534f; font-weight:bold;"><?= $cuotasPend ?> pendientes</span>
                    <span style="color:#aaa;"> de <?= count($cuotas) ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Resumen numérico -->
    <div class="row" style="margin-bottom:15px;">
        <div class="col-sm-4">
            <div class="panel panel-default" style="border-radius:6px;">
                <div class="panel-body text-center">
                    <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Valor Total Factura</div>
                    <div style="font-size:22px; font-weight:bold; color:#337ab7;">
                        $ <?= $fmt->asDecimal($totalValor, 0) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-default" style="border-radius:6px;">
                <div class="panel-body text-center">
                    <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Saldo Pendiente</div>
                    <div style="font-size:22px; font-weight:bold; color:<?= $totalPendiente > 0 ? '#d9534f' : '#5cb85c' ?>;">
                        $ <?= $fmt->asDecimal($totalPendiente, 0) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-default" style="border-radius:6px;">
                <div class="panel-body text-center">
                    <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Ya Pagado</div>
                    <div style="font-size:22px; font-weight:bold; color:#5cb85c;">
                        $ <?= $fmt->asDecimal($totalValor - $totalPendiente, 0) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de cuotas -->
    <?php if (empty($cuotas)): ?>
        <div class="alert alert-warning">No se encontraron cuotas para esta factura.</div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed" style="font-size:13px;">
            <thead>
                <tr style="background:#f0f0f0;">
                    <th style="text-align:center; width:60px;">N° Cuota</th>
                    <th style="text-align:center;">Fecha</th>
                    <th style="text-align:center;">Fecha Vcto.</th>
                    <th style="text-align:right;">Valor</th>
                    <th style="text-align:center; width:100px;">Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cuotas as $cuota):
                $esPendiente = ($cuota['ESTADO'] === 'Pendiente');
                $fechaVcto   = strtotime($cuota['FECHA_VCTO']);
                $vencida     = $esPendiente && $fechaVcto && $fechaVcto < time();
                $rowStyle    = $vencida ? 'background:#fff5f5;' : '';
            ?>
                <tr style="<?= $rowStyle ?>">
                    <td style="text-align:center; font-weight:bold;">
                        <?= (int)$cuota['NUMERO_CUOTA'] === 0 ? 1 : (int)$cuota['NUMERO_CUOTA'] ?>
                    </td>
                    <td style="text-align:center; white-space:nowrap;">
                        <?= $fmt->asDate($cuota['FECHA'], 'php:Y-m-d') ?>
                    </td>
                    <td style="text-align:center; white-space:nowrap;<?= $vencida ? ' color:#d9534f; font-weight:bold;' : '' ?>">
                        <?= $fmt->asDate($cuota['FECHA_VCTO'], 'php:Y-m-d') ?>
                        <?php if ($vencida): ?>
                            <br><span style="font-size:10px; background:#d9534f; color:#fff; padding:1px 4px; border-radius:3px;">VENCIDA</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        $ <?= $fmt->asDecimal($cuota['VALOR'], 2) ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($esPendiente): ?>
                            <span style="display:inline-block; padding:3px 10px; border-radius:10px; background:#d9534f; color:#fff; font-size:11px; font-weight:bold;">
                                Pendiente
                            </span>
                        <?php else: ?>
                            <span style="display:inline-block; padding:3px 10px; border-radius:10px; background:#5cb85c; color:#fff; font-size:11px; font-weight:bold;">
                                Pagado
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f0f0f0; font-weight:bold;">
                    <td colspan="3" style="text-align:right;">Total</td>
                    <td style="text-align:right; white-space:nowrap;">$ <?= $fmt->asDecimal($totalValor, 2) ?></td>
                    <td></td>
                </tr>
                <?php if ($totalPendiente > 0): ?>
                <tr style="background:#fff5f5; font-weight:bold; color:#d9534f;">
                    <td colspan="3" style="text-align:right;">Saldo Pendiente</td>
                    <td style="text-align:right; white-space:nowrap;">$ <?= $fmt->asDecimal($totalPendiente, 2) ?></td>
                    <td></td>
                </tr>
                <?php endif; ?>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

    <?= Html::a(
        '&larr; Volver al Historial',
        array_merge(['historial-facturas'], $backParams),
        ['class' => 'btn btn-warning', 'style' => 'margin-top:10px;']
    ) ?>

</div>
