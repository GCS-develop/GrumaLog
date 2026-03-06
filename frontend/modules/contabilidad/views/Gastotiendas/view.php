<?php
use yii\helpers\Html;

$this->title = "Detalle del Documento {$cabecera['F350_CONSEC_DOCTO']}";

// Calcular totales (excluyendo contrapartida)
$totalDebito = 0;
$totalCredito = 0;
foreach ($detalle as $row) {
    if ($row['F351_ID_AUXILIAR'] == '524015') {
        continue; // Ignorar contrapartida
    }
    $totalDebito += $row['F351_VALOR_DB'];
    $totalCredito += $row['F351_VALOR_CR'];
}

$respuestaRaw = trim((string)($cabecera['respuesta_siesa'] ?? ''));
$respuestaSiesa = [
    'isJson' => false,
    'codigo' => '',
    'mensaje' => '',
    'detalle' => [],
];

if ($respuestaRaw !== '') {
    $decoded = json_decode($respuestaRaw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $respuestaSiesa['isJson'] = true;
        $respuestaSiesa['codigo'] = trim((string)($decoded['codigo'] ?? ''));
        $respuestaSiesa['mensaje'] = trim((string)($decoded['mensaje'] ?? ''));

        $detalleRaw = $decoded['detalle'] ?? [];
        if (is_string($detalleRaw)) {
            $detalleDecoded = json_decode($detalleRaw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($detalleDecoded)) {
                $detalleRaw = $detalleDecoded;
            }
        }

        if (is_array($detalleRaw)) {
            $respuestaSiesa['detalle'] = $detalleRaw;
        }
    }
}
?>

<div class="container-fluid documento-view">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= Html::encode($this->title) ?></h2>
        <div>
            <?= Html::a('← Volver', ['index'], ['class' => 'btn btn-secondary']) ?>

            <?php
            // Botón ANULAR: solo si está pendiente (0) o error (2)
            if ($cabecera['estado_envio'] == 0 || $cabecera['estado_envio'] == 2) {
                echo Html::a('Anular', ['anular', 'id' => $cabecera['ID_TRANSACCION']], [
                    'class' => 'btn btn-danger',
                    'data' => ['confirm' => '¿Seguro que deseas anular este documento?', 'method' => 'post'],
                ]);

                // Botón EDITAR DETALLES: también solo en pendiente o error
                echo Html::a('Editar Detalles', ['update-detalle', 'id' => $cabecera['ID_TRANSACCION']], [
                    'class' => 'btn btn-info',
                ]);
            }

            // Botón ENVIAR A SIESA: solo si está pendiente (0) o error (2)
            if ($cabecera['estado_envio'] == 0 || $cabecera['estado_envio'] == 2) {
                echo Html::a('Enviar a Siesa', ['enviar-siesa', 'id' => $cabecera['ID_TRANSACCION']], [
                    'class' => 'btn btn-warning',
                    'data' => ['confirm' => '¿Seguro que deseas enviar este documento a Siesa?', 'method' => 'post'],
                ]);
            }
            ?>
        </div>
    </div>

    <!-- Encabezado -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Encabezado del Documento
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-bordered mb-0">
                <tr><th>CO Tienda</th><td><?= $cabecera['F350_ID_CO'] ?></td></tr>
                <tr><th>Tipo Documento</th><td><?= $cabecera['F350_ID_TIPO_DOCTO'] ?></td></tr>
                <tr><th>Consecutivo Siesa</th><td><?= $cabecera['F350_CONSEC_DOCTO'] ?></td></tr>
                <tr><th>Fecha</th><td><?= $cabecera['F350_FECHA'] ?></td></tr>
                <tr><th>Tercero</th><td><?= $cabecera['F350_ID_TERCERO'] ?></td></tr>
                <tr><th>Notas</th><td><?= $cabecera['F350_NOTAS'] ?></td></tr>
                <tr>
                    <th>Estado de Envío</th>
                    <td>
                        <?php
                        if ($cabecera['estado_envio'] == 3 || $cabecera['F350_IND_ESTADO'] == 0) {
                            echo '<span class="badge bg-dark">Anulado</span>';
                        } else {
                            switch ($cabecera['estado_envio']) {
                                case 0:
                                    echo '<span class="badge bg-warning">Pendiente</span>';
                                    break;
                                case 1:
                                    echo '<span class="badge bg-success">Enviado</span>';
                                    break;
                                case 2:
                                    echo '<span class="badge bg-danger">Error</span>';
                                    break;
                                default:
                                    echo '<span class="badge bg-secondary">Desconocido</span>';
                            }
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Respuesta Siesa</th>
                    <td>
                        <?php if ($respuestaRaw === ''): ?>
                            <span class="text-muted">Sin respuesta</span>
                        <?php elseif ($respuestaSiesa['isJson']): ?>
                            <?php
                            $codigo = $respuestaSiesa['codigo'];
                            $mensaje = $respuestaSiesa['mensaje'];
                            $detalles = $respuestaSiesa['detalle'];
                            $esError = $codigo !== '' && $codigo !== '0';
                            ?>

                            <div class="alert <?= $esError ? 'alert-danger' : 'alert-success' ?> mb-2">
                                <div><strong>Código:</strong> <?= Html::encode($codigo !== '' ? $codigo : 'N/D') ?></div>
                                <div><strong>Mensaje:</strong> <?= Html::encode($mensaje !== '' ? $mensaje : 'Sin mensaje') ?></div>
                            </div>

                            <?php if (!empty($detalles)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-2">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Línea</th>
                                                <th>Tipo Reg.</th>
                                                <th>Subtipo</th>
                                                <th>Auxiliar</th>
                                                <th>Detalle</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($detalles as $err): ?>
                                                <?php if (!is_array($err)) {
                                                    continue;
                                                } ?>
                                                <tr>
                                                    <td><?= Html::encode((string)($err['f_nro_linea'] ?? '-')) ?></td>
                                                    <td><?= Html::encode((string)($err['f_tipo_reg'] ?? '-')) ?></td>
                                                    <td><?= Html::encode((string)($err['f_subtipo_reg'] ?? '-')) ?></td>
                                                    <td><?= Html::encode((string)($err['f_valor'] ?? '-')) ?></td>
                                                    <td><?= Html::encode((string)($err['f_detalle'] ?? 'Sin detalle')) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>

                            <details>
                                <summary class="text-muted" style="cursor:pointer;">Ver respuesta técnica completa</summary>
                                <pre class="mb-0" style="margin-top:8px; white-space:pre-wrap; word-break:break-word; max-height:220px; overflow:auto;"><?= Html::encode($respuestaRaw) ?></pre>
                            </details>
                        <?php else: ?>
                            <div class="alert alert-info mb-0" style="white-space:pre-line;">
                                <?= Html::encode($respuestaRaw) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Detalle -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            Detalle de Movimientos
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Cuenta Auxiliar</th>
                        <th>Tercero</th>
                        <th>Débito</th>
                        <th>Crédito</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalle as $row): ?>
                        <?php if ($row['F351_ID_AUXILIAR'] == '524015') {
                            continue; // Ocultar contrapartida
                        } ?>

                        <?php
                        // Buscar descripción de la cuenta
                        $cuenta = \frontend\models\GrupoConceptoCuenta::findOne(['cuenta' => $row['F351_ID_AUXILIAR']]);
                        $descripcionCuenta = $cuenta ? $cuenta->descripcion : $row['F351_ID_AUXILIAR'];
                        ?>

                        <tr>
                            <td><?= Html::encode($descripcionCuenta) ?></td>
                            <td><?= $row['F351_ID_TERCERO'] ?></td>
                            <td><?= number_format($row['F351_VALOR_DB'], 2) ?></td>
                            <td><?= number_format($row['F351_VALOR_CR'], 2) ?></td>
                            <td><?= Html::encode($row['F351_NOTAS']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

                <tfoot class="table-light">
                    <tr>
                        <th colspan="2" class="text-end">Totales:</th>
                        <th><?= number_format($totalDebito, 2) ?></th>
                        <th><?= number_format($totalCredito, 2) ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
