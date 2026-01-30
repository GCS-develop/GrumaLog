<?php
use yii\helpers\Html;

/** @var array $documento */
/** @var array $detalle */

$this->title = "Documento #{$documento['id']}";

// Filtrar los que tienen existencia
$detalleValidos = array_filter($detalle, fn($d) => ($d['sin_existencia'] ?? 0) == 0);

// Totales SOLO de los válidos
$totalUnidades = !empty($detalleValidos) ? array_sum(array_column($detalleValidos, 'cantidad')) : 0;
$totalCosto    = !empty($detalleValidos) ? array_sum(array_column($detalleValidos, 'costo_total')) : 0;

// Valor total del documento en BD (el que se envió a Siesa)
$valorDocumento = isset($documento['valor_documento']) ? (float)$documento['valor_documento'] : 0;

// Calcular diferencia para detectar descuadres
$diferencia = round($valorDocumento - $totalCosto, 2);
?>
<h1><?= Html::encode($this->title) ?></h1>

<p>
    <?= Html::a('⬅ Volver a la lista', ['enviados'], ['class' => 'btn btn-secondary']) ?>
</p>

<!-- Cabecera -->
<div class="card mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered mb-0">
            <tr>
                <th>Tipo Documento</th>
                <td><?= Html::encode($documento['tipo_documento']) ?></td>
            </tr>
            <tr>
                <th>Fecha Documento</th>
                <td><?= Html::encode($documento['fecha_documento']) ?></td>
            </tr>
            <tr>
                <th>NIT Tercero</th>
                <td><?= Html::encode($documento['nit_tercero']) ?></td>
            </tr>
            <tr>
                <th>Valor Documento (enviado a Siesa)</th>
                <td class="text-end fw-bold">$ <?= number_format($valorDocumento, 2, ',', '.') ?></td>
            </tr>
            <tr>
                <th>Total calculado (según detalle)</th>
                <td class="text-end fw-bold">$ <?= number_format($totalCosto, 2, ',', '.') ?></td>
            </tr>
            <tr>
                <th>Diferencia</th>
                <td class="text-end <?= abs($diferencia) > 0.01 ? 'text-danger fw-bold' : 'text-success' ?>">
                    $ <?= number_format($diferencia, 2, ',', '.') ?>
                    <?= abs($diferencia) > 0.01 ? '⚠ Descuadre detectado' : '✔ Cuadrado' ?>
                </td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>
                    <?php if ($documento['estado'] === 'enviado'): ?>
                        <span class="badge bg-success">Enviado</span>
                    <?php elseif ($documento['estado'] === 'pendiente'): ?>
                        <span class="badge bg-warning text-dark">Pendiente</span>
                    <?php elseif ($documento['estado'] === 'procesando'): ?>
                        <span class="badge bg-info text-dark">Procesando</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Error</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Respuesta Siesa</th>
                <td>
                    <?php 
                    $respuesta = $documento['respuesta_siesa'] ?? '';
                    $textClass = 'text-muted';
                    if (stripos($respuesta, 'Error') !== false) {
                        $textClass = 'text-danger';
                    } elseif (stripos($respuesta, 'OK') !== false || stripos($respuesta, 'enviado') !== false) {
                        $textClass = 'text-success';
                    } elseif (stripos($respuesta, 'Procesando') !== false) {
                        $textClass = 'text-warning';
                    } elseif (stripos($respuesta, 'Pendiente') !== false) {
                        $textClass = 'text-primary';
                    }
                    ?>
                    <pre class="mb-0 <?= $textClass ?>" style="white-space: pre-wrap; max-height: 300px; overflow-y: auto;">
<?= Html::encode($respuesta) ?>
                    </pre>
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- Detalle -->
<h3>Detalle (solo ítems con existencia)</h3>
<?php if (!empty($detalleValidos)): ?>
    <table class="table table-bordered table-sm">
        <thead class="thead-dark">
            <tr>
                <th>Item</th>
                <th>Descripción</th>
                <th>Ext 1</th>
                <th>Ext 2</th>
                <th>Unidad</th>
                <th>Bodega</th>
                <th class="text-end">Cantidad</th>
                <th class="text-end">Precio Unitario</th>
                <th class="text-end">Costo Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalleValidos as $row): ?>
                <tr>
                    <td><?= Html::encode($row['item']) ?></td>
                    <td><?= Html::encode($row['descripcion']) ?></td>
                    <td><?= Html::encode($row['extension1']) ?></td>
                    <td><?= Html::encode($row['extension2']) ?></td>
                    <td><?= Html::encode($row['unidad']) ?></td>
                    <td><?= Html::encode($row['bodega']) ?></td>
                    <td class="text-end"><?= number_format($row['cantidad'], 0, ',', '.') ?></td>
                    <td class="text-end"><?= number_format($row['precio_unitario'], 2, ',', '.') ?></td>
                    <td class="text-end"><?= number_format($row['costo_total'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="6" class="text-end">TOTALES</td>
                <td class="text-end"><?= number_format($totalUnidades, 0, ',', '.') ?></td>
                <td></td>
                <td class="text-end">$ <?= number_format($totalCosto, 2, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>
<?php else: ?>
    <div class="alert alert-warning">No hay ítems con existencia válida.</div>
<?php endif; ?>
