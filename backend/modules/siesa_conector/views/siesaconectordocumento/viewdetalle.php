<?php
use yii\helpers\Html;

/** @var $documento app\models\SiesaConectorDocumento */
/** @var array $movimientosAgrupados */

$this->title = "Detalle de Conector: " . $documento->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Siesa Conector Documentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="siesa-conector-detalle">

    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="panel-body">
            <p><strong>Descripción:</strong> <?= Html::encode($documento->descripcion) ?></p>
            <p><strong>Traspaso relacionado:</strong> <?= Html::encode($documento->id_traspaso) ?></p>
        </div>
    </div>

    <div class="panel panel-info">
        <div class="panel-heading">
            <h4 class="panel-title">📄 Campos del Documento</h4>
        </div>
        <div class="panel-body table-responsive">
            <table class="table table-hover table-condensed table-bordered">
                <thead class="bg-info">
                    <tr>
                        <th>Nombre campo</th>
                        <th>Alias</th>
                        <th>Valor</th>
                        <th>Tipo de dato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documento->camposDocumento as $campo): ?>
                        <?php
                        $valor = '';
                        foreach ($documento->valoresDocumento as $v) {
                            if ($v->campo_id == $campo->id) {
                                $valor = $v->valor;
                                break;
                            }
                        }
                        ?>
                        <tr>
                            <td><?= Html::encode($campo->nombre_campo) ?></td>
                            <td><?= Html::encode($campo->alias) ?></td>
                            <td><?= Html::encode($valor) ?></td>
                            <td><?= Html::encode($campo->tipo_dato) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($movimientosAgrupados)): ?>
        <div class="panel panel-success">
            <div class="panel-heading">
                <h4 class="panel-title">📦 Detalles de Movimientos agrupados por <code>f470_id_item</code></h4>
            </div>
            <div class="panel-body">

                <?php foreach ($movimientosAgrupados as $itemId => $movimientos): ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Item: <?= Html::encode($itemId) ?></strong> (<?= count($movimientos) ?> movimientos)
                        </div>
                        <div class="panel-body table-responsive">
                            <table class="table table-bordered table-striped table-condensed">
                                <thead class="bg-success">
                                    <tr>
                                        <th>#</th>
                                        <?php foreach ($documento->camposMovimiento as $campo): ?>
                                            <th><?= Html::encode($campo->alias) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($movimientos as $i => $mov): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <?php foreach ($documento->camposMovimiento as $campo): ?>
                                                <?php
                                                $valor = '';
                                                foreach ($mov->valores as $v) {
                                                    if ($v->campo_id == $campo->id) {
                                                        $valor = $v->valor;
                                                        break;
                                                    }
                                                }
                                                ?>
                                                <td><?= Html::encode($valor) ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            No se encontraron movimientos relacionados con este documento.
        </div>
    <?php endif; ?>

</div>