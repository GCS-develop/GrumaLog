<?php
use yii\helpers\Html;

$this->title = 'Documentos de Devolución Mercancía Enviados';
?>
<div class="devolucionmercancia-enviados">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="mb-3">
        <?= Html::a('➕ Crear nuevo documento', ['index'], ['class' => 'btn btn-success']) ?>
    </div>

    <?php if (!empty($documentos)): ?>
        <table class="table table-bordered table-striped table-sm">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Tipo Documento</th>
                   
                    <th>NIT Tercero</th>
                    <th>Fecha Documento</th>
                    <th class="text-right">Valor</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th>Última actualización</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documentos as $doc): ?>
                    <tr>
                        <td><?= $doc['id'] ?></td>
                        <td><?= Html::encode($doc['tipo_documento']) ?></td>
                   
                        <td><?= Html::encode($doc['nit_tercero']) ?></td>
                        <td><?= Html::encode($doc['fecha_documento']) ?></td>
                        <td class="text-right">$ <?= number_format($doc['valor_documento'], 0, ',', '.') ?></td>
                        <td>
                            <?php if ($doc['estado'] === 'enviado'): ?>
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Enviado</span>
                            <?php elseif ($doc['estado'] === 'pendiente'): ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pendiente</span>
                            <?php elseif ($doc['estado'] === 'procesando'): ?>
                                <span class="badge bg-info text-dark"><i class="fas fa-spinner fa-spin"></i> Procesando</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Error</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $doc['created_at'] ?></td>
                        <td><?= $doc['updated_at'] ?></td>
                        <td>
                            <?= Html::a('Ver', ['view', 'id' => $doc['id']], ['class' => 'btn btn-sm btn-primary']) ?>
                            <?php if ($doc['estado'] !== 'enviado'): ?>
                                <?= Html::a(
                                    '<i class="fas fa-paper-plane"></i> Enviar a Siesa',
                                    ['reenviar', 'id' => $doc['id']],
                                    [
                                        'class' => 'btn btn-sm btn-danger',
                                        'data' => [
                                            'method' => 'post',
                                            'confirm' => '¿Está seguro de reenviar este documento a Siesa?',
                                        ],
                                    ]
                                ) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">
            No hay documentos generados todavía.
        </div>
    <?php endif; ?>
</div>
