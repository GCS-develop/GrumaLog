<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\icons\Icon;
Icon::map($this, Icon::FAS);

$this->title = 'Resultado Envío ERP';
$this->params['breadcrumbs'][] = ['label' => 'Transferencia ERP', 'url' => ['/siesa/transferenciaerp/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
    .card-resumen { border-radius: 8px; }
    .tabla-grupos th, .tabla-grupos td { vertical-align: middle; font-size: 12px; }
    .tabla-errores th, .tabla-errores td { font-size: 11px; }
    .badge-ok { background-color: #28a745; color: #fff; padding: 4px 8px; border-radius: 4px; }
    .badge-err { background-color: #dc3545; color: #fff; padding: 4px 8px; border-radius: 4px; }
    #overlaySpinner { display:none; position:fixed; top:0; left:0; width:100%; height:100%;
        background:rgba(0,0,0,.55); z-index:9999; align-items:center; justify-content:center; flex-direction:column; }
    #overlaySpinner .spinner-msg { color:#fff; margin-top:18px; font-size:16px; }
    #overlaySpinner .spinner-sub { color:#ddd; font-size:12px; margin-top:4px; }
');
?>

<!-- Overlay spinner (se activa al reintentar) -->
<div id="overlaySpinner">
    <div class="spinner-border text-light" style="width:4rem;height:4rem;" role="status"></div>
    <p class="spinner-msg">Enviando a SIESA, por favor espere...</p>
    <p class="spinner-sub">No cierre ni actualice esta ventana.</p>
</div>

<div class="transferenciaerp-resultado">

    <h4 class="mb-3">
        <i class="fa fa-globe"></i> Resultado Envío ERP &nbsp;
        <small class="text-muted" style="font-size:14px"><?= Html::encode($model->descripcion) ?></small>
    </h4>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-sm-4">
            <div class="card card-resumen text-center py-3">
                <div class="card-body py-2">
                    <h2 class="mb-1"><?= $totalGrupos ?></h2>
                    <div class="text-muted" style="font-size:13px">Total grupos</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-resumen text-center py-3 bg-success text-white">
                <div class="card-body py-2">
                    <h2 class="mb-1"><?= $exitosos ?></h2>
                    <div style="font-size:13px">Enviados exitosamente</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-resumen text-center py-3 <?= $fallidos > 0 ? 'bg-danger text-white' : 'bg-secondary text-white' ?>">
                <div class="card-body py-2">
                    <h2 class="mb-1"><?= $fallidos ?></h2>
                    <div style="font-size:13px">Con error / pendientes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="mb-3">
        <?= Html::a(
            '<i class="fa fa-arrow-left"></i> Volver al listado',
            ['/siesa/transferenciaerp/index'],
            ['class' => 'btn btn-default']
        ) ?>

        <?php if ($fallidos > 0): ?>
            <?= Html::button(
                '<i class="fa fa-redo"></i> Reintentar fallidos (' . $fallidos . ')',
                [
                    'class' => 'btn btn-warning ml-2',
                    'id' => 'btnReintentar',
                    'data-url' => Url::to(['transferencia', 'id' => $model->id]),
                    'data-desc' => Html::encode($model->descripcion),
                ]
            ) ?>
        <?php else: ?>
            <span class="ml-2 text-success"><i class="fa fa-check-circle"></i> Todos los grupos fueron enviados correctamente</span>
        <?php endif; ?>
    </div>

    <!-- Tabla de grupos -->
    <div class="card mb-3">
        <div class="card-header py-2">
            <strong><i class="fa fa-list"></i> Detalle por grupo enviado</strong>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered tabla-grupos mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th>CO</th>
                        <th>Tipo Doc</th>
                        <th>Fecha</th>
                        <th>Bodega Sal.</th>
                        <th>Bodega Ent.</th>
                        <th class="text-center">Registros</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Duración (s)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-3">Sin registros de envío</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $i => $log): ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td><?= Html::encode($log->centroOperacionDocumento) ?></td>
                            <td><?= Html::encode($log->tipoDocumento) ?></td>
                            <td><?= Html::encode($log->fechaDocumento) ?></td>
                            <td><?= Html::encode($log->bodegaSalidaDocumento) ?></td>
                            <td><?= Html::encode($log->bodegaEntradaDocumento) ?></td>
                            <td class="text-center"><?= $log->numeroRegistros ?></td>
                            <td class="text-center">
                                <?php if ($log->mensaje == '0'): ?>
                                    <span class="badge-ok"><i class="fa fa-check"></i> Enviado</span>
                                <?php else: ?>
                                    <span class="badge-err"><i class="fa fa-times"></i> Error (<?= Html::encode($log->mensaje) ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php
                                try {
                                    $d1 = new DateTime($log->startDate);
                                    $d2 = new DateTime($log->endDate);
                                    $diff = $d1->diff($d2);
                                    echo $diff->s + ($diff->i * 60) + ($diff->h * 3600);
                                } catch (Exception $ex) {
                                    echo '-';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Errores SIESA -->
    <?php if (!empty($errors)): ?>
    <div class="card border-danger">
        <div class="card-header bg-danger text-white py-2">
            <strong><i class="fa fa-exclamation-triangle"></i> Errores SIESA (<?= count($errors) ?>)</strong>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered tabla-errores mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>CO</th>
                        <th>Tipo Doc</th>
                        <th>Fecha</th>
                        <th>Línea</th>
                        <th>Detalle del error</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($errors as $err): ?>
                    <tr class="table-danger">
                        <td><?= Html::encode($err->centroOperacionDocumento ?? '-') ?></td>
                        <td><?= Html::encode($err->tipoDocumento ?? '-') ?></td>
                        <td><?= Html::encode($err->fechaDocumento ?? '-') ?></td>
                        <td><?= Html::encode($err->numeroLinea ?? '-') ?></td>
                        <td><?= Html::encode($err->detalle) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Modal de confirmación reintentar -->
<div class="modal fade" id="modalReintentar" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-redo text-warning"></i> Reintentar envío</h5>
            </div>
            <div class="modal-body">
                <p>Se enviarán únicamente los grupos que <strong>no fueron procesados correctamente</strong>. Los ya exitosos no se reenviarán.</p>
                <p class="text-muted mb-0" style="font-size:12px"><?= Html::encode($model->descripcion) ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarReintentar">
                    <i class="fa fa-redo"></i> Confirmar reintentar
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$actionUrl = Url::to(['transferencia', 'id' => $model->id]);
$js = <<<JS
$('#btnReintentar').on('click', function () {
    $('#modalReintentar').modal('show');
});

$('#btnConfirmarReintentar').on('click', function () {
    $('#modalReintentar').modal('hide');
    $('#overlaySpinner').css('display', 'flex');
    window.location.href = '$actionUrl';
});
JS;
$this->registerJs($js);
?>
