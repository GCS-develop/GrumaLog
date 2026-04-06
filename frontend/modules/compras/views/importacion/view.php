<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\icons\Icon;

Icon::map($this, Icon::FAS);

/** @var yii\web\View $this */
/** @var frontend\models\Comprasimportacion $model */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Detalle Importación #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Compras - Importar', 'url' => ['/compras/importacion/index']];
$this->params['breadcrumbs'][] = $this->title;

// Total costo de la importación
$totalCosto = (float) Yii::$app->db->createCommand(
    "SELECT SUM(uds * costo) FROM comprasimportaciondetalle WHERE idImportacion = :id"
)->bindValue(':id', $model->id, \PDO::PARAM_INT)->queryScalar();

// Historial de envíos a SIESA (para el modal de ver)
$logs = Yii::$app->db->createCommand(
    "SELECT id, fechaEnvio, tipoDoc, consecutivo, estadoEnvio, mensaje,
            jsonEnviado, jsonRespuesta, idUsuario
     FROM comprasimportacion_siesa_log
     WHERE idImportacion = :id
     ORDER BY fechaEnvio DESC"
)->bindValue(':id', $model->id, \PDO::PARAM_INT)->queryAll();

// ---- helpers de estado -----------------------------------------------
$estadoBadge = function ($estado, $mensaje, $tipoDoc = null, $numDoc = null) {
    if ($estado === null) {
        return '<span class="badge badge-secondary">Sin enviar</span>';
    }
    if ($estado == 1) {
        $docBadge = ($tipoDoc && $numDoc)
            ? ' &nbsp;<span class="badge badge-light border" title="Documento creado en SIESA">'
              . '<i class="fas fa-file-invoice mr-1 text-success"></i>'
              . Html::encode($tipoDoc . '-' . $numDoc) . '</span>'
            : '';
        return '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Exitoso</span>'
             . $docBadge
             . ($mensaje ? '<br><small class="text-success">' . Html::encode($mensaje) . '</small>' : '');
    }
    return '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Error</span>'
         . ($mensaje ? '<br><small class="text-danger">' . Html::encode($mensaje) . '</small>' : '');
};
?>

<?php $this->registerCss('
    .mi-gridview        { font-size: 11px; }
    .card-header-custom { background: #343a40; color: #fff; padding: 10px 16px; border-radius: 4px 4px 0 0; }
    .total-badge        { font-size: 1.1rem; font-weight: 700; }
    .btn-envio          { min-width: 160px; }
    #resp-siesa, #resp-carvajal { margin-top: 8px; }
    .json-box { background:#1e1e1e; color:#d4d4d4; font-size:11px; border-radius:4px;
                padding:12px; max-height:400px; overflow-y:auto; white-space:pre-wrap; word-break:break-all; }
    .doc-siesa-badge { font-size:1.4rem; font-weight:700; letter-spacing:1px; }
'); ?>

<?php $this->registerJs("
var urlSiesa = '" . Url::to(['/compras/importacion/envio-siesa', 'id' => $model->id]) . "';

// Botón SIESA abre el modal con el formulario
$('#btn-envio-siesa').on('click', function(){
    $('#modalSiesa').modal('show');
});
"); ?>

<!-- ===== ENCABEZADO ===== -->
<div class="card mb-3">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-file-excel mr-2"></i>
            Importación <strong>#<?= $model->id ?></strong>
            &nbsp;|&nbsp;
            <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>Volver', ['/compras/importacion/index'],
                ['class' => 'btn btn-sm btn-outline-light']) ?>
        </span>
        <span class="total-badge">
            <i class="fas fa-boxes mr-1"></i>
            Total Unidades: <?= number_format($model->totalUnidades, 0, '.', ',') ?>
            &nbsp;&nbsp;
            <i class="fas fa-dollar-sign mr-1"></i>
            Total Costo: $<?= number_format($totalCosto, 0, '.', ',') ?>
        </span>
    </div>

    <div class="card-body">
        <div class="row align-items-start">

            <!-- Info carga -->
            <div class="col-md-4">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width:130px">Fecha carga:</th>
                        <td><?= substr($model->created_at, 0, 16) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Usuario:</th>
                        <td><?= Html::encode($model->usuariocrea->username ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Registros:</th>
                        <td><?= number_format($model->numeroRegistros, 0) ?></td>
                    </tr>
                </table>
            </div>

            <!-- Botón SIESA -->
            <div class="col-md-4 text-center">
                <?= Html::button(
                    '<i class="fas fa-paper-plane mr-1"></i>Envío a SIESA',
                    [
                        'id'       => 'btn-envio-siesa',
                        'class'    => 'btn btn-warning btn-envio',
                        'disabled' => $model->estadoSiesa == 1,
                    ]
                ) ?>
                <div id="resp-siesa" class="mt-1">
                    <?php if ($model->estadoSiesa !== null): ?>
                        <?= $estadoBadge($model->estadoSiesa, $model->mensajeSiesa, $model->tipoDocSiesa, $model->numDocSiesa) ?>
                    <?php endif; ?>
                </div>
                <?php if ($model->jsonEnviadoSiesa || !empty($logs)): ?>
                <div class="mt-2">
                    <?= Html::button(
                        '<i class="fas fa-history mr-1"></i>Ver envíos SIESA'
                            . (count($logs) > 0 ? ' <span class="badge badge-dark ml-1">' . count($logs) . '</span>' : ''),
                        [
                            'class'       => 'btn btn-sm btn-outline-secondary',
                            'data-toggle' => 'modal',
                            'data-target' => '#modalVerSiesa',
                        ]
                    ) ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Botón Carvajal -->
            <div class="col-md-4 text-center">
                <?php
                $urlCarvajal = Url::to(['/compras/importacion/generar-txt-carvajal', 'id' => $model->id]);
                $puedeGenerar = ($model->estadoSiesa == 1 && !empty($model->tipoDocSiesa) && !empty($model->numDocSiesa));
                ?>
                <?php if ($puedeGenerar): ?>
                    <?= Html::a(
                        '<i class="fas fa-file-download mr-1"></i>Generar TXT Carvajal',
                        $urlCarvajal,
                        [
                            'id'    => 'btn-envio-carvajal',
                            'class' => 'btn btn-info btn-envio',
                            'title' => 'Descarga el archivo EDI para enviar a Carvajal',
                        ]
                    ) ?>
                <?php else: ?>
                    <?= Html::button(
                        '<i class="fas fa-file-download mr-1"></i>Generar TXT Carvajal',
                        [
                            'id'       => 'btn-envio-carvajal',
                            'class'    => 'btn btn-info btn-envio',
                            'disabled' => true,
                            'title'    => 'Primero envíe a SIESA para obtener el consecutivo',
                        ]
                    ) ?>
                    <?php if ($model->estadoSiesa != 1): ?>
                    <div class="mt-1"><small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Requiere envío a SIESA</small></div>
                    <?php endif; ?>
                <?php endif; ?>
                <div id="resp-carvajal" class="mt-1">
                    <?php if ($model->estadoCarvajal !== null): ?>
                        <?= $estadoBadge($model->estadoCarvajal, $model->mensajeCarvajal) ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '_modal_siesa.php'; ?>
<?php include '_modal_ver_siesa.php'; ?>

<!-- ===== TABLA DETALLE ===== -->
<div class="card">
    <div class="card-body p-1">
        <?= GridView::widget([
            'dataProvider'   => $dataProvider,
            'summary'        => 'Mostrando {begin} - {end} de {totalCount} registros',
            'showPageSummary'=> true,
            'formatter'      => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
            'options'        => ['class' => 'mi-gridview'],
            'columns'        => [

                ['class' => 'kartik\grid\SerialColumn'],

                [
                    'attribute'     => 'cc',
                    'hAlign'        => 'center',
                    'vAlign'        => 'middle',
                    'headerOptions' => ['style' => 'width:50px'],
                ],
                [
                    'attribute' => 'codigo',
                    'label'     => 'Código',
                    'hAlign'    => 'center',
                    'vAlign'    => 'middle',
                ],
                [
                    'attribute' => 'talla',
                    'label'     => 'Talla',
                    'hAlign'    => 'center',
                    'vAlign'    => 'middle',
                ],
                [
                    'attribute' => 'color',
                    'label'     => 'Color',
                    'hAlign'    => 'center',
                    'vAlign'    => 'middle',
                ],
                [
                    'attribute' => 'producto',
                    'label'     => 'Producto',
                    'vAlign'    => 'middle',
                ],
                [
                    'attribute'       => 'uds',
                    'label'           => 'UDS.',
                    'hAlign'          => 'right',
                    'vAlign'          => 'middle',
                    'format'          => ['decimal', 0],
                    'pageSummary'     => true,
                    'pageSummaryFunc' => \kartik\grid\GridView::F_SUM,
                ],
                [
                    'attribute' => 'mes',
                    'label'     => 'MES',
                    'hAlign'    => 'center',
                    'vAlign'    => 'middle',
                ],
                [
                    'attribute' => 'tienda',
                    'label'     => 'Tienda',
                    'vAlign'    => 'middle',
                ],
                [
                    'attribute'       => 'costo',
                    'label'           => 'Costo',
                    'hAlign'          => 'right',
                    'vAlign'          => 'middle',
                    'format'          => ['decimal', 0],
                    'pageSummary'     => true,
                    'pageSummaryFunc' => \kartik\grid\GridView::F_SUM,
                ],
            ],
        ]); ?>
    </div>
</div>
