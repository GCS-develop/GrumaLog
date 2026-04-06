<?php
/** @var $this yii\web\View */
/** @var $searchModel \frontend\models\search\ComparativoDashboardSearch */
/** @var $comparativo array  (nivel proveedor) */
/** @var $proveedores array */

use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Comparativo OC → Conteo → Traspasos → Tienda';

$jsUrl           = Url::to(['/traspaso/comparativo-dashboard/detalle']);
$jsUrlOcsProv    = Url::to(['/traspaso/comparativo-dashboard/ocs-proveedor']);
$jsUrlItemsOc    = Url::to(['/traspaso/comparativo-dashboard/items-oc']);
$jsUrlOcs        = Url::to(['/traspaso/comparativo-dashboard/ocs']);
$jsUrlCoc        = Url::to(['/traspaso/comparativo-dashboard/conteo-oc']);
?>

<div class="container-fluid py-3">

    <!-- ===== FILTROS ===== -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body py-3">
            <?php $form = ActiveForm::begin(['method' => 'get', 'id' => 'form-filtros']); ?>

            <div class="row align-items-end g-2">

                <div class="col-12 col-md-2">
                    <?= $form->field($searchModel, 'desde')->widget(DatePicker::class, [
                        'language'      => 'es',
                        'options'       => [
                            'placeholder' => 'Desde...',
                            'value'       => $searchModel->desde ?? date('Y-m-01'),
                        ],
                        'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd'],
                    ])->label('Fecha Desde') ?>
                </div>

                <div class="col-12 col-md-2">
                    <?= $form->field($searchModel, 'hasta')->widget(DatePicker::class, [
                        'language'      => 'es',
                        'options'       => [
                            'placeholder' => 'Hasta...',
                            'value'       => $searchModel->hasta ?? date('Y-m-d'),
                        ],
                        'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd'],
                    ])->label('Fecha Hasta') ?>
                </div>

                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'tercero')->widget(Select2::class, [
                        'data'          => $proveedores,
                        'options'       => ['placeholder' => 'Todos los proveedores...'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('Proveedor') ?>
                </div>

                <div class="col-12 col-md-2">
                    <?= $form->field($searchModel, 'item')->textInput(['placeholder' => 'Código SKU SIESA...'])->label('Item (SKU)') ?>
                </div>

                <div class="col-12 col-md-1 d-flex align-items-end">
                    <?= Html::submitButton('<i class="fas fa-search"></i> Buscar', [
                        'class' => 'btn btn-primary w-100 mb-3',
                    ]) ?>
                </div>

            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- ===== RESUMEN KPI ===== -->
    <?php
    $totEntrada   = array_sum(array_column($comparativo, 'cant_entrada'));
    $totConteo    = array_sum(array_column($comparativo, 'cant_conteo'));
    $totTraspasos = array_sum(array_column($comparativo, 'cant_traspasos'));
    $totTienda    = array_sum(array_column($comparativo, 'cant_tienda'));
    $totProvs     = count($comparativo);
    ?>
    <div class="row mb-3 g-2">
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-info"><?= number_format($totProvs) ?></div>
                <div class="text-muted small">Proveedores</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-primary"><?= number_format($totEntrada) ?></div>
                <div class="text-muted small">Entrada OC</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-success"><?= number_format($totConteo) ?></div>
                <div class="text-muted small">Conteo Logística</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-warning"><?= number_format($totTraspasos) ?></div>
                <div class="text-muted small">Traspasos (salida)</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold" style="color:#8b5cf6"><?= number_format($totTienda) ?></div>
                <div class="text-muted small">Recibido Tienda</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <?php $pendiente = $totTraspasos - $totTienda; ?>
            <div class="card border-0 shadow-sm text-center py-3 <?= $pendiente > 0 ? 'bg-warning bg-opacity-10' : '' ?>">
                <div class="fs-2 fw-bold <?= $pendiente > 0 ? 'text-danger' : 'text-success' ?>">
                    <?= number_format($pendiente) ?>
                </div>
                <div class="text-muted small">Pendiente tienda</div>
            </div>
        </div>
    </div>

    <!-- ===== TABLA POR PROVEEDOR ===== -->
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-table me-1"></i> Resumen por Proveedor</span>
            <span class="badge bg-secondary"><?= count($comparativo) ?> proveedores</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover table-striped mb-0" id="tbl-comparativo">
                    <thead class="table-dark">
                        <tr>
                            <th rowspan="2" class="align-middle">Proveedor</th>
                            <th class="text-end bg-primary bg-opacity-25">Entrada OC</th>
                            <th class="text-end bg-success bg-opacity-25">Conteo Log.</th>
                            <th class="text-end bg-secondary bg-opacity-25">Dif.</th>
                            <th class="text-end bg-warning bg-opacity-25">Traspasos</th>
                            <th class="text-end bg-secondary bg-opacity-25">Dif.</th>
                            <th class="text-end" style="background:rgba(139,92,246,.15)">Recib. Tienda</th>
                            <th class="text-end bg-secondary bg-opacity-25">Dif.</th>
                            <th rowspan="2" class="align-middle text-center">Ver OCs</th>
                        </tr>
                        <tr class="small" style="color:#ccc">
                            <th class="text-center fw-normal bg-primary bg-opacity-10">SIESA (todas las OCs)</th>
                            <th class="text-center fw-normal bg-success bg-opacity-10">Legalizados</th>
                            <th class="text-center fw-normal bg-secondary bg-opacity-10">C-E</th>
                            <th class="text-center fw-normal bg-warning bg-opacity-10">Bodega→Tienda</th>
                            <th class="text-center fw-normal bg-secondary bg-opacity-10">T-C</th>
                            <th class="text-center fw-normal" style="background:rgba(139,92,246,.08)">Tienda confirma</th>
                            <th class="text-center fw-normal bg-secondary bg-opacity-10">Ti-T</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($comparativo)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No hay datos para el período seleccionado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($comparativo as $row): ?>
                                <?php
                                $difCE  = (int)$row['dif_conteo_entrada'];
                                $difTC  = (int)$row['dif_traspasos_conteo'];
                                $difTiT = (int)$row['dif_tienda_traspaso'];
                                $clsCE  = $difCE  < 0 ? 'text-danger fw-bold' : ($difCE  > 0 ? 'fw-bold' : 'text-success');
                                $clsTC  = $difTC  < 0 ? 'text-danger fw-bold' : ($difTC  > 0 ? 'fw-bold' : 'text-success');
                                $clsTiT = $difTiT < 0 ? 'text-danger fw-bold' : ($difTiT > 0 ? 'fw-bold' : 'text-success');
                                $styleCE  = $difCE  > 0 ? 'color:#e67e00' : '';
                                $styleTC  = $difTC  > 0 ? 'color:#e67e00' : '';
                                $styleTiT = $difTiT > 0 ? 'color:#e67e00' : '';
                                ?>
                                <tr>
                                    <td><?= Html::encode($row['proveedor']) ?></td>
                                    <td class="text-end"><?= number_format($row['cant_entrada']) ?></td>
                                    <td class="text-end"><?= number_format($row['cant_conteo']) ?></td>
                                    <td class="text-end <?= $clsCE ?>" style="<?= $styleCE ?>">
                                        <?= ($difCE > 0 ? '+' : '') . number_format($difCE) ?>
                                    </td>
                                    <td class="text-end"><?= number_format($row['cant_traspasos']) ?></td>
                                    <td class="text-end <?= $clsTC ?>" style="<?= $styleTC ?>">
                                        <?= ($difTC > 0 ? '+' : '') . number_format($difTC) ?>
                                    </td>
                                    <td class="text-end"><?= number_format($row['cant_tienda']) ?></td>
                                    <td class="text-end <?= $clsTiT ?>" style="<?= $styleTiT ?>">
                                        <?= ($difTiT > 0 ? '+' : '') . number_format($difTiT) ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-xs btn-outline-dark btn-ocs-prov"
                                                data-tercero="<?= Html::encode($row['tercero']) ?>"
                                                data-prov="<?= Html::encode($row['proveedor']) ?>"
                                                title="Ver órdenes de compra del proveedor">
                                            <i class="fas fa-clipboard-list"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($comparativo)): ?>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td>TOTALES</td>
                            <td class="text-end"><?= number_format($totEntrada) ?></td>
                            <td class="text-end"><?= number_format($totConteo) ?></td>
                            <?php $dCE = $totConteo - $totEntrada; ?>
                            <td class="text-end <?= $dCE < 0 ? 'text-danger' : ($dCE > 0 ? 'text-warning' : 'text-success') ?>">
                                <?= ($dCE > 0 ? '+' : '') . number_format($dCE) ?>
                            </td>
                            <td class="text-end"><?= number_format($totTraspasos) ?></td>
                            <?php $dTC = $totTraspasos - $totConteo; ?>
                            <td class="text-end <?= $dTC < 0 ? 'text-danger' : ($dTC > 0 ? 'text-warning' : 'text-success') ?>">
                                <?= ($dTC > 0 ? '+' : '') . number_format($dTC) ?>
                            </td>
                            <td class="text-end"><?= number_format($totTienda) ?></td>
                            <?php $dTiT = $totTienda - $totTraspasos; ?>
                            <td class="text-end <?= $dTiT < 0 ? 'text-danger' : ($dTiT > 0 ? 'text-warning' : 'text-success') ?>">
                                <?= ($dTiT > 0 ? '+' : '') . number_format($dTiT) ?>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

</div><!-- /container-fluid -->

<!-- ===== MODAL OCs DEL PROVEEDOR (nivel 1) ===== -->
<div class="modal fade" id="modal-ocs-prov" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="fas fa-clipboard-list me-1"></i>
                    Órdenes de Compra — <span id="modal-ocs-prov-title"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="alert alert-info rounded-0 mb-0 py-2 px-3 small">
                    OCs vinculadas a facturas SIESA del proveedor en el período.
                    <strong>Pedido OC</strong>: suma de cantidadPedida en los detalles de la OC.
                    <strong>Conteo Log.</strong>: unidades contadas en programaciones <em>legalizadas</em> de esa OC.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>OC GRUMALog</th>
                                <th>Estado</th>
                                <th class="text-center">Facturas</th>
                                <th class="text-end bg-primary bg-opacity-25">Pedido OC</th>
                                <th class="text-end bg-success bg-opacity-25">Conteo Log.</th>
                                <th class="text-end">Diferencia</th>
                                <th class="text-center">Ver Items</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-ocs-prov-body">
                            <tr><td colspan="7" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold" id="tbl-ocs-prov-foot" style="display:none">
                                <td colspan="3">TOTAL</td>
                                <td class="text-end" id="ocs-prov-tot-pedido">0</td>
                                <td class="text-end" id="ocs-prov-tot-conteo">0</td>
                                <td class="text-end" id="ocs-prov-tot-dif">0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL ITEMS DE LA OC (nivel 2) ===== -->
<div class="modal fade" id="modal-items-oc" tabindex="-1" aria-hidden="true" style="z-index:1060">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-boxes me-1"></i>
                    Items — <span id="modal-items-oc-title"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="alert alert-warning rounded-0 mb-0 py-2 px-3 small">
                    <strong>Pedido OC</strong>: cantidades pedidas en los detalles de la OC GRUMALog.
                    <strong>Conteo Log.</strong>: unidades contadas y <em>legalizadas</em> en programación para esta OC.
                    Diferencia negativa = se contó menos de lo pedido.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Item (SKU)</th>
                                <th>Talla</th>
                                <th>Color</th>
                                <th>Descripción</th>
                                <th class="text-end bg-primary bg-opacity-25">Pedido OC</th>
                                <th class="text-end bg-success bg-opacity-25">Conteo Log.</th>
                                <th class="text-end">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-items-oc-body">
                            <tr><td colspan="7" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold" id="tbl-items-oc-foot" style="display:none">
                                <td colspan="4">TOTAL</td>
                                <td class="text-end" id="items-oc-tot-pedido">0</td>
                                <td class="text-end" id="items-oc-tot-conteo">0</td>
                                <td class="text-end" id="items-oc-tot-dif">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL DETALLE TRASPASOS + TIENDA ===== -->
<div class="modal fade" id="modal-detalle" tabindex="-1" aria-hidden="true" style="z-index:1070">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="fas fa-route me-1"></i>
                    Trazabilidad del item: <span id="modal-ref"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0" id="tbl-detalle">
                        <thead class="table-dark">
                            <tr>
                                <th rowspan="2" class="align-middle">Traspaso #</th>
                                <th rowspan="2" class="align-middle">Doc. SIESA</th>
                                <th rowspan="2" class="align-middle">Origen</th>
                                <th rowspan="2" class="align-middle">Destino</th>
                                <th rowspan="2" class="align-middle text-center">Código</th>
                                <th colspan="3" class="text-center bg-warning bg-opacity-25">Bodega (salida)</th>
                                <th colspan="3" class="text-center" style="background:rgba(139,92,246,.15)">Tienda (recepción)</th>
                                <th rowspan="2" class="align-middle text-end">Dif.</th>
                            </tr>
                            <tr class="small">
                                <th class="text-end bg-warning bg-opacity-10">Cant.</th>
                                <th class="bg-warning bg-opacity-10">Usuario</th>
                                <th class="bg-warning bg-opacity-10">Fecha</th>
                                <th class="text-end" style="background:rgba(139,92,246,.08)">Cant.</th>
                                <th style="background:rgba(139,92,246,.08)">Usuario</th>
                                <th style="background:rgba(139,92,246,.08)">Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-detalle-body">
                            <tr><td colspan="12" class="text-center text-muted py-3">Selecciona un item para ver su trazabilidad.</td></tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td colspan="5">TOTAL</td>
                                <td class="text-end" id="detalle-total-bodega">-</td>
                                <td colspan="2"></td>
                                <td class="text-end" id="detalle-total-tienda">-</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL FACTURAS / OCs (drill-down adicional) ===== -->
<div class="modal fade" id="modal-ocs" tabindex="-1" aria-hidden="true" style="z-index:1080">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice me-1"></i>
                    Facturas SIESA — item <span id="modal-ocs-ref"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="alert alert-info rounded-0 mb-0 py-2 px-3 small">
                    Facturas SIESA que tienen conteos asociados. <strong>Conteo Log.</strong>: unidades contadas en programaciones legalizadas de esa factura.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Factura</th>
                                <th>OC GRUMALog</th>
                                <th>Estado</th>
                                <th class="text-end bg-primary bg-opacity-25">Entrada SIESA</th>
                                <th class="text-end bg-success bg-opacity-25">Conteo Log.</th>
                                <th class="text-end">Diferencia</th>
                                <th class="text-center">Ver conteos</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-ocs-body">
                            <tr><td colspan="7" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-success btn-coc-all" id="btn-ver-todos-conteos"
                        title="Ver todos los conteos legalizados del item sin filtrar por factura">
                    <i class="fas fa-clipboard-list me-1"></i> Ver todos los conteos del item
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL CONTEOS ===== -->
<div class="modal fade" id="modal-conteo-oc" tabindex="-1" aria-hidden="true" style="z-index:1090">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-clipboard-check me-1"></i>
                    Conteos legalizados — <span id="modal-coc-numero"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="alert alert-warning rounded-0 mb-0 py-2 px-3 small">
                    Solo conteos de agendas <strong>legalizadas</strong>. Desglose por SKU (talla/color) y OC GRUMALog.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Item (SKU)</th>
                                <th>Talla</th>
                                <th>Color</th>
                                <th>OC GRUMALog</th>
                                <th class="text-end">Paquetes</th>
                                <th class="text-end">Equiv.</th>
                                <th class="text-end bg-success bg-opacity-25">Unidades</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-coc-body">
                            <tr><td colspan="7" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td colspan="6">TOTAL CONTADO</td>
                                <td class="text-end" id="coc-total">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
$currentDesde = $searchModel->desde ?? date('Y-m-01');
$currentHasta = $searchModel->hasta ?? date('Y-m-d');
$this->registerJs(<<<JS
$(function () {

    // ========================================================
    // NIVEL 1: OCs del proveedor
    // ========================================================
    $(document).on('click', '.btn-ocs-prov', function () {
        var tercero = $(this).data('tercero');
        var prov    = $(this).data('prov');

        $('#modal-ocs-prov-title').text(prov);
        $('#modal-ocs-prov').data('tercero', tercero);
        $('#tbl-ocs-prov-body').html('<tr><td colspan="7" class="text-center py-3"><div class="spinner-border spinner-border-sm text-dark"></div></td></tr>');
        $('#tbl-ocs-prov-foot').hide();
        $('#modal-ocs-prov').modal('show');

        var sep = ('$jsUrlOcsProv'.indexOf('?') >= 0) ? '&' : '?';
        var url = '$jsUrlOcsProv' + sep + 'tercero=' + encodeURIComponent(tercero)
                + '&ComparativoDashboardSearch[desde]=' + encodeURIComponent('$currentDesde')
                + '&ComparativoDashboardSearch[hasta]=' + encodeURIComponent('$currentHasta');

        $.getJSON(url, function (data) {
            if (!data || data.length === 0) {
                $('#tbl-ocs-prov-body').html('<tr><td colspan="7" class="text-center text-muted py-3">Sin órdenes de compra en este período.</td></tr>');
                return;
            }

            var html = '';
            var totPed = 0, totCont = 0;

            $.each(data, function (i, row) {
                var ped  = parseInt(row.cant_pedida) || 0;
                var cont = parseInt(row.cant_conteo) || 0;
                var dif  = cont - ped;
                totPed  += ped;
                totCont += cont;

                var legalizado  = parseInt(row.idEstadoLegalizacion) === 2;
                var estadoBadge = legalizado
                    ? '<span class="badge bg-success">'  + (row.estado_legalizacion || 'Legalizado') + '</span>'
                    : '<span class="badge bg-warning text-dark">' + (row.estado_legalizacion || 'Pendiente') + '</span>';

                var difClass = dif < 0 ? 'text-danger fw-bold' : (dif > 0 ? 'text-warning fw-bold' : 'text-success');
                var difStr   = (dif > 0 ? '+' : '') + dif.toLocaleString();
                var contStr  = cont > 0 ? cont.toLocaleString()
                             : '<span class="text-muted">0</span>';

                html += '<tr>'
                    + '<td><strong>' + (row.oc_gruma || '—') + '</strong></td>'
                    + '<td>' + estadoBadge + '</td>'
                    + '<td class="text-center">'
                    +   '<span class="badge bg-secondary">' + (row.num_facturas || 0) + ' fact.</span>'
                    + '</td>'
                    + '<td class="text-end fw-bold">' + ped.toLocaleString() + '</td>'
                    + '<td class="text-end">' + contStr + '</td>'
                    + '<td class="text-end ' + difClass + '">' + difStr + '</td>'
                    + '<td class="text-center">'
                    + (parseInt(row.idOc) > 0
                        ? '<button type="button" class="btn btn-xs btn-outline-primary btn-items-oc"'
                          +   ' data-oc-id="' + row.idOc + '"'
                          +   ' data-oc-gruma="' + (row.oc_gruma || '') + '"'
                          +   ' title="Ver items de esta OC"><i class="fas fa-boxes"></i></button>'
                        : '<span class="text-muted small" title="Sin agenda GRUMALog vinculada"><i class="fas fa-ban"></i></span>')
                    + '</td>'
                    + '</tr>';
            });

            $('#tbl-ocs-prov-body').html(html);

            var totDif = totCont - totPed;
            var colCls = function(n) { return n < 0 ? 'text-danger' : (n > 0 ? 'text-warning' : 'text-success'); };
            $('#ocs-prov-tot-pedido').text(totPed.toLocaleString());
            $('#ocs-prov-tot-conteo').text(totCont.toLocaleString());
            $('#ocs-prov-tot-dif').text((totDif > 0 ? '+' : '') + totDif.toLocaleString())
                                  .removeClass().addClass('text-end ' + colCls(totDif));
            $('#tbl-ocs-prov-foot').show();

        }).fail(function () {
            $('#tbl-ocs-prov-body').html('<tr><td colspan="7" class="text-danger text-center py-3">Error al cargar los datos.</td></tr>');
        });
    });

    // ========================================================
    // NIVEL 2: Items de la OC (Pedido OC vs Conteo)
    // ========================================================
    $(document).on('click', '.btn-items-oc', function () {
        var idOc    = $(this).data('oc-id');
        var ocGruma = $(this).data('oc-gruma');

        $('#modal-items-oc-title').text(ocGruma);
        $('#tbl-items-oc-body').html('<tr><td colspan="7" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>');
        $('#tbl-items-oc-foot').hide();
        $('#modal-items-oc').modal('show');

        var sep = ('$jsUrlItemsOc'.indexOf('?') >= 0) ? '&' : '?';
        var url = '$jsUrlItemsOc' + sep + 'idOc=' + idOc;

        $.getJSON(url, function (data) {
            if (!data || data.length === 0) {
                $('#tbl-items-oc-body').html('<tr><td colspan="7" class="text-center text-muted py-3">Sin items en esta OC.</td></tr>');
                return;
            }

            var html = '';
            var totPed = 0, totCont = 0;

            $.each(data, function (i, row) {
                var ped  = parseInt(row.cant_pedida)  || 0;
                var cont = parseInt(row.cant_conteo)  || 0;
                var dif  = parseInt(row.diferencia)   || 0;
                totPed  += ped;
                totCont += cont;

                var difClass = dif < 0 ? 'text-danger fw-bold' : (dif > 0 ? 'text-warning fw-bold' : 'text-success');
                var difStr   = (dif > 0 ? '+' : '') + dif.toLocaleString();

                html += '<tr>'
                    + '<td class="text-nowrap"><strong>' + (row.item_codigo || '—') + '</strong></td>'
                    + '<td class="small">' + (row.talla || '—') + '</td>'
                    + '<td class="small">' + (row.color || '—') + '</td>'
                    + '<td class="small">' + (row.descripcion || '—') + '</td>'
                    + '<td class="text-end fw-bold">' + ped.toLocaleString() + '</td>'
                    + '<td class="text-end">' + (cont > 0 ? cont.toLocaleString() : '<span class="text-muted">0</span>') + '</td>'
                    + '<td class="text-end ' + difClass + '">' + difStr + '</td>'
                    + '</tr>';
            });

            $('#tbl-items-oc-body').html(html);

            var totDif = totCont - totPed;
            var colCls = function(n) { return n < 0 ? 'text-danger' : (n > 0 ? 'text-warning' : 'text-success'); };
            $('#items-oc-tot-pedido').text(totPed.toLocaleString());
            $('#items-oc-tot-conteo').text(totCont.toLocaleString());
            $('#items-oc-tot-dif').text((totDif > 0 ? '+' : '') + totDif.toLocaleString())
                                  .removeClass().addClass('text-end ' + colCls(totDif));
            $('#tbl-items-oc-foot').show();

        }).fail(function () {
            $('#tbl-items-oc-body').html('<tr><td colspan="7" class="text-danger text-center py-3">Error al cargar los datos.</td></tr>');
        });
    });

    // ========================================================
    // MODAL DETALLE TRASPASOS (conservado para drill-down extra)
    // ========================================================
    $(document).on('click', '.btn-detalle', function () {
        var idItem = $(this).data('id');
        var ref    = $(this).data('ref');

        $('#modal-ref').text(ref);
        $('#tbl-detalle-body').html('<tr><td colspan="12" class="text-center py-3"><div class="spinner-border spinner-border-sm text-info"></div></td></tr>');
        $('#detalle-total-bodega').text('-');
        $('#detalle-total-tienda').text('-');
        $('#modal-detalle').modal('show');

        var sep = ('$jsUrl'.indexOf('?') >= 0) ? '&' : '?';
        var url = '$jsUrl' + sep + 'idItem=' + idItem
                           + '&ComparativoDashboardSearch[desde]=' + encodeURIComponent('$currentDesde')
                           + '&ComparativoDashboardSearch[hasta]=' + encodeURIComponent('$currentHasta');

        $.getJSON(url, function (data) {
            if (!data || data.length === 0) {
                $('#tbl-detalle-body').html('<tr><td colspan="12" class="text-center text-muted py-3">Sin traspasos en este período.</td></tr>');
                $('#detalle-total-bodega').text('0');
                $('#detalle-total-tienda').text('0');
                return;
            }
            var html = '';
            var totalBodega = 0;
            var totalTienda = 0;
            $.each(data, function (i, row) {
                var cantBodega  = parseInt(row.cantidad_bodega)   || 0;
                var cantTienda  = parseInt(row.cantidad_tienda)   || 0;
                var dif         = parseInt(row.dif_tienda_bodega) || 0;
                var difClass    = dif < 0 ? 'text-danger fw-bold' : (dif > 0 ? 'text-warning fw-bold' : 'text-success');
                var tiendaStr   = row.cantidad_tienda !== null ? cantTienda.toLocaleString() : '<span class="text-muted">—</span>';
                totalBodega += cantBodega;
                totalTienda += cantTienda;
                html += '<tr>'
                    + '<td>' + row.idTraspaso + '</td>'
                    + '<td>' + (row.consecutivo_siesa || '<span class="text-muted">—</span>') + '</td>'
                    + '<td class="small">' + (row.bodega_origen  || '-') + '</td>'
                    + '<td class="small">' + (row.bodega_destino || '-') + '</td>'
                    + '<td class="text-center"><span class="badge bg-secondary">' + (row.codigo_destino || '-') + '</span></td>'
                    + '<td class="text-end fw-bold">' + cantBodega.toLocaleString() + '</td>'
                    + '<td>' + (row.usuario_bodega || '-') + '</td>'
                    + '<td class="text-muted small">' + (row.fecha_bodega || '-') + '</td>'
                    + '<td class="text-end fw-bold">' + tiendaStr + '</td>'
                    + '<td>' + (row.usuario_tienda  || '<span class="text-muted">—</span>') + '</td>'
                    + '<td class="text-muted small">' + (row.fecha_tienda || '<span class="text-muted">—</span>') + '</td>'
                    + '<td class="text-end ' + difClass + '">' + (dif > 0 ? '+' : '') + dif + '</td>'
                    + '</tr>';
            });
            $('#tbl-detalle-body').html(html);
            $('#detalle-total-bodega').text(totalBodega.toLocaleString());
            $('#detalle-total-tienda').text(totalTienda.toLocaleString());
        }).fail(function () {
            $('#tbl-detalle-body').html('<tr><td colspan="12" class="text-danger text-center py-3">Error al cargar los datos.</td></tr>');
        });
    });

    // ========================================================
    // MODAL FACTURAS (drill-down extra desde otros contextos)
    // ========================================================
    $(document).on('click', '.btn-ocs', function () {
        var idItem = $(this).data('id');
        var ref    = $(this).data('ref');
        $('#modal-ocs-ref').text(ref);
        $('#modal-ocs').data('current-item-id', idItem).data('current-ref', ref);
        $('#tbl-ocs-body').html('<tr><td colspan="7" class="text-center py-3"><div class="spinner-border spinner-border-sm text-secondary"></div></td></tr>');
        $('#modal-ocs').modal('show');

        var sep = ('$jsUrlOcs'.indexOf('?') >= 0) ? '&' : '?';
        var url = '$jsUrlOcs' + sep + 'idItem=' + idItem
                + '&ComparativoDashboardSearch[desde]=' + encodeURIComponent('$currentDesde')
                + '&ComparativoDashboardSearch[hasta]=' + encodeURIComponent('$currentHasta');

        $.getJSON(url, function (data) {
            if (!data || data.length === 0) {
                $('#tbl-ocs-body').html('<tr><td colspan="7" class="text-center text-muted py-3">Sin facturas en este período.</td></tr>');
                return;
            }
            var html = '';
            $.each(data, function (i, row) {
                var ent  = parseInt(row.cant_entrada) || 0;
                var cont = parseInt(row.cant_conteo)  || 0;
                var dif  = parseInt(row.diferencia)   || 0;
                var sinRegistro = !row.idFactura || row.idFactura == 0;
                var legalizado  = parseInt(row.idEstadoLegalizacion) === 2;
                var estadoBadge = sinRegistro
                    ? '<span class="badge bg-secondary">Sin registro GRUMALog</span>'
                    : (legalizado
                        ? '<span class="badge bg-success">' + row.estado_legalizacion + '</span>'
                        : '<span class="badge bg-warning text-dark">' + (row.estado_legalizacion || '—') + '</span>');
                var botonConteo = sinRegistro
                    ? '<button class="btn btn-xs btn-outline-secondary" disabled><i class="fas fa-ban"></i></button>'
                    : '<button class="btn btn-xs btn-outline-success btn-coc"'
                    +   ' data-factura-id="' + row.idFactura + '" data-fac-num="' + row.numero_factura + '"'
                    +   ' data-item-id="' + idItem + '" data-ref="' + ref + '">'
                    +   '<i class="fas fa-search"></i></button>';
                var difClass = dif < 0 ? 'text-danger fw-bold' : (dif > 0 ? 'text-warning fw-bold' : 'text-success');
                html += '<tr' + (sinRegistro ? ' class="table-warning"' : '') + '>'
                    + '<td><strong>' + row.numero_factura + '</strong></td>'
                    + '<td class="small">' + (row.oc_gruma || '—') + '</td>'
                    + '<td>' + estadoBadge + '</td>'
                    + '<td class="text-end fw-bold">' + ent.toLocaleString() + '</td>'
                    + '<td class="text-end">' + (cont > 0 ? cont.toLocaleString() : '<span class="text-muted">0</span>') + '</td>'
                    + '<td class="text-end ' + difClass + '">' + (dif > 0 ? '+' : '') + dif.toLocaleString() + '</td>'
                    + '<td class="text-center">' + botonConteo + '</td>'
                    + '</tr>';
            });
            $('#tbl-ocs-body').html(html);
        }).fail(function () {
            $('#tbl-ocs-body').html('<tr><td colspan="7" class="text-danger text-center py-3">Error al cargar los datos.</td></tr>');
        });
    });

    $(document).on('click', '.btn-coc-all', function () {
        var idItem = $('#modal-ocs').data('current-item-id');
        var ref    = $('#modal-ocs').data('current-ref');
        abrirModalConteo(0, ref + ' (todos los conteos)', idItem, ref);
    });

    $(document).on('click', '.btn-coc', function () {
        var idFactura = $(this).data('factura-id');
        var facNum    = $(this).data('fac-num');
        var idItem    = $(this).data('item-id');
        var ref       = $(this).data('ref');
        abrirModalConteo(idFactura, facNum || ref, idItem, ref);
    });

    function abrirModalConteo(idFactura, titulo, idItem, ref) {
        $('#modal-coc-numero').text(titulo);
        $('#tbl-coc-body').html('<tr><td colspan="7" class="text-center py-3"><div class="spinner-border spinner-border-sm text-success"></div></td></tr>');
        $('#coc-total').text('-');
        $('#modal-conteo-oc').modal('show');

        var sep = ('$jsUrlCoc'.indexOf('?') >= 0) ? '&' : '?';
        var url = '$jsUrlCoc' + sep + 'idFactura=' + idFactura + '&idItem=' + idItem;

        $.getJSON(url, function (resp) {
            var detalle    = (resp && resp.detalle) ? resp.detalle : [];
            var cantPedida = parseInt((resp && resp.cant_pedida) || 0);
            var html = '';
            var total = 0;
            if (idFactura > 0) {
                html += '<tr class="table-primary fw-bold">'
                    + '<td colspan="6" class="text-primary">Pedido en OC GRUMALog</td>'
                    + '<td class="text-end text-primary">' + cantPedida.toLocaleString() + '</td>'
                    + '</tr>';
            }
            if (detalle.length === 0) {
                html += '<tr><td colspan="7" class="text-center text-muted py-2">Sin conteos legalizados registrados.</td></tr>';
            } else {
                $.each(detalle, function (i, row) {
                    var uni = parseFloat(row.cant_unidades) || 0;
                    total  += uni;
                    html += '<tr>'
                        + '<td class="text-nowrap"><strong>' + (row.item_codigo || '—') + '</strong></td>'
                        + '<td class="small">' + (row.talla || '—') + '</td>'
                        + '<td class="small">' + (row.color || '—') + '</td>'
                        + '<td class="small"><span class="badge bg-secondary">' + (row.oc_gruma || '—') + '</span></td>'
                        + '<td class="text-end">' + (parseInt(row.cant_paquetes) || 0).toLocaleString() + '</td>'
                        + '<td class="text-end text-muted small">× ' + (parseFloat(row.equivalencia) || 1) + '</td>'
                        + '<td class="text-end fw-bold">' + uni.toLocaleString() + '</td>'
                        + '</tr>';
                });
            }
            $('#tbl-coc-body').html(html);
            $('#coc-total').text(total.toLocaleString());
        }).fail(function () {
            $('#tbl-coc-body').html('<tr><td colspan="7" class="text-danger text-center py-3">Error al cargar los datos.</td></tr>');
        });
    }

});
JS
);
?>
