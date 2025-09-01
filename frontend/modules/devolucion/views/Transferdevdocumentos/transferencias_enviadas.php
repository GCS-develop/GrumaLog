<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

$this->title = 'Transferencias Enviadas';
?>

<h3><?= Html::encode($this->title) ?></h3>

<hr>

<h4>Historial de Documentos Enviados</h4>

<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Centro</th>
            <th>Tipo</th>
            <th>Fecha</th>
            <th>Nit Proveedor</th>
            <th>Proveedor</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>



<?php foreach ($documentosEnviados as $doc): ?>
<tr>
    <td><?= Html::encode($doc->id_transferencia) ?></td>
    <td><?= Html::encode($doc->centro_operacion) ?></td>
    <td><?= Html::encode($doc->tipo_documento) ?></td>
    <td><?= Html::encode($doc->fecha_documento) ?></td>
    <td><?= Html::encode($doc->tercero_proveedor) ?></td>
    <td><?= Html::encode($doc->proveedor->razonSocial ?? '(Sin nombre)') ?></td>


    <td>
        <?php if ($doc->estado_envio == 1): ?>
            <span class="badge badge-success">ENVIADO</span>
        <?php elseif ($doc->estado_envio == 9): ?>
            <span class="badge badge-secondary">ANULADO</span>
        <?php else: ?>
            <span class="badge badge-danger">NO ENVIADO</span>
        <?php endif; ?>
    </td>
    <td>
        <?= Html::a('👁️', ['ver-transferencia', 'id' => $doc->id_transferencia], [
            'title' => 'Ver',
            'class' => 'btn btn-sm btn-primary'
        ]) ?>
        <?php if ($doc->estado_envio != 1 && $doc->estado_envio != 9): ?>
            <?= Html::a('🗑️', ['anular-transferencia', 'id' => $doc->id_transferencia], [
                'title' => 'Anular',
                'class' => 'btn btn-sm btn-dark',
                'data-confirm' => '¿Estás seguro de anular esta transferencia?',
            ]) ?>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>

    </tbody>
</table>
