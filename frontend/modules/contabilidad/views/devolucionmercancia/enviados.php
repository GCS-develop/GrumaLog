<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Documentos de Devolucion Mercancia Enviados';
$f = $filtros ?? [];
?>
<div class="devolucionmercancia-enviados">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="mb-3">
        <?= Html::a('+ Crear nuevo documento', ['index'], ['class' => 'btn btn-success']) ?>
    </div>

    <div class="card card-body mb-3">
        <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['enviados']]); ?>
        <div class="row">
            <div class="col-md-2">
                <label class="control-label">ID</label>
                <input type="text" name="id" class="form-control" value="<?= Html::encode($f['id'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="control-label">Tipo Documento</label>
                <input type="text" name="tipo_documento" class="form-control" value="<?= Html::encode($f['tipo_documento'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="control-label">Proveedor (NIT)</label>
                <input type="text" name="proveedor" class="form-control" value="<?= Html::encode($f['proveedor'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="control-label">Estado</label>
                <select name="estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="enviado" <?= (($f['estado'] ?? '') === 'enviado') ? 'selected' : '' ?>>Enviado</option>
                    <option value="pendiente" <?= (($f['estado'] ?? '') === 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                    <option value="procesando" <?= (($f['estado'] ?? '') === 'procesando') ? 'selected' : '' ?>>Procesando</option>
                    <option value="error" <?= (($f['estado'] ?? '') === 'error') ? 'selected' : '' ?>>Error</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <?= Html::submitButton('Filtrar', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Limpiar', ['enviados'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php if (!empty($documentos)): ?>
        <table class="table table-bordered table-striped table-sm">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Tipo Documento</th>
                    <th>NIT Tercero</th>
                    <th>Nombre Proveedor</th>
                    <th>Fecha Documento</th>
                    <th class="text-right">Valor</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th>Ultima actualizacion</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documentos as $doc): ?>
                    <tr>
                        <td><?= $doc['id'] ?></td>
                        <td><?= Html::encode($doc['tipo_documento']) ?></td>
                        <td><?= Html::encode($doc['nit_tercero']) ?></td>
                        <td><?= Html::encode($doc['proveedor_nombre'] ?? '') ?></td>
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
                                            'confirm' => 'Esta seguro de reenviar este documento a Siesa?',
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
            No hay documentos generados todavia.
        </div>
    <?php endif; ?>
</div>
