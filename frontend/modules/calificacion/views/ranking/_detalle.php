<?php
/** @var frontend\models\Calificacionproveedor[] $historial */

use frontend\models\Calificacionproveedor;
use yii\helpers\Html;
use yii\helpers\Url;
?>

<?php if (empty($historial)): ?>
    <p class="text-center text-muted py-3">No hay calificaciones para este proveedor.</p>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-sm table-striped mb-0" style="font-size:12px">
        <thead class="thead-dark">
            <tr>
                <th>N° OC</th>
                <th class="text-center">Oportunidad<br><small class="text-muted">10%</small></th>
                <th class="text-center">Cantidad<br><small class="text-muted">30%</small></th>
                <th class="text-center">Cal. Criterios<br><small class="text-muted">30%</small></th>
                <th class="text-center">Cal. Producto<br><small class="text-muted">30%</small></th>
                <th class="text-center">Puntaje Total</th>
                <th class="text-center">Fecha</th>
                <th class="text-center">Calificado Por</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($historial as $cal): ?>
        <?php
            $lt = Calificacionproveedor::puntajeALetra($cal->puntaje_total);
            $lo = Calificacionproveedor::puntajeALetra($cal->oportunidad_formula);
            $lc = Calificacionproveedor::puntajeALetra($cal->cantidad_formula);
            $lq = Calificacionproveedor::puntajeALetra($cal->calidad_ponderada);
            $lp = $cal->calidad_producto ? Calificacionproveedor::puntajeALetra($cal->calidad_producto) : null;
        ?>
        <tr>
            <td><strong><?= Html::encode($cal->numero_oc) ?></strong></td>
            <td class="text-center">
                <span class="badge badge-<?= Calificacionproveedor::letraClase($lo) ?>"><?= $lo ?> (<?= $cal->oportunidad_formula ?>)</span>
            </td>
            <td class="text-center">
                <span class="badge badge-<?= Calificacionproveedor::letraClase($lc) ?>"><?= $lc ?> (<?= $cal->cantidad_formula ?>)</span>
            </td>
            <td class="text-center">
                <span class="badge badge-<?= Calificacionproveedor::letraClase($lq) ?>"><?= $lq ?> (<?= number_format((float)$cal->calidad_ponderada, 2) ?>)</span>
            </td>
            <td class="text-center">
                <?php if ($lp): ?>
                <span class="badge badge-<?= Calificacionproveedor::letraClase($lp) ?>"><?= $lp ?> (<?= $cal->calidad_producto ?>)</span>
                <?php else: ?><span class="text-muted">—</span><?php endif ?>
            </td>
            <td class="text-center">
                <span class="badge badge-<?= Calificacionproveedor::letraClase($lt) ?> badge-pill px-2">
                    <?= $lt ?> (<?= number_format((float)$cal->puntaje_total, 2) ?>)
                </span>
            </td>
            <td class="text-center text-muted"><?= substr($cal->created_at ?? '', 0, 10) ?></td>
            <td class="text-center"><?= Html::encode($cal->createdByUser->username ?? 'N/A') ?></td>
            <td>
                <?= Html::a('<i class="fas fa-eye"></i>', Url::to(['/calificacion/calificacion/view', 'id' => $cal->id]), ['class' => 'btn btn-xs btn-outline-secondary', 'target' => '_blank']) ?>
            </td>
        </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
<?php endif ?>
