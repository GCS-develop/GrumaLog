<?php

use yii\helpers\Html;

/** @var array $rows */
/** @var string $codigoBodega */
/** @var string $item */
/** @var string $idColor */
/** @var string $idTalla */
/** @var string $created_from */
/** @var string $created_to */
?>

<div class="mb-2">
    <div><strong>Bodega:</strong> <?= Html::encode($codigoBodega) ?></div>
    <div><strong>Item:</strong> <?= Html::encode($item) ?></div>
    <div><strong>Color/Talla:</strong> <?= Html::encode($idColor ?: 'NA') ?> / <?= Html::encode($idTalla ?: 'NA') ?></div>
    <div><strong>Fechas aplicadas:</strong> <?= Html::encode($created_from ?: '-') ?> a <?= Html::encode($created_to ?: '-') ?></div>
</div>

<hr class="my-2">

<?php if (empty($rows)): ?>
    <div class="alert alert-warning mb-0">
        No se encontraron conteos/marcaciones para este SKU con los filtros actuales.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th>Conteo ID</th>
                    <th>Estado</th>
                    <th>Fecha conteo</th>
                    <th>Marcación ID</th>
                    <th>Sección</th>
                    <th>Ubicación</th>
                    <th class="text-end">Manual (unid)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="text-nowrap"><?= Html::encode($r['conteo_id'] ?? '') ?></td>
                        <td><?= Html::encode($r['estado'] ?? '') ?></td>
                        <td class="text-nowrap"><?= Html::encode($r['conteo_created_at'] ?? '') ?></td>
                        <td class="text-nowrap"><?= Html::encode($r['marcacion_id'] ?? '') ?></td>
                        <td><?= Html::encode($r['seccion'] ?? '') ?></td>
                        <td><?= Html::encode($r['ubicacion'] ?? '') ?></td>
                        <td class="text-end"><?= number_format((float)($r['manual_unidades'] ?? 0), 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>