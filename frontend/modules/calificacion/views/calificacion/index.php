<?php
/** @var yii\web\View $this */
/** @var array $ocs */
/** @var bool $soloSinCalificar */
/** @var array $filtros */
/** @var array $tiposDocMap */

use frontend\models\Calificacionproveedor;
use yii\helpers\Html;

$this->title = 'Órdenes de Compra — Calificación Proveedores';
$this->params['breadcrumbs'][] = $this->title;

$hayFiltros = !empty($filtros['q_oc']) || !empty($filtros['q_proveedor']) || !empty($filtros['q_tipo_doc']);
?>

<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-star text-warning"></i> Calificación de Proveedores</h4>
        <div>
            <?= Html::a(
                '<i class="fas fa-trophy"></i> Ver Ranking',
                ['/calificacion/ranking/index'],
                ['class' => 'btn btn-info mr-2']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-list"></i> Historial Calificaciones',
                ['/calificacion/calificacion/historial'],
                ['class' => 'btn btn-secondary mr-2']
            ) ?>
        </div>
    </div>

    <!-- Filtro pendientes / todas + búsqueda -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form method="get" action="<?= Yii::$app->request->scriptUrl ?>" class="mb-2">
                <input type="hidden" name="r" value="calificacion/calificacion/index">
                <input type="hidden" name="pendientes" value="<?= $soloSinCalificar ? 1 : 0 ?>">
                <div class="row align-items-end">
                    <div class="col-md-3 col-sm-6 mb-1">
                        <label class="small text-muted mb-0">Tipo de Documento</label>
                        <select name="q_tipo_doc" class="form-control form-control-sm">
                            <option value="">-- Todos --</option>
                            <?php foreach ($tiposDocMap as $id => $nombre): ?>
                            <option value="<?= $id ?>" <?= (string)$filtros['q_tipo_doc'] === (string)$id ? 'selected' : '' ?>>
                                <?= Html::encode($nombre) ?>
                            </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-1">
                        <label class="small text-muted mb-0">N° OC <small class="text-muted">(solo número)</small></label>
                        <input type="number" name="q_oc" class="form-control form-control-sm"
                               placeholder="Ej: 6513"
                               value="<?= Html::encode($filtros['q_oc']) ?>">
                    </div>
                    <div class="col-md-4 col-sm-6 mb-1">
                        <label class="small text-muted mb-0">Proveedor</label>
                        <input type="text" name="q_proveedor" class="form-control form-control-sm"
                               placeholder="Nombre del proveedor..."
                               value="<?= Html::encode($filtros['q_proveedor']) ?>">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-primary mr-1">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <?php if ($hayFiltros): ?>
                        <?= Html::a(
                            '<i class="fas fa-times"></i> Limpiar',
                            ['index', 'pendientes' => $soloSinCalificar ? 1 : 0],
                            ['class' => 'btn btn-sm btn-outline-secondary']
                        ) ?>
                        <?php endif ?>
                    </div>
                </div>
            </form>

            <div class="d-flex align-items-center">
                <div class="btn-group btn-group-sm mr-3" role="group">
                    <?= Html::a(
                        '<i class="fas fa-clock"></i> Sin Calificar',
                        ['index', 'pendientes' => 1, 'q_oc' => $filtros['q_oc'], 'q_proveedor' => $filtros['q_proveedor'], 'q_tipo_doc' => $filtros['q_tipo_doc']],
                        ['class' => 'btn ' . ($soloSinCalificar ? 'btn-primary' : 'btn-outline-primary')]
                    ) ?>
                    <?= Html::a(
                        '<i class="fas fa-list"></i> Todas las OC',
                        ['index', 'pendientes' => 0, 'q_oc' => $filtros['q_oc'], 'q_proveedor' => $filtros['q_proveedor'], 'q_tipo_doc' => $filtros['q_tipo_doc']],
                        ['class' => 'btn ' . (!$soloSinCalificar ? 'btn-primary' : 'btn-outline-primary')]
                    ) ?>
                </div>
                <span class="text-muted small">
                    <?= count($ocs) ?> registro(s) — mostrando las últimas 10
                    <?= $soloSinCalificar ? 'OC sin calificación' : 'OC' ?>
                    <?= $hayFiltros ? '<span class="badge badge-warning ml-1">Filtro activo</span>' : '' ?>
                </span>
                <div class="ml-auto">
                    <?= Html::a(
                        '<i class="fas fa-file-excel"></i> Exportar Excel',
                        ['/calificacion/calificacion/export',
                            'q_oc'        => $filtros['q_oc'],
                            'q_proveedor' => $filtros['q_proveedor'],
                            'q_tipo_doc'  => $filtros['q_tipo_doc'],
                            'pendientes'  => $soloSinCalificar ? 1 : 0,
                        ],
                        ['class' => 'btn btn-sm btn-success']
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de OCs -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0" style="font-size:12px;">
                    <thead class="thead-dark">
                        <tr>
                            <th>N° OC</th>
                            <th>Proveedor</th>
                            <th>CO</th>
                            <th>Fecha OC</th>
                            <th class="text-center">Subcats calificadas</th>
                            <th class="text-center">Ponderado OC</th>
                            <th class="text-center">Última Calif.</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($ocs)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-search fa-2x mb-2"></i><br>
                                <?= $hayFiltros
                                    ? 'No se encontraron OC con los filtros aplicados.'
                                    : ($soloSinCalificar ? 'Todas las OC han sido calificadas.' : 'No hay órdenes de compra registradas.')
                                ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ocs as $oc): ?>
                        <?php
                            $califs    = (int)$oc['total_calificaciones'];
                            $ponderado = $oc['puntaje_ponderado'] ?? null;
                            $letra     = $ponderado ? Calificacionproveedor::puntajeALetra($ponderado) : null;
                            $clase     = $letra ? Calificacionproveedor::letraClase($letra) : 'secondary';
                            $pendiente = $califs === 0;
                        ?>
                        <tr class="<?= $pendiente ? '' : 'table-success' ?>">
                            <td><strong><?= Html::encode($oc['numero_oc']) ?></strong></td>
                            <td><?= Html::encode($oc['proveedor']) ?></td>
                            <td><?= Html::encode($oc['centro_operacion']) ?></td>
                            <td><?= Html::encode(substr($oc['fecha'] ?? '', 0, 10)) ?></td>
                            <td class="text-center">
                                <?php if ($califs > 0): ?>
                                    <span class="badge badge-info"><?= $califs ?> subcategoría(s)</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Sin calificar</span>
                                <?php endif ?>
                            </td>
                            <td class="text-center">
                                <?php if ($ponderado !== null): ?>
                                    <span class="badge badge-<?= $clase ?>" title="Promedio de <?= $califs ?> subcategoría(s)">
                                        <?= $letra ?> (<?= number_format((float)$ponderado, 2) ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif ?>
                            </td>
                            <td class="text-center">
                                <?= $oc['ultima_fecha'] ? Html::encode(substr($oc['ultima_fecha'], 0, 10)) : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td class="text-center text-nowrap">
                                <?= Html::a(
                                    '<i class="fas fa-star"></i> Calificar',
                                    ['/calificacion/calificacion/create', 'id_oc' => $oc['id']],
                                    ['class' => 'btn btn-xs btn-' . ($pendiente ? 'warning' : 'outline-warning') . ' mr-1']
                                ) ?>
                                <?php if ($califs > 0): ?>
                                <?= Html::a(
                                    '<i class="fas fa-chart-bar"></i>',
                                    ['/calificacion/calificacion/ponderado', 'id_oc' => $oc['id']],
                                    ['class' => 'btn btn-xs btn-outline-info', 'title' => 'Ver ponderado']
                                ) ?>
                                <?php endif ?>
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
