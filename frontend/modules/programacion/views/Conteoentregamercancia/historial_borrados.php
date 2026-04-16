<?php

use yii\helpers\Html;
use yii\grid\GridView;

$oc = $modelprogramacion->agendaEntregaMercancia->ordenCompra;
$labelOC = $oc->tipoDocumento->codigo . '-' . $oc->cO->codigo . '-' . $oc->consecutivo;

$this->title = 'Historial de Borrados — OC: ' . $labelOC;
?>

<div class="container-fluid">

    <h3>
        Historial de Borrados de Conteo
        <small style="color:#666;">OC: <?= Html::encode($labelOC) ?></small>
    </h3>

    <p>
        <?= Html::a('&larr; Volver al Conteo', ['indexprogramacion', 'idprogramacion' => $modelprogramacion->id], ['class' => 'btn btn-default btn-sm']) ?>
    </p>

    <?php if (empty($logs)): ?>
        <div class="alert alert-info">No hay registros de borrado para esta programación.</div>
    <?php else: ?>

    <table class="table table-bordered table-striped table-condensed" style="font-size:13px;">
        <thead>
            <tr style="background:#c0392b; color:#fff;">
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Item</th>
                <th>Color</th>
                <th>Talla</th>
                <th style="text-align:right;">Unid. Antes</th>
                <th style="text-align:right;">Unid. Borradas</th>
                <th>OC Referencia</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= Html::encode($log->created_at) ?></td>
                <td><?= Html::encode($log->user ? $log->user->username : 'ID:' . $log->created_by) ?></td>
                <td>
                    <?php
                    $etiquetas = [
                        'BORRAR_TODOS'  => ['label' => 'Borrar TODOS',   'class' => 'label-danger'],
                        'REDUCIR_ITEM'  => ['label' => 'Reducir Item',   'class' => 'label-warning'],
                        'REDUCIR_FILA'  => ['label' => 'Reducir Fila',   'class' => 'label-warning'],
                        'REDUCIR_TALLA' => ['label' => 'Reducir Talla',  'class' => 'label-warning'],
                        'ELIMINAR_FILA' => ['label' => 'Eliminar Fila',  'class' => 'label-danger'],
                    ];
                    $e = $etiquetas[$log->accion] ?? ['label' => $log->accion, 'class' => 'label-default'];
                    echo '<span class="label ' . $e['class'] . '">' . Html::encode($e['label']) . '</span>';
                    ?>
                </td>
                <td><?= Html::encode($log->item ?? '—') ?></td>
                <td><?= Html::encode($log->color ?? '—') ?></td>
                <td><?= Html::encode($log->talla ?? '—') ?></td>
                <td style="text-align:right;"><?= (int)$log->unidadesAntes ?></td>
                <td style="text-align:right; color:#c0392b; font-weight:bold;"><?= (int)$log->unidadesBorradas ?></td>
                <td><?= Html::encode(trim(($log->tipoDocumentoOC ?? '') . '-' . ($log->codigoCO ?? '') . '-' . ($log->consecutivoOC ?? ''), '-')) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php endif; ?>

</div>
