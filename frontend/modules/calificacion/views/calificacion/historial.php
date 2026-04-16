<?php
/** @var yii\web\View $this */
/** @var array $calificaciones */
/** @var array $users */
/** @var array $filtros */

use frontend\models\Calificacionproveedor;
use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Historial de Calificaciones';
$this->params['breadcrumbs'][] = ['label' => 'Calificación Proveedores', 'url' => ['/calificacion/calificacion/index']];
$this->params['breadcrumbs'][] = $this->title;

$hayFiltros = !empty($filtros['q_oc']) || !empty($filtros['q_proveedor'])
           || !empty($filtros['q_desde']) || !empty($filtros['q_hasta']);

?>

<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-history text-secondary"></i> <?= Html::encode($this->title) ?></h4>
        <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['/calificacion/calificacion/index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form method="get" action="<?= Yii::$app->request->scriptUrl ?>">
                <input type="hidden" name="r" value="calificacion/calificacion/historial">
                <div class="row align-items-end">
                    <div class="col-md-2 col-sm-6 mb-1">
                        <label class="small text-muted mb-0">N° OC</label>
                        <input type="text" name="q_oc" class="form-control form-control-sm"
                               placeholder="Ej: OC-00006513"
                               value="<?= Html::encode($filtros['q_oc']) ?>">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-1">
                        <label class="small text-muted mb-0">Proveedor</label>
                        <input type="text" name="q_proveedor" class="form-control form-control-sm"
                               placeholder="Nombre del proveedor..."
                               value="<?= Html::encode($filtros['q_proveedor']) ?>">
                    </div>
                    <div class="col-md-2 col-sm-6 mb-1">
                        <label class="small text-muted mb-0">Fecha Cita Desde <small class="text-info">(fecha recepción)</small></label>
                        <?= DatePicker::widget([
                            'name'          => 'q_desde',
                            'value'         => $filtros['q_desde'],
                            'language'      => 'es',
                            'options'       => ['placeholder' => 'Fecha Cita Desde...', 'class' => 'form-control form-control-sm'],
                            'pluginOptions' => [
                                'autoclose'      => true,
                                'format'         => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-1">
                        <label class="small text-muted mb-0">Fecha Cita Hasta <small class="text-info">(fecha recepción)</small></label>
                        <?= DatePicker::widget([
                            'name'          => 'q_hasta',
                            'value'         => $filtros['q_hasta'],
                            'language'      => 'es',
                            'options'       => ['placeholder' => 'Fecha Cita Hasta...', 'class' => 'form-control form-control-sm'],
                            'pluginOptions' => [
                                'autoclose'      => true,
                                'format'         => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-3 col-sm-12 mb-1 d-flex align-items-end flex-wrap" style="gap:4px">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <?php if ($hayFiltros): ?>
                        <?= Html::a('<i class="fas fa-times"></i> Limpiar', ['/calificacion/calificacion/historial'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?php endif ?>
                        <?= Html::a(
                            '<i class="fas fa-file-excel"></i> Excel',
                            array_merge(['/calificacion/calificacion/export-historial'], $filtros),
                            ['class' => 'btn btn-sm btn-success ml-auto']
                        ) ?>
                    </div>
                </div>
            </form>
            <div class="mt-1">
                <span class="text-muted small">
                    <?= count($calificaciones) ?> registro(s)
                    <?= $hayFiltros ? '<span class="badge badge-warning ml-1">Filtro activo</span>' : '(máx. 200)' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0" style="font-size:12px;">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>N° OC</th>
                            <th>Proveedor</th>
                            <th>Subcategoría</th>
                            <th>Transportadora</th>
                            <th class="text-center">Oportunidad<br><small>10%</small></th>
                            <th class="text-center">Cantidad<br><small>30%</small></th>
                            <th class="text-center">Cal. Criterios<br><small>30%</small></th>
                            <th class="text-center">Cal. Producto<br><small>30%</small></th>
                            <th class="text-center">Puntaje Total</th>
                            <th class="text-center">Fecha Cita<br><small class="font-weight-normal">(recepción OC)</small></th>
                            <th class="text-center">Calificado Por</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($calificaciones)): ?>
                        <tr>
                            <td colspan="13" class="text-center text-muted py-4">
                                <i class="fas fa-search fa-2x mb-2"></i><br>
                                <?= $hayFiltros ? 'No se encontraron calificaciones con los filtros aplicados.' : 'No hay calificaciones registradas.' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($calificaciones as $i => $cal): ?>
                        <?php
                            $lt = Calificacionproveedor::puntajeALetra($cal['puntaje_total']);
                            $lo = Calificacionproveedor::puntajeALetra($cal['oportunidad_formula']);
                            $lc = Calificacionproveedor::puntajeALetra($cal['cantidad_formula']);
                            $lq = Calificacionproveedor::puntajeALetra($cal['calidad_ponderada']);
                            $lp = $cal['calidad_producto'] ? Calificacionproveedor::puntajeALetra($cal['calidad_producto']) : null;
                            $username = $users[$cal['created_by']] ?? 'N/A';
                        ?>
                        <tr>
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td><strong><?= Html::encode($cal['numero_oc']) ?></strong></td>
                            <td><?= Html::encode($cal['proveedor']) ?></td>
                            <td>
                                <small class="text-muted"><?= Html::encode($cal['categoria']) ?></small><br>
                                <strong><?= Html::encode($cal['subcategoria']) ?></strong>
                            </td>
                            <td><?= Html::encode($cal['transportadora']) ?></td>
                            <td class="text-center">
                                <span class="badge badge-<?= Calificacionproveedor::letraClase($lo) ?>">
                                    <?= $lo ?> (<?= $cal['oportunidad_formula'] ?>)
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?= Calificacionproveedor::letraClase($lc) ?>">
                                    <?= $lc ?> (<?= $cal['cantidad_formula'] ?>)
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?= Calificacionproveedor::letraClase($lq) ?>">
                                    <?= $lq ?> (<?= number_format((float)$cal['calidad_ponderada'], 2) ?>)
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($lp): ?>
                                <span class="badge badge-<?= Calificacionproveedor::letraClase($lp) ?>">
                                    <?= $lp ?> (<?= $cal['calidad_producto'] ?>)
                                </span>
                                <?php else: ?><span class="text-muted">—</span><?php endif ?>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?= Calificacionproveedor::letraClase($lt) ?> badge-pill px-2">
                                    <?= $lt ?> (<?= number_format((float)$cal['puntaje_total'], 2) ?>)
                                </span>
                            </td>
                            <td class="text-center text-muted">
                                <?= $cal['fecha_entrega_cita']
                                    ? Html::encode(substr($cal['fecha_entrega_cita'], 0, 10))
                                    : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td class="text-center"><?= Html::encode($username) ?></td>
                            <td class="text-center" style="white-space:nowrap">
                                <?php if ($cal['id_ordendecompra']): ?>
                                <?= Html::a('<i class="fas fa-chart-bar"></i>', ['/calificacion/calificacion/ponderado', 'id_oc' => $cal['id_ordendecompra']], ['class' => 'btn btn-xs btn-outline-info mr-1', 'title' => 'Ver ponderado OC']) ?>
                                <?php endif ?>
                                <?= Html::a('<i class="fas fa-eye"></i>', ['/calificacion/calificacion/view', 'id' => $cal['id']], ['class' => 'btn btn-xs btn-outline-secondary', 'title' => 'Ver detalle']) ?>
                                <?= Html::a('<i class="fas fa-edit"></i>', ['/calificacion/calificacion/update', 'id' => $cal['id']], ['class' => 'btn btn-xs btn-outline-primary', 'title' => 'Editar']) ?>
                                <?= Html::a('<i class="fas fa-trash"></i>', ['/calificacion/calificacion/delete', 'id' => $cal['id']], [
                                    'class' => 'btn btn-xs btn-outline-danger',
                                    'title' => 'Eliminar',
                                    'data-confirm' => '¿Eliminar esta calificación?',
                                    'data-method'  => 'post',
                                ]) ?>
                            </td>
                        </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
