<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\DetailView;
use yii\bootstrap4\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\Transferdevdocumentos $documento */
/** @var yii\data\ActiveDataProvider $detalles */

$this->title = 'Detalles del Documento #' . $documento->id_transferencia;
$this->params['breadcrumbs'][] = ['label' => 'Transferencias ERP', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
    .mi-gridview {
        font-size: 11px;
    }
    .btn-create { width: 300px; }
    .centrar { text-align: center; }
    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc;
        margin: 20px 0;
    }
    .table th, .table td { vertical-align: middle !important; }
    .card-header h5 { margin-bottom: 0; }
    .verde-texto { color: green !important; }
');
?>

<div class="transferencia-view">

    <!-- ✅ Mostrar alertas -->
    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <?= Alert::widget([
            'options' => ['class' => 'alert alert-' . $type],
            'body' => $message,
        ]) ?>
    <?php endforeach; ?>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Encabezado del Documento</h5>

            <?php if ($documento->estado_envio != 1): ?>
                <?= Html::a('<i class="fas fa-edit"></i> Editar Encabezado', ['update', 'id_transferencia' => $documento->id_transferencia], [
                    'class' => 'btn btn-success btn-sm',
                ]) ?>
            <?php endif; ?>
        </div>

        <!-- Encabezado -->
        <?= DetailView::widget([
            'model' => $documento,
            'attributes' => [
                'id_transferencia',
                'centro_operacion',
                'tipo_documento',
                'consecutivo_documento',
                'fecha_documento',
                'tercero_proveedor',
                'sucursal_proveedor',
                'comprador',
                'consignacion',
                'notas:ntext',
                [
                    'attribute' => 'respuesta_siesa',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if (empty($model->respuesta_siesa)) {
                            return Html::tag('span', 'Sin respuesta', ['class' => 'badge badge-secondary']);
                        }
                        $json = json_decode($model->respuesta_siesa, true);
                        if (is_array($json)) {
                            if (isset($json['codigo']) && $json['codigo'] == 0) {
                                return Html::tag('span', 'Enviado correctamente', ['class' => 'badge badge-success']);
                            }
                            $mensaje = $json['mensaje'] ?? 'Error desconocido';
                            $detalle = '';
                            if (isset($json['detalle']) && is_array($json['detalle'])) {
                                foreach ($json['detalle'] as $error) {
                                    $detalle .= isset($error['f_detalle']) ? $error['f_detalle'] . '<br>' : '';
                                }
                            }
                            $contenido = Html::tag('div', 'Error: ' . Html::encode($mensaje), ['class' => 'badge badge-danger']);
                            if (!empty($detalle)) {
                                $contenido .= Html::tag('div', "<strong>Detalle:</strong><br>$detalle", ['class' => 'mt-2 text-danger']);
                            }
                            return $contenido;
                        }
                        return Html::tag('span', Html::encode($model->respuesta_siesa), ['class' => 'badge badge-danger']);
                    },
                    'label' => 'Respuesta Siesa',
                ],
            ],
        ]) ?>

        <hr class="horizontal-line">

        <div class="centrar">
            <?php if ($documento->estado_envio != 1): ?>
                <?= Html::button('<i class="fas fa-paper-plane"></i> Ejecutar Transferencia', [
                    'class' => 'btn btn-success btn-ejecutar-transferencia-dev',
                    'data-url' => \yii\helpers\Url::to(['ejecutar-transferencia', 'id' => $documento->id_transferencia]),
                    'data-desc' => 'Documento #' . $documento->id_transferencia,
                ]) ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <strong>Nota:</strong> Esta transferencia ya fue enviada a Siesa.
                </div>
            <?php endif; ?>

            <?= Html::a('Salir', Yii::$app->request->referrer, [
                'class' => 'btn btn-success',
            ]) ?>
        </div>

        <hr class="horizontal-line">

        <!-- Detalles -->
        <h4>Detalles del Documento</h4>

        <?= GridView::widget([
            'dataProvider' => $detalles,
            'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
            'options' => ['class' => 'mi-gridview'],
            'showPageSummary' => true,
            'columns' => [
                [
                    'label' => 'CENTRO DE OPERACION',
                    'value' => fn() => $documento->centro_operacion,
                ],
                [
                    'label' => 'TIPO DE DOCUMENTO',
                    'value' => fn() => $documento->tipo_documento,
                ],
                [
                    'label' => 'BODEGA',
                    'value' => fn() => '214',
                ],
                [
                    'label' => 'MOTIVO',
                    'value' => fn() => $documento->motivo,
                ],
                [
                    'attribute' => 'unidadMedida',
                    'label' => 'UNIDAD DE MEDIDA',
                    'value' => fn() => 'UND',
                ],
                [
    'attribute' => 'cantidadRegistrada',
    'label' => 'CANTIDAD BASE',
    'format' => ['decimal', 0],
    'value' => fn($m) => $m->cantidadBase,
    'pageSummary' => true,
],
[
    'label' => 'EAN',
    'value' => fn($m) => $m->codigoBarras ? trim($m->codigoBarras) : '(sin EAN)',
],
[
    'label' => 'VALOR BRUTO',
    'hAlign' => 'right',
    'format' => ['decimal', 2], // usa formatter directo
    'value' => fn($m) => $m->valorBruto,
    'pageSummary' => true,
],
                [
                    'attribute' => 'item',
                    'label' => 'ITEM',
                ],
                [
                    'attribute' => 'color',
                    'label' => 'COLOR',
                ],
                [
                    'label' => 'TALLA',
                    'value' => fn($m) => $m->talla ?? 'NA',
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => fn($url, $m) => Html::a(
                            '<i class="fas fa-trash-alt"></i>',
                            ['transferdevdetalle/delete', 'id' => $m->id, 'id_transferencia' => $m->id_transferencia],
                            [
                                'class' => 'btn btn-sm btn-danger',
                                'title' => 'Eliminar',
                                'data-confirm' => '¿Eliminar este detalle?',
                                'data-method' => 'post',
                            ]
                        ),
                    ],
                ],
            ],
        ]) ?>
    </div>
</div>

<!-- Modal confirmación ejecutar transferencia -->
<div class="modal fade" id="modalConfirmTransferDev" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-paper-plane"></i> Confirmar Transferencia</h5>
            </div>
            <div id="transferdev-confirm-panel" class="modal-body">
                <p>¿Está seguro de enviar esta transferencia a Siesa?</p>
                <p class="text-muted mb-0" style="font-size:12px" id="transferdev-desc-text"></p>
            </div>
            <div id="transferdev-spinner-panel" class="modal-body text-center d-none">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-3 mb-0">Enviando a SIESA, por favor espere...</p>
            </div>
            <div class="modal-footer" id="transferdev-modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmTransferDev">
                    <i class="fas fa-paper-plane"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
var _transferDevUrl = '';

$(document).on('click', '.btn-ejecutar-transferencia-dev', function () {
    _transferDevUrl = $(this).data('url');
    var desc = $(this).data('desc');
    $('#transferdev-desc-text').text(desc);
    $('#transferdev-confirm-panel').removeClass('d-none');
    $('#transferdev-spinner-panel').addClass('d-none');
    $('#transferdev-modal-footer').removeClass('d-none');
    $('#modalConfirmTransferDev').modal('show');
});

$('#btnConfirmTransferDev').on('click', function () {
    $('#transferdev-confirm-panel').addClass('d-none');
    $('#transferdev-spinner-panel').removeClass('d-none');
    $('#transferdev-modal-footer').addClass('d-none');
    window.location.href = _transferDevUrl;
});
JS;
$this->registerJs($js);
?>
