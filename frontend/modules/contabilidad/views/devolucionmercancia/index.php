<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $model frontend\modules\contabilidad\models\DevolucionmercanciaSearch */
/** @var $data array */
/** @var $resultados array */

$this->title = 'Consulta de Devoluciones de Mercancía';
?>

<h1><?= Html::encode($this->title) ?></h1>

<div class="devolucionmercancia-search card card-body mb-3">
    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'options' => ['class' => 'mb-0'],
    ]); ?>

    <div class="row">
        <div class="col-12">
            <h5 class="mb-3">Filtros</h5>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'fecha_inicio')->input('date') ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'fecha_fin')->input('date') ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'tipo_documento')->textInput([
                'maxlength' => 3,
                'placeholder' => 'Ej: 2EC',
                'style' => 'text-transform: uppercase;',
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'consecutivo')->textInput([
                'placeholder' => 'Ej: 1669',
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'bodega')->textInput([
                'placeholder' => 'Ej: 214',
            ]) ?>
        </div>
        <div class="col-md-9 d-flex align-items-end">
            <div class="form-group mb-3">
                <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Limpiar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                <?= Html::a(
                    'Ajustar Existencias',
                    array_merge(['index', 'ajustar' => 1], Yii::$app->request->get()),
                    ['class' => 'btn btn-warning']
                ) ?>
                <?php if (!empty($data)): ?>
                    <?= Html::a('Generar Documento', ['generar'], ['class' => 'btn btn-success']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php if (!empty($data)): ?>
    <?php 
        $totalCantidad = array_sum(array_column($data, 'cantidad')); 
        $totalCosto = array_sum(array_column($data, 'costo_total')); 
    ?>

    <!-- 🔹 Resumen arriba -->
    <div class="alert alert-info">
        <strong>Total Cantidad:</strong> <?= number_format($totalCantidad, 0) ?> |
        <strong>Total Costo:</strong> <?= number_format($totalCosto, 0) ?>
    </div>

    <table class="table table-bordered table-striped table-sm">
        <thead class="thead-dark">
            <tr>
                <th>Tipo Documento</th>
                <th>Consecutivo</th>
                <th>Bodega</th>
                <th>Fecha Documento</th>
                <th>ID Item</th>
                <th>Descripción</th>
                <th>Ext 1</th>
                <th>Ext 2</th>
                <th>Unidad</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Costo Unitario</th>
                <th class="text-right">Costo Total</th>
                <th>Notas Docto</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= $row['tipo_documento'] ?></td>
                    <td><?= $row['consecutivo'] ?></td>
                    <td><?= $row['bodega'] ?></td>
                    <td><?= $row['fecha_documento'] ?></td>
                    <td><?= $row['v121_id_item'] ?></td>
                    <td><?= $row['descripcion'] ?></td>
                    <td><?= $row['extension1'] ?></td>
                    <td><?= $row['extension2'] ?></td>
                    <td><?= $row['unidad'] ?></td>
                    <td class="text-right"><?= number_format($row['cantidad'], 0) ?></td>
                    <td class="text-right"><?= number_format($row['costo_unitario'], 0) ?></td>
                    <td class="text-right"><?= number_format($row['costo_total'], 0) ?></td>
                    <td><?= $row['notas_docto'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="font-weight-bold">
                <td colspan="9" class="text-right">TOTALES</td>
                <td class="text-right"><?= number_format($totalCantidad, 0) ?></td>
                <td></td>
                <td class="text-right"><?= number_format($totalCosto, 0) ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
<?php endif; ?>

<?php if (!empty($resultados)): ?>
    <h3>Resultado de Ajuste de Existencias</h3>
    <table class="table table-bordered table-sm">
        <thead class="thead-dark">
            <tr>
                <th>Item</th>
                <th>Bodega</th>
                <th>Estado</th>
                <th>Mensaje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultados as $r): ?>
                <tr class="<?php
                    if ($r['estado'] === 'SIN EXISTENCIA') echo 'table-danger';
                    elseif ($r['estado'] === 'AJUSTADO') echo 'table-warning';
                    else echo 'table-success';
                ?>">
                    <td><?= $r['item'] ?></td>
                    <td><?= $r['bodega'] ?></td>
                    <td><?= $r['estado'] ?></td>
                    <td><?= $r['mensaje'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
