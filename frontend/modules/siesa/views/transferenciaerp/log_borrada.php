<?php
use yii\helpers\Html;

$this->title = 'Logs SIESA — Transferencia #' . $idBorrada;
$this->params['breadcrumbs'][] = ['label' => 'Integración ERP', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Historial borrados', 'url' => ['historial']];
$this->params['breadcrumbs'][] = $this->title;

$errores = ($logHeader && $logHeader->erroresJson) ? json_decode($logHeader->erroresJson, true) : [];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-alt mr-1"></i>
            Transferencia eliminada #<?= $idBorrada ?>
            <?php if ($logHeader): ?>
                — <?= Html::encode($logHeader->descripcion) ?>
            <?php endif; ?>
        </h3>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['historial'], ['class' => 'btn btn-sm btn-secondary']) ?>
        </div>
    </div>
    <div class="card-body">

        <?php if ($logHeader): ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <table class="table table-sm table-bordered">
                    <tr><th>Acción</th><td><?= Html::encode($logHeader->accion) ?></td></tr>
                    <tr><th>Origen</th><td><?= Html::encode($logHeader->origen ?? '—') ?></td></tr>
                    <tr><th>Registros</th><td><?= $logHeader->numeroRegistros ?></td></tr>
                    <tr><th>Enviado SIESA</th><td><?= $logHeader->enviadoWS ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-bordered">
                    <tr><th>Borrado el</th><td><?= $logHeader->created_at ?></td></tr>
                    <tr><th>Borrado por</th><td><?= $logHeader->userBorrado ? Html::encode($logHeader->userBorrado->username) : $logHeader->created_by ?></td></tr>
                    <tr><th>ID nueva transfer.</th><td><?= $logHeader->idTransferenciaerpNueva ?? '—' ?></td></tr>
                    <tr><th>Notas</th><td><?= Html::encode($logHeader->notas ?? '—') ?></td></tr>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <h5>Logs de envío a SIESA</h5>
        <?php if (empty($logs)): ?>
            <div class="alert alert-warning">No hay logs de envío a SIESA para esta transferencia.</div>
        <?php else: ?>
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th style="width:70px">ID</th>
                        <th style="width:140px">Inicio</th>
                        <th style="width:140px">Fin</th>
                        <th style="width:80px">Registros</th>
                        <th style="width:80px">Resultado</th>
                        <th>Mensaje SIESA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= $log->id ?></td>
                        <td><?= $log->startDate ?></td>
                        <td><?= $log->endDate ?></td>
                        <td><?= $log->numeroRegistros ?></td>
                        <td>
                            <?= $log->mensaje == '0'
                                ? '<span class="badge badge-success">Éxito</span>'
                                : '<span class="badge badge-danger">Error (' . Html::encode($log->mensaje) . ')</span>' ?>
                        </td>
                        <td><small><?= Html::encode(mb_substr((string)$log->mensaje, 0, 300)) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
        <h5 class="mt-3">Errores SIESA capturados al borrar</h5>
        <table class="table table-bordered table-striped table-sm">
            <thead>
                <tr>
                    <th>C.O.</th>
                    <th>Tipo Doc.</th>
                    <th>Línea</th>
                    <th>Nivel</th>
                    <th>Valor</th>
                    <th>Detalle del error</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($errores as $e): ?>
                <tr>
                    <td><?= Html::encode($e['centroOperacionDocumento'] ?? '') ?></td>
                    <td><?= Html::encode($e['tipoDocumento'] ?? '') ?></td>
                    <td><?= Html::encode($e['numeroLinea'] ?? '') ?></td>
                    <td><?= Html::encode($e['nivel'] ?? '') ?></td>
                    <td><?= Html::encode($e['valor'] ?? '') ?></td>
                    <td><?= Html::encode($e['detalle'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php elseif ($logHeader && $logHeader->enviadoWS == 0): ?>
            <div class="alert alert-info mt-2">Esta transferencia nunca fue enviada a SIESA — no hay errores de respuesta.</div>
        <?php else: ?>
            <div class="alert alert-secondary mt-2">No se capturaron errores SIESA (transferencia borrada antes de implementar este log).</div>
        <?php endif; ?>

    </div>
</div>
