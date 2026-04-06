<?php
/** @var yii\web\View $this */
/** @var array $ranking */

use frontend\models\Calificacionproveedor;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Ranking de Proveedores';
$this->params['breadcrumbs'][] = ['label' => 'Calificación Proveedores', 'url' => ['/calificacion/calificacion/index']];
$this->params['breadcrumbs'][] = $this->title;

$detalleUrl = Url::to(['/calificacion/ranking/detalle']);
?>

<?php $this->registerJs("
$(document).on('click', '.btn-ver-ocs', function() {
    var idProv = $(this).data('id');
    $.get('" . $detalleUrl . "', {id_proveedor: idProv}, function(html) {
        $('#modal-detalle-body').html(html);
        $('#modal-detalle').modal('show');
    });
});
"); ?>

<!-- Modal detalle OCs del proveedor -->
<div class="modal fade" id="modal-detalle" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-list"></i> OC Calificadas del Proveedor</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="modal-detalle-body">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-trophy text-warning"></i> Ranking de Proveedores</h4>
        <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['/calificacion/calificacion/index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php if (empty($ranking)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Aún no hay calificaciones registradas.
        </div>
    <?php else: ?>

    <!-- KPIs globales -->
    <?php
    $totalCalif   = array_sum(array_column($ranking, 'total_calificaciones'));
    $promedioGlobal = array_sum(array_column($ranking, 'promedio_total')) / count($ranking);
    $topProveedor = $ranking[0]['proveedor'] ?? '—';
    ?>
    <div class="row mb-4 g-2">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-primary" style="font-size:2rem"><?= count($ranking) ?></div>
                <div class="text-muted small">Proveedores Calificados</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-info" style="font-size:2rem"><?= $totalCalif ?></div>
                <div class="text-muted small">Total Calificaciones</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <?php $lg = Calificacionproveedor::puntajeALetra($promedioGlobal); ?>
                <div class="fs-2 fw-bold text-<?= Calificacionproveedor::letraClase($lg) ?>" style="font-size:2rem">
                    <?= number_format($promedioGlobal, 2) ?>
                </div>
                <div class="text-muted small">Promedio Global</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold text-success text-truncate px-2" title="<?= Html::encode($topProveedor) ?>" style="font-size:0.95rem">
                    <i class="fas fa-medal text-warning"></i> <?= Html::encode(mb_strimwidth($topProveedor, 0, 25, '…')) ?>
                </div>
                <div class="text-muted small">Mejor Proveedor</div>
            </div>
        </div>
    </div>

    <!-- Tabla ranking -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0" style="font-size:12px;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width:40px">#</th>
                            <th>Proveedor</th>
                            <th class="text-center">Calificaciones</th>
                            <th class="text-center">Oportunidad<br><small>10%</small></th>
                            <th class="text-center">Cantidad<br><small>30%</small></th>
                            <th class="text-center">Cal. Criterios<br><small>30%</small></th>
                            <th class="text-center">Cal. Producto<br><small>30%</small></th>
                            <th class="text-center" style="width:130px">Puntaje Total</th>
                            <th class="text-center">Última Calif.</th>
                            <th class="text-center">OC Calificadas</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ranking as $i => $row): ?>
                    <?php
                        $promTotal = (float)$row['promedio_total'];
                        $letra  = Calificacionproveedor::puntajeALetra($promTotal);
                        $clase  = Calificacionproveedor::letraClase($letra);
                        $lO     = Calificacionproveedor::puntajeALetra($row['promedio_oportunidad']);
                        $lC     = Calificacionproveedor::puntajeALetra($row['promedio_cantidad']);
                        $lQ     = Calificacionproveedor::puntajeALetra($row['promedio_calidad']);
                        $lP     = Calificacionproveedor::puntajeALetra($row['promedio_calidad_producto']);

                        // Ícono medalla para top 3
                        $medalla = '';
                        if ($i === 0) $medalla = '<i class="fas fa-medal" style="color:gold"></i> ';
                        elseif ($i === 1) $medalla = '<i class="fas fa-medal" style="color:silver"></i> ';
                        elseif ($i === 2) $medalla = '<i class="fas fa-medal" style="color:#cd7f32"></i> ';
                    ?>
                    <tr>
                        <td class="text-center font-weight-bold"><?= $i + 1 ?></td>
                        <td><?= $medalla . Html::encode($row['proveedor']) ?></td>
                        <td class="text-center"><span class="badge badge-info"><?= $row['total_calificaciones'] ?></span></td>
                        <td class="text-center">
                            <span class="badge badge-<?= Calificacionproveedor::letraClase($lO) ?>">
                                <?= $lO ?> (<?= number_format((float)$row['promedio_oportunidad'], 2) ?>)
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-<?= Calificacionproveedor::letraClase($lC) ?>">
                                <?= $lC ?> (<?= number_format((float)$row['promedio_cantidad'], 2) ?>)
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-<?= Calificacionproveedor::letraClase($lQ) ?>">
                                <?= $lQ ?> (<?= number_format((float)$row['promedio_calidad'], 2) ?>)
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($row['promedio_calidad_producto'] !== null): ?>
                            <span class="badge badge-<?= Calificacionproveedor::letraClase($lP) ?>">
                                <?= $lP ?> (<?= number_format((float)$row['promedio_calidad_producto'], 2) ?>)
                            </span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif ?>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-<?= $clase ?> badge-pill px-3" style="font-size:0.9rem">
                                <?= $letra ?> (<?= number_format($promTotal, 2) ?>)
                            </span>
                            <!-- Mini barra de progreso -->
                            <div class="progress mt-1" style="height:4px">
                                <div class="progress-bar bg-<?= $clase ?>" style="width:<?= ($promTotal / 5 * 100) ?>%"></div>
                            </div>
                        </td>
                        <td class="text-center text-muted">
                            <?= Html::encode(substr($row['ultima_calificacion'] ?? '', 0, 10)) ?>
                        </td>
                        <td class="text-center">
                            <?= Html::button(
                                '<i class="fas fa-eye"></i> Ver OC',
                                [
                                    'class'   => 'btn btn-xs btn-outline-dark btn-ver-ocs',
                                    'data-id' => $row['id_proveedor'],
                                ]
                            ) ?>
                        </td>
                    </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php endif ?>
</div>
