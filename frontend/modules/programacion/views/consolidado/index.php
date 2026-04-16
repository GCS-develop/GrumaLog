<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/** @var yii\web\View $this */
/** @var yii\data\ArrayDataProvider $dataProvider */
/** @var array $totales */
/** @var array $listaBodegas */
/** @var array $listaZonas */
/** @var array $filtros */

$this->title = 'Consolidado de Inventario';
$this->params['breadcrumbs'][] = ['label' => 'Programación', 'url' => ['/programacion/facturaentregamercancia/index']];
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date('Y-m-d');

$this->registerCss('
    .consolidado-resumen {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
    }
    .bodega-card {
        background: #2e7d32;
        color: white;
        border-radius: 8px;
        padding: 8px 14px;
        min-width: 110px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    .bodega-card .bc-codigo {
        font-size: 15px;
        font-weight: bold;
        line-height: 1.1;
    }
    .bodega-card .bc-nombre {
        font-size: 10px;
        opacity: 0.85;
        margin-top: 1px;
    }
    .bodega-card .bc-und {
        font-size: 13px;
        font-weight: 600;
        margin-top: 4px;
    }
    .bodega-card .bc-items {
        font-size: 10px;
        opacity: 0.85;
    }
    .bodega-card.sin-zona {
        background: #5c6bc0;
    }
    .filtros-consolidado {
        background: #f1f8e9;
        border: 1px solid #c5e1a5;
        border-radius: 8px;
        padding: 12px 16px 8px;
        margin-bottom: 14px;
    }
    .filtros-consolidado .fc-label {
        font-size: 11px;
        font-weight: 700;
        color: #2e7d32;
        margin-bottom: 2px;
        display: block;
    }
    .badge-bodega {
        background: #2e7d32;
        color: white;
        font-weight: bold;
        font-size: 12px;
        padding: 2px 8px;
        border-radius: 4px;
        display: inline-block;
    }
    .badge-zona {
        background: #1565c0;
        color: white;
        font-weight: bold;
        font-size: 11px;
        padding: 2px 7px;
        border-radius: 4px;
        display: inline-block;
    }
');
?>

<div class="consolidado-index">

    <h4><?= Html::encode($this->title) ?></h4>
    <p class="text-muted" style="font-size:13px;">
        Items con existencia registrada por bodega. Use los filtros para consultar por ubicación o referencia.
    </p>

    <!-- ====== TARJETAS TOTALES POR BODEGA ====== -->
    <?php if (!empty($totales)): ?>
    <div class="consolidado-resumen">
        <?php foreach ($totales as $t): ?>
        <div class="bodega-card <?= empty($t['zona']) ? 'sin-zona' : '' ?>">
            <div class="bc-codigo"><?= Html::encode($t['codigoBodega']) ?></div>
            <div class="bc-nombre"><?= Html::encode(mb_substr($t['nombreBodega'], 0, 18)) ?></div>
            <?php if (!empty($t['zona'])): ?>
                <div class="bc-nombre">Zona: <?= Html::encode($t['zona']) ?></div>
            <?php endif; ?>
            <div class="bc-und"><?= number_format($t['totalUnidades']) ?> und</div>
            <div class="bc-items"><?= $t['totalItems'] ?> ítem(s)</div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ====== FILTROS ====== -->
    <form method="get" action="<?= Url::to(['/programacion/consolidado/index']) ?>" class="filtros-consolidado">
        <input type="hidden" name="r" value="programacion/consolidado/index">
        <div class="row align-items-end">

            <div class="col-md-2 col-sm-6">
                <span class="fc-label">Bodega</span>
                <select name="bodega" class="form-control form-control-sm">
                    <option value="">Todas</option>
                    <?php foreach ($listaBodegas as $b): ?>
                        <option value="<?= Html::encode($b['codigo']) ?>"
                            <?= $filtros['bodega'] === $b['codigo'] ? 'selected' : '' ?>>
                            <?= Html::encode($b['codigo'] . ' - ' . $b['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (!empty($listaZonas)): ?>
            <div class="col-md-1 col-sm-4">
                <span class="fc-label">Zona</span>
                <select name="zona" class="form-control form-control-sm">
                    <option value="">Todas</option>
                    <?php foreach ($listaZonas as $z): ?>
                        <option value="<?= Html::encode($z) ?>"
                            <?= $filtros['zona'] === $z ? 'selected' : '' ?>>
                            <?= Html::encode($z) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="col-md-2 col-sm-4">
                <span class="fc-label">Item</span>
                <input type="text" name="item" class="form-control form-control-sm"
                       placeholder="Ej: 342411"
                       value="<?= Html::encode($filtros['item']) ?>">
            </div>

            <div class="col-md-3 col-sm-6">
                <span class="fc-label">Descripción</span>
                <input type="text" name="descripcion" class="form-control form-control-sm"
                       placeholder="Buscar por descripción..."
                       value="<?= Html::encode($filtros['descripcion']) ?>">
            </div>

            <div class="col-md-1 col-sm-4">
                <button type="submit" class="btn btn-success btn-sm btn-block">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>

            <div class="col-md-1 col-sm-4">
                <?= Html::a('<i class="fas fa-times"></i> Limpiar', ['/programacion/consolidado/index'], [
                    'class' => 'btn btn-outline-secondary btn-sm btn-block'
                ]) ?>
            </div>

            <div class="col-md-1 col-sm-4 text-right">
                <?php echo ExportMenu::widget([
                    'dataProvider' => $dataProvider,
                    'columns'      => [
                        ['attribute' => 'zona',              'label' => 'Zona'],
                        ['attribute' => 'codigoBodega',      'label' => 'Bodega'],
                        ['attribute' => 'nombreBodega',      'label' => 'Nombre Bodega'],
                        ['attribute' => 'item',              'label' => 'Item'],
                        ['attribute' => 'descripcion',       'label' => 'Descripción'],
                        ['attribute' => 'color',             'label' => 'Color'],
                        ['attribute' => 'talla',             'label' => 'Talla'],
                        ['attribute' => 'codigoBarras',      'label' => 'EAN'],
                        ['attribute' => 'existencia',        'label' => 'Existencia'],
                        ['attribute' => 'fechaActualizacion','label' => 'Última Actualización'],
                    ],
                    'fontAwesome'     => true,
                    'filename'        => 'Consolidado_Inventario_' . $fecha_actual,
                    'dropdownOptions' => [
                        'label' => '<i class="fas fa-file-excel"></i>',
                        'class' => 'btn btn-success btn-sm',
                        'title' => 'Exportar a Excel',
                    ],
                    'exportConfig' => [
                        ExportMenu::FORMAT_TEXT    => false,
                        ExportMenu::FORMAT_HTML    => false,
                        ExportMenu::FORMAT_EXCEL   => false,
                        ExportMenu::FORMAT_PDF     => false,
                        ExportMenu::FORMAT_CSV     => false,
                        ExportMenu::FORMAT_EXCEL_X => [
                            'label'       => 'Excel 2007+',
                            'icon'        => 'file-excel-o',
                            'iconOptions' => ['class' => 'text-success'],
                            'extension'   => 'xlsx',
                            'writer'      => ExportMenu::FORMAT_EXCEL_X,
                        ],
                    ],
                ]); ?>
            </div>

        </div>
    </form>

    <!-- ====== GRID ====== -->
    <?= GridView::widget([
        'dataProvider'    => $dataProvider,
        'hover'           => true,
        'striped'         => true,
        'condensed'       => true,
        'pjax'            => false,
        'showPageSummary' => true,
        'columns'         => [
            ['class' => 'kartik\grid\SerialColumn'],

            [
                'attribute' => 'zona',
                'label'     => 'Zona',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'format'    => 'raw',
                'value'     => function ($m) {
                    if (empty($m['zona'])) return '<span class="text-muted">—</span>';
                    return '<span class="badge-zona">' . Html::encode($m['zona']) . '</span>';
                },
                'width'     => '60px',
            ],
            [
                'attribute' => 'codigoBodega',
                'label'     => 'Bodega',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'format'    => 'raw',
                'value'     => function ($m) {
                    return '<span class="badge-bodega">' . Html::encode($m['codigoBodega']) . '</span>';
                },
                'width'     => '75px',
            ],
            [
                'attribute' => 'nombreBodega',
                'label'     => 'Ubicación',
                'hAlign'    => 'left',
                'vAlign'    => 'middle',
                'width'     => '140px',
            ],
            [
                'attribute' => 'item',
                'label'     => 'Item',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'width'     => '80px',
            ],
            [
                'attribute' => 'descripcion',
                'label'     => 'Descripción',
                'hAlign'    => 'left',
                'vAlign'    => 'middle',
            ],
            [
                'attribute' => 'color',
                'label'     => 'Color',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'width'     => '75px',
            ],
            [
                'attribute' => 'talla',
                'label'     => 'Talla',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'width'     => '65px',
            ],
            [
                'attribute' => 'codigoBarras',
                'label'     => 'EAN',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'width'     => '110px',
            ],
            [
                'attribute'   => 'existencia',
                'label'       => 'Existencia',
                'hAlign'      => 'right',
                'vAlign'      => 'middle',
                'width'       => '90px',
                'pageSummary' => true,
                'format'      => ['decimal', 0],
            ],
            [
                'attribute' => 'fechaActualizacion',
                'label'     => 'Actualizado',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'width'     => '120px',
            ],
        ],
        'panel' => [
            'type'    => GridView::TYPE_SUCCESS,
            'heading' => '<i class="fas fa-boxes"></i>&nbsp; Consolidado — Items por Ubicación',
        ],
    ]); ?>

</div>
