<?php

use yii\helpers\Html;

$this->registerCss('
    .btn-create { width: 200px; }
    .centrar    { text-align: center; }
    .horizontal-line { border: none; border-top: 1px solid #ccc; margin: 10px 0; }
    .titulonombre { color: black; font-weight: bold; font-size: 20px; }
');
?>

<div class="d-flex justify-content-center mt-4">
    <div class="card shadow-sm p-4" style="max-width: 500px; width: 100%;">
        <h5 class="mb-3 text-primary text-center">Consulta por Rango de Fechas</h5>

        <div class="mb-3 text-center small text-muted">
            <strong>Proveedor:</strong> <?= Html::encode($razonSocial) ?><br>
            <strong>Código:</strong> <?= Html::encode($codigo) ?>
        </div>

        <form method="POST" action="">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

            <div class="mb-3">
                <label class="form-label">Fecha Desde</label>
                <input type="date" name="fechaDesde" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" name="fechaHasta" class="form-control" required>
            </div>

            <div class="d-grid gap-2 centrar">
                <?= Html::submitButton('Consultar Ventas', ['class' => 'btn btn-success btn-lg btn-create']) ?>
                <?= Html::a('Regresar', ['ventas-diario'], ['class' => 'btn btn-secondary btn-lg btn-create']) ?>
            </div>
        </form>
    </div>
</div>
