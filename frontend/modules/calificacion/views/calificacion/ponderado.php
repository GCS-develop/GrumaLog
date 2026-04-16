<?php
/** @var yii\web\View $this */
/** @var frontend\models\Calificacionproveedor[] $calificaciones */
/** @var array $ponderado  ['ponderado' => float, 'total' => int] */
/** @var array $ocDatos    ['numero_oc' => string, 'proveedor' => string] */
/** @var int   $id_oc */
/** @var array $subcatsPend  subcategorías sin calificar */

use frontend\models\Calificacionproveedor;
use yii\helpers\Html;

$numeroOc = $ocDatos['numero_oc'] ?? "OC #{$id_oc}";
$this->title = "Ponderado — {$numeroOc}";
$this->params['breadcrumbs'][] = ['label' => 'Calificación Proveedores', 'url' => ['/calificacion/calificacion/index']];
$this->params['breadcrumbs'][] = $this->title;

$pond   = $ponderado['ponderado'] ?? null;
$total  = (int)($ponderado['total'] ?? 0);
$letra  = $pond ? Calificacionproveedor::puntajeALetra($pond) : null;
$clase  = $letra ? Calificacionproveedor::letraClase($letra) : 'secondary';
?>

<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fas fa-chart-bar text-info"></i>
            Ponderado OC: <strong><?= Html::encode($numeroOc) ?></strong>
            <small class="text-muted ml-2"><?= Html::encode($ocDatos['proveedor'] ?? '') ?></small>
        </h4>
        <div>
            <?= Html::a(
                '<i class="fas fa-plus"></i> Agregar Subcategoría',
                ['/calificacion/calificacion/create', 'id_oc' => $id_oc],
                ['class' => 'btn btn-warning mr-2']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-arrow-left"></i> Volver',
                ['/calificacion/calificacion/index'],
                ['class' => 'btn btn-secondary']
            ) ?>
        </div>
    </div>

    <!-- Puntaje ponderado final -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card shadow border-0 text-center" style="border-left: 6px solid #17a2b8 !important;">
                <div class="card-body py-4">
                    <div class="text-muted small text-uppercase mb-1">Puntaje Ponderado OC</div>
                    <?php if ($pond !== null): ?>
                        <div class="display-4 font-weight-bold text-<?= $clase ?>">
                            <?= $letra ?>
                        </div>
                        <div class="h4 text-<?= $clase ?>"><?= number_format((float)$pond, 2) ?> / 5</div>
                        <div class="text-muted small">Promedio de <?= $total ?> subcategoría(s)</div>
                    <?php else: ?>
                        <div class="text-muted h5">Sin calificaciones aún</div>
                    <?php endif ?>
                </div>
            </div>
        </div>

        <?php if ($pond !== null): ?>
        <div class="col-md-8">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-dark text-white py-2">
                    <i class="fas fa-info-circle"></i> ¿Cómo se calcula el ponderado?
                </div>
                <div class="card-body py-2" style="font-size:0.85rem;">
                    <p class="mb-1">El puntaje ponderado de la OC es el <strong>promedio simple</strong> de los puntajes de cada subcategoría calificada.</p>
                    <p class="mb-1">Todas las subcategorías tienen el mismo peso, independientemente de la cantidad de unidades.</p>
                    <p class="mb-0">
                        <code>Ponderado = (<?= implode(' + ', array_map(fn($c) => number_format((float)$c->puntaje_total, 2), $calificaciones)) ?>) / <?= $total ?> = <strong><?= number_format((float)$pond, 2) ?></strong></code>
                    </p>
                </div>
            </div>
        </div>
        <?php endif ?>
    </div>

    <!-- Subcategorías pendientes -->
    <?php if (!empty($subcatsPend)): ?>
    <div class="alert alert-warning d-flex align-items-center mb-3">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <div>
            <strong>Subcategorías sin calificar (<?= count($subcatsPend) ?>):</strong>
            <?php foreach ($subcatsPend as $sp): ?>
                <?= Html::a(
                    Html::encode($sp['subcategoria']),
                    ['/calificacion/calificacion/create', 'id_oc' => $id_oc],
                    ['class' => 'badge badge-warning text-dark ml-1']
                ) ?>
            <?php endforeach ?>
        </div>
    </div>
    <?php endif ?>

    <!-- Tabla de subcategorías calificadas -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white py-2">
            <i class="fas fa-list-check"></i>
            Subcategorías calificadas (<?= count($calificaciones) ?>)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0" style="font-size:12px;">
                    <thead class="thead-light">
                        <tr>
                            <th>Subcategoría</th>
                            <th>Categoría</th>
                            <th class="text-center">Uds. Ord.</th>
                            <th class="text-center">Uds. Ent.</th>
                            <th class="text-center">Oportunidad<br><small>10%</small></th>
                            <th class="text-center">Cantidad<br><small>30%</small></th>
                            <th class="text-center">Cal. Criterios<br><small>30%</small></th>
                            <th class="text-center">Cal. Producto<br><small>30%</small></th>
                            <th class="text-center">Puntaje</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($calificaciones)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                No hay subcategorías calificadas para esta OC.
                                <?= Html::a('Calificar ahora', ['/calificacion/calificacion/create', 'id_oc' => $id_oc], ['class' => 'btn btn-sm btn-warning ml-2']) ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($calificaciones as $c):
                            $l = Calificacionproveedor::puntajeALetra($c->puntaje_total);
                            $cl = Calificacionproveedor::letraClase($l);
                        ?>
                        <tr>
                            <td><strong><?= Html::encode($c->subcategoria) ?></strong></td>
                            <td><?= Html::encode($c->categoria) ?></td>
                            <td class="text-center"><?= number_format((int)$c->unidades_ordenadas) ?></td>
                            <td class="text-center"><?= number_format((int)$c->unidades_entregadas) ?></td>
                            <td class="text-center">
                                <?php if ($c->oportunidad_formula !== null): ?>
                                    <?php $oc = Calificacionproveedor::letraClase(Calificacionproveedor::puntajeALetra($c->oportunidad_formula)) ?>
                                    <span class="badge badge-<?= $oc ?>"><?= number_format((float)$c->oportunidad_formula, 1) ?></span>
                                <?php else: ?><span class="text-muted">—</span><?php endif ?>
                            </td>
                            <td class="text-center">
                                <?php if ($c->cantidad_formula !== null): ?>
                                    <?php $cc = Calificacionproveedor::letraClase(Calificacionproveedor::puntajeALetra($c->cantidad_formula)) ?>
                                    <span class="badge badge-<?= $cc ?>"><?= number_format((float)$c->cantidad_formula, 1) ?></span>
                                <?php else: ?><span class="text-muted">—</span><?php endif ?>
                            </td>
                            <td class="text-center">
                                <?php if ($c->calidad_ponderada !== null): ?>
                                    <?php $cq = Calificacionproveedor::letraClase(Calificacionproveedor::puntajeALetra($c->calidad_ponderada)) ?>
                                    <span class="badge badge-<?= $cq ?>"><?= number_format((float)$c->calidad_ponderada, 2) ?></span>
                                <?php else: ?><span class="text-muted">—</span><?php endif ?>
                            </td>
                            <td class="text-center">
                                <?php
                                    $cpEf  = $c->calcularCalidadProductoEfectiva();
                                    $nIncP = (int)($c->num_incumplimientos ?? 0);
                                ?>
                                <?php if ($cpEf !== null): ?>
                                    <?php $cp = Calificacionproveedor::letraClase(Calificacionproveedor::puntajeALetra($cpEf)) ?>
                                    <span class="badge badge-<?= $cp ?>"><?= number_format($cpEf, 2) ?></span>
                                    <?php if ($nIncP > 0): ?>
                                        <br><small class="text-danger" title="<?= $nIncP ?> incumplimiento(s)"><i class="fas fa-exclamation-triangle"></i> <?= (int)$c->calidad_producto ?>/5 −<?= $nIncP ?>inc</small>
                                    <?php endif ?>
                                <?php else: ?><span class="text-muted">—</span><?php endif ?>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?= $cl ?> px-2 py-1" style="font-size:0.85rem;">
                                    <?= $l ?> (<?= number_format((float)$c->puntaje_total, 2) ?>)
                                </span>
                            </td>
                            <td class="text-center text-nowrap">
                                <?= Html::a(
                                    '<i class="fas fa-eye"></i>',
                                    ['/calificacion/calificacion/view', 'id' => $c->id],
                                    ['class' => 'btn btn-xs btn-outline-secondary mr-1', 'title' => 'Ver detalle']
                                ) ?>
                                <?= Html::a(
                                    '<i class="fas fa-edit"></i>',
                                    ['/calificacion/calificacion/update', 'id' => $c->id],
                                    ['class' => 'btn btn-xs btn-outline-primary mr-1', 'title' => 'Editar']
                                ) ?>
                                <?= Html::a(
                                    '<i class="fas fa-trash"></i>',
                                    ['/calificacion/calificacion/delete', 'id' => $c->id],
                                    [
                                        'class' => 'btn btn-xs btn-outline-danger',
                                        'title' => 'Eliminar',
                                        'data-confirm' => '¿Eliminar la calificación de "' . Html::encode($c->subcategoria) . '"?',
                                        'data-method'  => 'post',
                                    ]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach ?>
                        <!-- Fila de totales -->
                        <?php if ($pond !== null): ?>
                        <tr class="table-dark font-weight-bold">
                            <td colspan="8" class="text-right pr-3">
                                <i class="fas fa-equals"></i> PONDERADO FINAL OC
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?= $clase ?> px-2 py-1" style="font-size:0.9rem;">
                                    <?= $letra ?> (<?= number_format((float)$pond, 2) ?>)
                                </span>
                            </td>
                            <td></td>
                        </tr>
                        <?php endif ?>
                    <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
