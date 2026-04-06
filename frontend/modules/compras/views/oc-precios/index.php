<?php
/* @var $this yii\web\View */
/* @var $datos array */
/* @var $filtros array */
/* @var $hayFiltro bool */
/* @var $tiposDocto array */
/* @var $estados array */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\data\ArrayDataProvider;
use yii\widgets\LinkPager;

$this->title = 'Generar OC';

$this->registerCss('
    .oc-sort-link { color: inherit; text-decoration: none; display: block; }
    .oc-sort-link:hover { color: #fff; text-decoration: underline; }
    .oc-sort-link .sort-icon { font-size: 10px; margin-left: 3px; opacity: 0.6; }
    .oc-sort-link.asc  .sort-icon::after { content: " ▲"; }
    .oc-sort-link.desc .sort-icon::after { content: " ▼"; }
    .oc-sort-link:not(.asc):not(.desc) .sort-icon::after { content: " ⇅"; }
    .pagination { margin: 6px 0; }
');
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <h1 class="m-0">
                    <i class="fas fa-file-invoice-dollar text-primary mr-2"></i>
                    Generar OC
                </h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<!-- ===== FORMULARIO DE FILTROS ===== -->
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter mr-1"></i> Filtros de Búsqueda
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>

    <div class="card-body">
        <?= Html::beginForm(['/compras/oc-precios/index'], 'get', ['id' => 'form-filtros']) ?>

        <?php
        $nrosUnicos        = array_unique(array_column($datos, 'nro_orden'));
        $proveedoresUnicos = array_unique(array_column($datos, 'razon_social_proveedor'));
        $pdfValido         = count($nrosUnicos) === 1 && count($proveedoresUnicos) === 1;

        $mensajeInvalido = '';
        if (count($nrosUnicos) > 1 && count($proveedoresUnicos) > 1) {
            $mensajeInvalido = 'La consulta contiene ' . count($nrosUnicos) . ' órdenes de compra y '
                . count($proveedoresUnicos) . ' proveedores distintos. '
                . 'El PDF debe generarse para una única orden de compra de un único proveedor. '
                . 'Filtra por Nro. Orden y Tipo Docto. para continuar.';
        } elseif (count($nrosUnicos) > 1) {
            $mensajeInvalido = 'La consulta contiene ' . count($nrosUnicos) . ' órdenes de compra distintas ('
                . implode(', ', array_slice($nrosUnicos, 0, 5)) . '...). '
                . 'El PDF debe generarse para una única orden. '
                . 'Filtra por Nro. Orden para continuar.';
        } elseif (count($proveedoresUnicos) > 1) {
            $mensajeInvalido = 'La consulta contiene ' . count($proveedoresUnicos) . ' proveedores distintos. '
                . 'El PDF debe generarse para un único proveedor. '
                . 'Filtra por Nro. Orden para continuar.';
        }
        ?>

        <div class="row">
            <!-- Nro Orden -->
            <div class="col-md-2">
                <div class="form-group">
                    <label>Nro. Orden</label>
                    <?= Html::input('text', 'nro_orden', $filtros['nro_orden'], [
                        'class'       => 'form-control form-control-sm',
                        'placeholder' => 'Ej: 6229',
                    ]) ?>
                </div>
            </div>

            <!-- Tipo Docto -->
            <div class="col-md-2">
                <div class="form-group">
                    <label>Tipo Docto.</label>
                    <?= Html::dropDownList('tipo_docto', $filtros['tipo_docto'], $tiposDocto, [
                        'class' => 'form-control form-control-sm',
                    ]) ?>
                </div>
            </div>

            <!-- Fecha Orden Desde -->
            <div class="col-md-2">
                <div class="form-group">
                    <label>Fecha Orden Desde</label>
                    <?= Html::input('date', 'fecha_ord_ini', $filtros['fecha_ord_ini'], [
                        'class' => 'form-control form-control-sm',
                    ]) ?>
                </div>
            </div>

            <!-- Fecha Orden Hasta -->
            <div class="col-md-2">
                <div class="form-group">
                    <label>Fecha Orden Hasta</label>
                    <?= Html::input('date', 'fecha_ord_fin', $filtros['fecha_ord_fin'], [
                        'class' => 'form-control form-control-sm',
                    ]) ?>
                </div>
            </div>

            <!-- Proveedor (NIT o Nombre) -->
            <div class="col-md-4">
                <div class="form-group">
                    <label>Proveedor (NIT o Nombre)</label>
                    <?= Html::input('text', 'proveedor', $filtros['proveedor'] ?? null, [
                        'class' => 'form-control form-control-sm',
                        'placeholder' => 'Buscar por NIT o nombre...',
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?= Html::submitButton('<i class="fas fa-search mr-1"></i> Buscar', [
                    'class' => 'btn btn-primary btn-sm mr-2',
                ]) ?>
                <?= Html::a('<i class="fas fa-times mr-1"></i> Limpiar', ['/compras/oc-precios/index'], [
                    'class' => 'btn btn-secondary btn-sm mr-2',
                ]) ?>
                <?php if ($hayFiltro && count($datos) > 0): ?>
                    <?= Html::a(
                        '<i class="fas fa-file-excel mr-1"></i> Exportar Excel (' . count($datos) . ' registros)',
                        [
                            '/compras/oc-precios/exportar',
                            'nro_orden'     => $filtros['nro_orden'],
                            'tipo_docto'    => $filtros['tipo_docto'],
                            'fecha_ord_ini' => $filtros['fecha_ord_ini'],
                            'fecha_ord_fin' => $filtros['fecha_ord_fin'],
                            'proveedor'     => $filtros['proveedor'] ?? null,
                        ],
                        ['class' => 'btn btn-success btn-sm mr-2']
                    ) ?>
                    <?php if ($pdfValido): ?>
                        <?= Html::a('<i class="fas fa-file-pdf mr-1"></i> Generar OC PDF',
                            [
                                '/compras/oc-precios/pdf',
                                'nro_orden'  => $nrosUnicos[0],
                                'tipo_docto' => $filtros['tipo_docto'],
                            ],
                            ['class' => 'btn btn-danger btn-sm', 'target' => '_blank']
                        ) ?>
                    <?php else: ?>
                        <button type="button" class="btn btn-danger btn-sm" id="btn-pdf-invalido">
                            <i class="fas fa-file-pdf mr-1"></i> Generar OC PDF
                        </button>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!$pdfValido && !empty($mensajeInvalido)): ?>
                    <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var btn = document.getElementById('btn-pdf-invalido');
                        if (btn) {
                            btn.addEventListener('click', function () {
                                var msg = <?= json_encode($mensajeInvalido) ?>;
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({ icon: 'warning', title: 'Acción no válida', text: msg,
                                        confirmButtonColor: '#1F4E79', confirmButtonText: 'Entendido' });
                                } else {
                                    alert('Acción no válida\n\n' + msg);
                                }
                            });
                        }
                    });
                    </script>
                <?php endif; ?>
            </div>
        </div>

        <?= Html::endForm() ?>
    </div>
</div>

<!-- ===== RESULTADOS ===== -->
<?php if ($hayFiltro): ?>
<?php
// --- Totales sobre TODOS los datos ---
$totalUnidades = array_sum(array_column($datos, 'cant_ordenada'));
$totalNeto     = 0;
foreach ($datos as $d) {
    $totalNeto += floatval($d['vlr_neto'] ?? 0);
}

// --- Paginación y orden via ArrayDataProvider ---
$perPage = 50;

$provider = new ArrayDataProvider([
    'allModels' => $datos,
    'sort' => [
        'attributes' => [
            'nro_orden', 'tipo_docto', 'razon_social_proveedor',
            'item', 'referencia', 'color', 'talla', 'codigo_barras',
            'desc_item', 'cant_ordenada', 'fecha_entrega', 'precio_vigente',
        ],
        'defaultOrder' => ['nro_orden' => SORT_ASC],
    ],
    'pagination' => [
        'pageSize' => $perPage,
    ],
]);

$sort       = $provider->sort;
$paginacion = $provider->pagination;
$filasPagina = $provider->getModels();

// URL base con todos los filtros activos + per_page para los links de paginación/sort
$baseParams = array_filter(array_merge(Yii::$app->request->queryParams, ['per_page' => $perPage]), fn($v) => $v !== null && $v !== '');
?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap" style="gap:6px;">
            <div class="card-title d-flex align-items-center flex-wrap" style="gap:4px; margin-bottom:0;">
                <i class="fas fa-table mr-1"></i>
                Resultados
                <?php if (count($datos) > 0): ?>
                    <span class="badge badge-primary ml-1"><?= number_format(count($datos), 0, ',', '.') ?> registros</span>
                    <span class="badge badge-info ml-1"><?= count($nrosUnicos) ?> Órdenes</span>
                    <span class="badge badge-secondary ml-1"><?= count($proveedoresUnicos) ?> Proveedores</span>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                <?php if (count($datos) > 0): ?>
                <span style="font-size:12px; white-space:nowrap;">
                    <strong>Cantidad Total:</strong> <?= number_format($totalUnidades, 0, ',', '.') ?> Uds.
                    &nbsp;|&nbsp;
                    <strong>Neto Total:</strong> $<?= number_format($totalNeto, 0, ',', '.') ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-body p-0">

        <?php if (empty($datos)): ?>
            <div class="alert alert-warning m-3">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                No se encontraron órdenes de compra con los filtros aplicados.
            </div>
        <?php else: ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm mb-0" style="font-size:12px;">
                <thead class="thead-dark">
                    <tr>
                        <th><?= $sort->link('nro_orden',            ['label' => 'Nro Orden',      'class' => 'oc-sort-link']) ?></th>
                        <th><?= $sort->link('tipo_docto',           ['label' => 'Tipo',            'class' => 'oc-sort-link']) ?></th>
                        <th><?= $sort->link('razon_social_proveedor', ['label' => 'Proveedor',     'class' => 'oc-sort-link']) ?></th>
                        <th><?= $sort->link('item',                 ['label' => 'Ítem',            'class' => 'oc-sort-link']) ?></th>
                        <th><?= $sort->link('referencia',           ['label' => 'Referencia',      'class' => 'oc-sort-link']) ?></th>
                        <th><?= $sort->link('color',                ['label' => 'Color',           'class' => 'oc-sort-link']) ?></th>
                        <th><?= $sort->link('talla',                ['label' => 'Talla',           'class' => 'oc-sort-link']) ?></th>
                        <th><?= $sort->link('codigo_barras',        ['label' => 'Cód. Barras',     'class' => 'oc-sort-link']) ?></th>
                        <th><?= $sort->link('desc_item',            ['label' => 'Descripción',     'class' => 'oc-sort-link']) ?></th>
                        <th class="text-right"><?= $sort->link('cant_ordenada', ['label' => 'Cant. Ord.', 'class' => 'oc-sort-link text-right']) ?></th>
                        <th><?= $sort->link('fecha_entrega',        ['label' => 'Fec. Entrega',    'class' => 'oc-sort-link']) ?></th>
                        <th class="text-right text-primary"><?= $sort->link('precio_vigente', ['label' => 'Precio Vigente', 'class' => 'oc-sort-link text-primary text-right']) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filasPagina as $d): ?>
                    <tr>
                        <td><strong><?= Html::encode($d['nro_orden']) ?></strong></td>
                        <td><?= Html::encode($d['tipo_docto']) ?></td>
                        <td><?= Html::encode($d['razon_social_proveedor'] ?? '') ?></td>
                        <td><?= Html::encode($d['item']) ?></td>
                        <td><?= Html::encode($d['referencia'] ?? '') ?></td>
                        <td><?= Html::encode($d['color'] ?? '') ?></td>
                        <td><?= Html::encode($d['talla'] ?? '') ?></td>
                        <td><?= Html::encode($d['codigo_barras'] ?? '') ?></td>
                        <td><?= Html::encode($d['desc_item']) ?></td>
                        <td class="text-right"><?= number_format($d['cant_ordenada'], 0) ?></td>
                        <td><?= Html::encode($d['fecha_entrega'] ?? '') ?></td>
                        <td class="text-right font-weight-bold text-primary">
                            <?= $d['precio_vigente'] !== null
                                ? number_format($d['precio_vigente'], 0, ',', '.')
                                : '<span class="text-muted">Sin precio</span>' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="d-flex justify-content-between align-items-center px-3 py-2">
            <small class="text-muted">
                Mostrando <?= $paginacion->getOffset() + 1 ?>–<?= min($paginacion->getOffset() + $perPage, count($datos)) ?>
                de <?= number_format(count($datos), 0, ',', '.') ?> registros
            </small>
            <?= LinkPager::widget([
                'pagination'        => $paginacion,
                'options'           => ['class' => 'pagination pagination-sm mb-0'],
                'linkOptions'       => ['class' => 'page-link'],
                'linkContainerOptions' => ['class' => 'page-item'],
                'activePageCssClass'   => 'active',
                'disabledPageCssClass' => 'disabled',
                'firstPageLabel'    => '«',
                'lastPageLabel'     => '»',
                'prevPageLabel'     => '‹',
                'nextPageLabel'     => '›',
                'maxButtonCount'    => 8,
            ]) ?>
        </div>

        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

</div>
</section>
