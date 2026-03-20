<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var frontend\models\GrumascanSnapshot $model */
/** @var frontend\models\Grumascanconteo[] $conteosAsignados */
/** @var frontend\models\Grumascanconteo[] $conteosDisponibles */

$this->title = "Snapshot #{$model->id}";
$this->params['breadcrumbs'][] = ['label' => 'Snapshots', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="grumascan-snapshot-view">

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $msg): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?>">
            <?= Html::encode(is_array($msg) ? implode(' ', $msg) : $msg) ?>
        </div>
    <?php endforeach; ?>

    <!-- ── CABECERA ─────────────────────────────────────────────────── -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>📦 Snapshot #<?= $model->id ?></strong>
            <div>
                <?= Html::a('Ver Consolidado con este Snapshot', [
                    '/grumascanmarcacion/reporte-conteos/consolidado',
                    'GrumascanReporteSearch[tienda]'     => $model->codigoBodega,
                    'GrumascanReporteSearch[idSnapshot]' => $model->id,
                ], ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('Eliminar Snapshot', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-sm ms-2',
                    'data'  => ['method' => 'post', 'confirm' => "¿Eliminar snapshot #{$model->id}? Solo si no tiene conteos asignados."],
                ]) ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Bodega:</strong><br>
                    [<?= Html::encode($model->codigoBodega) ?>]
                    <?= Html::encode($model->bodega ? $model->bodega->nombre : '-') ?>
                </div>
                <div class="col-md-4"><strong>Descripción:</strong><br>
                    <?= Html::encode($model->descripcion ?: '—') ?>
                </div>
                <div class="col-md-3"><strong>Fecha snapshot:</strong><br>
                    <?= substr($model->fecha_snapshot, 0, 16) ?>
                </div>
                <div class="col-md-2"><strong>Items Siesa:</strong><br>
                    <span class="badge bg-info fs-6"><?= number_format($model->total_items) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <!-- ── COLUMNA IZQUIERDA: Asignar conteos ──────────────────── -->
        <div class="col-md-5">

            <!-- Por rango de fechas -->
            <div class="card mb-3">
                <div class="card-header"><strong>📅 Asignar conteos por rango de fechas</strong></div>
                <div class="card-body">
                    <p class="small text-muted">
                        Asigna todos los conteos terminados (estado=1) de la bodega
                        <strong>[<?= Html::encode($model->codigoBodega) ?>]</strong>
                        en el rango indicado.
                    </p>
                    <form action="<?= Url::to(['asignar-conteos', 'id' => $model->id]) ?>" method="post">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                        <input type="hidden" name="modo" value="fecha">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label form-label-sm">Desde</label>
                                <input type="date" name="desde" class="form-control form-control-sm"
                                       value="<?= date('Y-m-01') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label form-label-sm">Hasta</label>
                                <input type="date" name="hasta" class="form-control form-control-sm"
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="form-check mt-2 mb-2">
                            <input class="form-check-input" type="checkbox" name="sobrescribir" value="1" id="chk-sobre">
                            <label class="form-check-label small" for="chk-sobre">
                                Sobrescribir conteos que ya tienen otro snapshot
                            </label>
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm w-100">
                            Asignar por rango de fechas
                        </button>
                    </form>
                </div>
            </div>

            <!-- Conteos disponibles (checkbox manual) -->
            <?php if (!empty($conteosDisponibles)): ?>
            <div class="card mb-3">
                <div class="card-header"><strong>✅ Asignar conteos individuales</strong></div>
                <div class="card-body p-2">
                    <p class="small text-muted mb-1">
                        Conteos de la bodega sin snapshot asignado (max 200):
                    </p>
                    <form action="<?= Url::to(['asignar-conteos', 'id' => $model->id]) ?>" method="post">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                        <input type="hidden" name="modo" value="ids">
                        <div style="max-height:300px;overflow-y:auto;" class="border rounded p-2 mb-2">
                            <?php foreach ($conteosDisponibles as $c): ?>
                                <?php
                                $seccion  = $c->marcacion->seccion ?? '-';
                                $ubicacion = $c->marcacion->ubicacion ?? '-';
                                $fecha    = substr($c->created_at ?? '', 0, 10);
                                ?>
                                <div class="form-check form-check-sm">
                                    <input class="form-check-input chk-conteo" type="checkbox"
                                           name="conteo_ids[]" value="<?= $c->id ?>"
                                           id="cc<?= $c->id ?>">
                                    <label class="form-check-label small" for="cc<?= $c->id ?>">
                                        #<?= $c->id ?> · <?= Html::encode($fecha) ?>
                                        · <?= Html::encode($seccion) ?>/<?= Html::encode($ubicacion) ?>
                                        · <?= $c->totalunidades ?> u.
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-all">Seleccionar todos</button>
                            <button type="submit" class="btn btn-warning btn-sm">Asignar seleccionados</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- ── COLUMNA DERECHA: Conteos ya asignados ───────────────── -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>📋 Conteos asignados a este snapshot (<?= count($conteosAsignados) ?>)</strong>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($conteosAsignados)): ?>
                        <div class="p-3 text-muted">Sin conteos asignados aún. Use los formularios a la izquierda.</div>
                    <?php else: ?>
                        <form action="<?= Url::to(['desasignar', 'id' => $model->id]) ?>" method="post">
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                            <div style="max-height:500px;overflow-y:auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th><input type="checkbox" id="chk-all-asig"></th>
                                            <th>#ID</th>
                                            <th>Fecha</th>
                                            <th>Sección</th>
                                            <th>Ubicación</th>
                                            <th class="text-end">Unidades</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($conteosAsignados as $c): ?>
                                            <tr>
                                                <td>
                                                    <input class="form-check-input chk-asig" type="checkbox"
                                                           name="conteo_ids[]" value="<?= $c->id ?>">
                                                </td>
                                                <td><?= $c->id ?></td>
                                                <td><?= substr($c->created_at ?? '', 0, 10) ?></td>
                                                <td><?= Html::encode($c->marcacion->seccion ?? '-') ?></td>
                                                <td><?= Html::encode($c->marcacion->ubicacion ?? '-') ?></td>
                                                <td class="text-end"><?= number_format($c->totalunidades) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-2 border-top">
                                <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('¿Desasignar los conteos seleccionados de este snapshot?')">
                                    Desasignar seleccionados
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$js = <<<JS
// Seleccionar todos los conteos disponibles
$('#btn-all').on('click', function () {
    var chks = $('.chk-conteo');
    var allChecked = chks.filter(':checked').length === chks.length;
    chks.prop('checked', !allChecked);
    $(this).text(allChecked ? 'Seleccionar todos' : 'Deseleccionar todos');
});

// Toggle todos los asignados
$('#chk-all-asig').on('change', function () {
    $('.chk-asig').prop('checked', this.checked);
});
JS;
$this->registerJs($js);
?>
