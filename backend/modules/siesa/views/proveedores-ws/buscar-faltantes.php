<?php

use yii\helpers\Html;
use common\widgets\Alert;

$this->title = 'Proveedores Faltantes en Local';
$this->params['breadcrumbs'][] = ['label' => 'Proveedores - ERP', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
    .table-faltantes { font-size: 12px; }
    .badge-count { font-size: 14px; }
');
?>

<div class="proveedores-faltantes">

    <?= Alert::widget() ?>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">
                Proveedores en SIESA que <strong>NO existen</strong> en la base local
                <span class="badge badge-danger badge-count ml-2"><?= count($faltantes) ?></span>
            </h5>
            <p class="text-muted mb-0">
                Selecciona los que deseas importar y haz clic en <strong>Sincronizar Seleccionados</strong>.
            </p>
        </div>
    </div>

    <?php if (empty($faltantes)): ?>
        <div class="alert alert-success">
            <strong>¡Todo sincronizado!</strong> No hay proveedores faltantes en la base local.
        </div>
        <p><?= Html::a('Volver', ['index'], ['class' => 'btn btn-secondary']) ?></p>
    <?php else: ?>

        <?php $form = \yii\widgets\ActiveForm::begin([
            'action' => ['sincronizarfaltantes'],
            'method' => 'post',
            'id'     => 'form-faltantes',
        ]); ?>

        <div class="mb-2">
            <?= Html::submitButton(
                'Sincronizar Seleccionados (<span id="cnt">0</span>)',
                ['class' => 'btn btn-success', 'id' => 'btn-sync']
            ) ?>
            &nbsp;
            <button type="button" class="btn btn-primary" id="btn-all">Seleccionar Todos</button>
            <button type="button" class="btn btn-secondary" id="btn-none">Deseleccionar Todos</button>
            &nbsp;&nbsp;
            <?= Html::a('Volver', ['index'], ['class' => 'btn btn-light']) ?>
        </div>

        <table class="table table-bordered table-hover table-sm table-faltantes">
            <thead class="thead-dark">
                <tr>
                    <th style="width:40px">
                        <input type="checkbox" id="chk-master">
                    </th>
                    <th>#</th>
                    <th>NIT</th>
                    <th>Razón Social</th>
                    <th>Sucursal</th>
                    <th>Descripción Sucursal</th>
                    <th>Tipo ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($faltantes as $i => $prov): ?>
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="nits[]"
                               value="<?= Html::encode($prov['nit']) ?>"
                               class="chk-faltante">
                    </td>
                    <td><?= $i + 1 ?></td>
                    <td><?= Html::encode($prov['nit']) ?></td>
                    <td><?= Html::encode($prov['razonSocial']) ?></td>
                    <td><?= Html::encode($prov['sucursal'] ?? '-') ?></td>
                    <td><?= Html::encode($prov['descripcionSucursal'] ?? '-') ?></td>
                    <td><?= Html::encode($prov['tipoIdentificacion'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php \yii\widgets\ActiveForm::end(); ?>

    <?php endif; ?>

</div>

<?php
$this->registerJs("
    function actualizarContador() {
        var n = $('.chk-faltante:checked').length;
        $('#cnt').text(n);
        $('#btn-sync').prop('disabled', n === 0);
    }

    $('#chk-master').on('change', function () {
        $('.chk-faltante').prop('checked', this.checked);
        actualizarContador();
    });

    $('.chk-faltante').on('change', actualizarContador);

    $('#btn-all').on('click', function () {
        $('.chk-faltante, #chk-master').prop('checked', true);
        actualizarContador();
    });

    $('#btn-none').on('click', function () {
        $('.chk-faltante, #chk-master').prop('checked', false);
        actualizarContador();
    });

    actualizarContador();
");
?>
