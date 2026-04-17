<?php
/** @var yii\web\View $this */
/** @var frontend\models\Calificacionproveedor $model */

use frontend\models\Calificacionproveedor;
use yii\helpers\Html;

$this->title = 'Calificación #' . $model->id . ' — ' . $model->numero_oc;
$this->params['breadcrumbs'][] = ['label' => 'Calificación Proveedores', 'url' => ['/calificacion/calificacion/index']];
$this->params['breadcrumbs'][] = $this->title;

$letraTotal = Calificacionproveedor::puntajeALetra($model->puntaje_total);
$claseTotal = Calificacionproveedor::letraClase($letraTotal);

// Criterios con peso (contribuyen al puntaje de calidad)
$criterios = [
    ['caja_bulto',          '5%',  'Caja / Bulto'],
    ['calibre',             '5%',  'Calibre'],
    ['rotulo',              '5%',  'Rótulo'],
    ['contiene_documentos', '5%',  'Contiene Documentos'],
    ['separa_tallas',       '5%',  'Separa Tallas'],
    ['separa_color',        '5%',  'Separa Color'],
    ['separa_referencia',   '5%',  'Separa Referencia'],
    ['etiquetado',          '5%',  'Etiquetado'],
    ['error_tiqueteo',      '20%', 'Error Tiqueteo'],
    ['homologacion',        '5%',  'Homologación'],
    ['precio',              '5%',  'Precio'],
    ['novedad',             '15%', 'Novedad'],
    ['factura',             '10%', 'Factura'],
    ['orden_compra_doc',    '5%',  'Orden de Compra (doc)'],
];

// Criterios informativos (sin peso en el puntaje)
$criteriosInfo = [
    ['gancho',  'Gancho'],
    ['tallero', 'Tallero'],
];
?>

<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-star text-warning"></i> <?= Html::encode($this->title) ?></h4>
        <div>
            <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary mr-1']) ?>
            <?php if ($model->id_ordendecompra): ?>
                <?= Html::a('<i class="fas fa-chart-bar"></i> Ponderado OC', ['ponderado', 'id_oc' => $model->id_ordendecompra], ['class' => 'btn btn-info mr-1']) ?>
            <?php endif ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <!-- ===== Resumen puntaje (5 columnas) ===== -->
    <div class="col-12 mb-3 px-0">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row text-center">

                    <!-- PUNTAJE TOTAL -->
                    <div class="col-md-3 border-right">
                        <div class="text-muted small font-weight-bold">PUNTAJE TOTAL</div>
                        <div class="display-4 font-weight-bold text-<?= $claseTotal ?>">
                            <?= $letraTotal ?>
                        </div>
                        <div class="text-muted"><?= number_format((float)$model->puntaje_total, 2) ?> / 5</div>
                    </div>

                    <!-- OPORTUNIDAD 10% -->
                    <div class="col-md-2 border-right">
                        <?php $lO = Calificacionproveedor::puntajeALetra($model->oportunidad_formula) ?>
                        <div class="text-muted small">OPORTUNIDAD <span class="badge badge-secondary">10%</span></div>
                        <div class="h2 font-weight-bold text-<?= Calificacionproveedor::letraClase($lO) ?>"><?= $lO ?></div>
                        <div class="text-muted"><?= number_format((float)$model->oportunidad_formula, 1) ?> / 5</div>
                    </div>

                    <!-- CANTIDAD 30% -->
                    <div class="col-md-2 border-right">
                        <?php $lC = Calificacionproveedor::puntajeALetra($model->cantidad_formula) ?>
                        <div class="text-muted small">CANTIDAD <span class="badge badge-secondary">30%</span></div>
                        <div class="h2 font-weight-bold text-<?= Calificacionproveedor::letraClase($lC) ?>"><?= $lC ?></div>
                        <div class="text-muted"><?= number_format((float)$model->cantidad_formula, 1) ?> / 5</div>
                    </div>

                    <!-- CALIDAD CRITERIOS 30% -->
                    <div class="col-md-2 border-right">
                        <?php $lQ = Calificacionproveedor::puntajeALetra($model->calidad_ponderada) ?>
                        <div class="text-muted small"> CALIDAD ENTREGA <span class="badge badge-secondary">30%</span></div>
                        <div class="h2 font-weight-bold text-<?= Calificacionproveedor::letraClase($lQ) ?>"><?= $lQ ?></div>
                        <div class="text-muted"><?= number_format((float)$model->calidad_ponderada, 2) ?> / 5</div>
                    </div>

                    <!-- CALIDAD DEL PRODUCTO 30% -->
                    <div class="col-md-3">
                        <?php
                            $cpEf   = $model->calcularCalidadProductoEfectiva();
                            $cpRaw  = $model->calidad_producto ? (float)$model->calidad_producto : null;
                            $lP     = $cpEf !== null ? Calificacionproveedor::puntajeALetra($cpEf) : '—';
                            $claseP = $cpEf !== null ? Calificacionproveedor::letraClase($lP) : 'secondary';
                            $nInc   = (int)($model->num_incumplimientos ?? 0);
                        ?>
                        <div class="text-muted small font-weight-bold">CALIDAD DEL PRODUCTO <span class="badge badge-dark">30%</span></div>
                        <div class="h2 font-weight-bold text-<?= $claseP ?>"><?= $lP ?></div>
                        <div class="text-muted">
                            <?php if ($cpEf !== null): ?>
                                <?= number_format($cpEf, 2) ?> / 5
                                <?php if ($nInc > 0): ?>
                                    <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> ingresado: <?= $cpRaw ?>, <?= $nInc ?> incumpl.</small>
                                <?php endif ?>
                            <?php else: ?>—<?php endif ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <!-- Datos OC -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-dark text-white py-2">Datos de la OC</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted">N° OC</td><td><strong><?= Html::encode($model->numero_oc) ?></strong></td></tr>
                        <tr><td class="text-muted">Proveedor</td><td><?= Html::encode($model->proveedor) ?></td></tr>
                        <tr><td class="text-muted">Categoría</td><td><?= Html::encode($model->categoria) ?></td></tr>
                        <tr><td class="text-muted">Subcategoría</td><td><?= Html::encode($model->subcategoria) ?></td></tr>
                        <tr><td class="text-muted">Tipo Mercancía</td><td><?= Html::encode($model->tipo_mercancia) ?></td></tr>
                        <tr><td class="text-muted">Producto</td><td><?= Html::encode($model->producto) ?></td></tr>
                        <tr><td class="text-muted">Transportadora</td><td><?= Html::encode($model->transportadora) ?></td></tr>
                        <tr><td class="text-muted">Revisado Por</td><td><?= Html::encode($model->revisado_por) ?></td></tr>
                        <tr><td class="text-muted">Uds. Ordenadas</td><td><?= number_format($model->unidades_ordenadas) ?></td></tr>
                        <tr><td class="text-muted">Uds. Entregadas</td><td><?= number_format($model->unidades_entregadas) ?>
                            <?php if ($model->unidades_ordenadas): ?>
                                <small class="text-muted">(<?= number_format($model->unidades_entregadas / $model->unidades_ordenadas * 100, 1) ?>%)</small>
                            <?php endif ?>
                        </td></tr>
                        <tr><td class="text-muted">N° Incumplimientos</td><td>
                            <?php $ni = (int)($model->num_incumplimientos ?? 0); ?>
                            <?php if ($ni > 0): ?>
                                <span class="badge badge-danger"><?= $ni ?></span>
                            <?php else: ?>
                                <span class="badge badge-success">0</span>
                            <?php endif ?>
                        </td></tr>
                        <tr><td class="text-muted">Fecha Cita</td><td><?= Html::encode(substr($model->fecha_entrega_cita ?? '', 0, 10)) ?></td></tr>
                        <tr><td class="text-muted">Fecha Entrega OC</td><td><?= Html::encode(substr($model->fecha_entrega_oc ?? '', 0, 10)) ?></td></tr>
                        <tr><td class="text-muted">Calificado Por</td><td><?= Html::encode($model->createdByUser->username ?? 'N/A') ?></td></tr>
                        <tr><td class="text-muted">Fecha Calificación</td><td><?= Html::encode(substr($model->created_at ?? '', 0, 10)) ?></td></tr>
                    </table>
                </div>
            </div>

            <?php if ($model->observacion): ?>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-secondary text-white py-2">Observación</div>
                <div class="card-body">
                    <p class="mb-0"><?= nl2br(Html::encode($model->observacion)) ?></p>
                </div>
            </div>
            <?php endif ?>
        </div>

        <!-- Criterios de calidad -->
        <div class="col-md-7">

            <!-- Criterios con peso -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-dark text-white py-2">Criterios de Calidad</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Criterio</th>
                                <th class="text-center">Peso</th>
                                <th class="text-center">Puntaje</th>
                                <th class="text-center">Letra</th>
                                <th class="text-center">Contribución</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $pesos = Calificacionproveedor::PESOS_CALIDAD;
                        foreach ($criterios as [$campo, $pesoStr, $etiqueta]):
                            $puntaje = (float)($model->$campo ?? 0);
                            $peso    = $pesos[$campo];
                            $contrib = $puntaje * $peso;
                            $letra   = Calificacionproveedor::puntajeALetra($puntaje);
                            $clase   = Calificacionproveedor::letraClase($letra);
                        ?>
                        <tr>
                            <td><?= $etiqueta ?></td>
                            <td class="text-center"><?= $pesoStr ?></td>
                            <td class="text-center"><strong><?= $puntaje ?></strong></td>
                            <td class="text-center"><span class="badge badge-<?= $clase ?>"><?= $letra ?></span></td>
                            <td class="text-center"><?= number_format($contrib, 3) ?></td>
                        </tr>
                        <?php endforeach ?>
                        <tr class="table-info font-weight-bold">
                            <td colspan="4">Calidad ponderada total</td>
                            <td class="text-center"><?= number_format((float)$model->calidad_ponderada, 2) ?> / 5</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Criterios informativos (gancho / tallero) -->
            <?php
            $tieneInfo = $model->gancho || $model->tallero;
            ?>
            <?php if ($tieneInfo): ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white py-2">
                    <i class="fas fa-info-circle"></i> Criterios Informativos <small class="ml-1 opacity-75">(sin peso en el puntaje)</small>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Criterio</th>
                                <th class="text-center">Puntaje</th>
                                <th class="text-center">Letra</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($criteriosInfo as [$campo, $etiqueta]):
                            if (!$model->$campo) continue;
                            $puntaje = (float)$model->$campo;
                            $letra   = Calificacionproveedor::puntajeALetra($puntaje);
                            $clase   = Calificacionproveedor::letraClase($letra);
                        ?>
                        <tr>
                            <td><?= $etiqueta ?></td>
                            <td class="text-center"><strong><?= $puntaje ?></strong></td>
                            <td class="text-center"><span class="badge badge-<?= $clase ?>"><?= $letra ?></span></td>
                        </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif ?>

        </div>

    </div>
</div>
