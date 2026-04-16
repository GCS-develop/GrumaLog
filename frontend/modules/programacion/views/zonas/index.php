<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/** @var yii\web\View $this */
/** @var yii\data\ArrayDataProvider $dataProvider */
/** @var array $listaZonas */
/** @var array $totales   — [ ['zona'=>'A','totalUnidades'=>120,'totalItems'=>5], ... ] */
/** @var array $filtros */

$this->title = 'Zonas de Conteo';
$this->params['breadcrumbs'][] = ['label' => 'Programación', 'url' => ['/programacion/facturaentregamercancia/index']];
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date('Y-m-d');

$this->registerCss('
    /* ---- Cards de totales por zona ---- */
    .zonas-resumen {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
    }
    .zona-card {
        background: #3a6bc9;
        color: white;
        border-radius: 8px;
        padding: 8px 14px;
        min-width: 80px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    .zona-card .zc-codigo {
        font-size: 22px;
        font-weight: bold;
        line-height: 1;
    }
    .zona-card .zc-und {
        font-size: 13px;
        font-weight: 600;
        margin-top: 2px;
    }
    .zona-card .zc-items {
        font-size: 10px;
        opacity: 0.85;
    }
    /* ---- Filtros ---- */
    .filtros-zona {
        background: #f0f4ff;
        border: 1px solid #c8d6f5;
        border-radius: 8px;
        padding: 12px 16px 8px;
        margin-bottom: 14px;
    }
    .filtros-zona .fz-label {
        font-size: 11px;
        font-weight: 700;
        color: #3a6bc9;
        margin-bottom: 2px;
        display: block;
    }
    .filtros-zona .form-control-sm {
        font-size: 13px;
    }
    /* ---- Tabla ---- */
    .badge-zona {
        background: #3a6bc9;
        color: white;
        font-weight: bold;
        font-size: 13px;
        padding: 3px 10px;
        border-radius: 4px;
        display: inline-block;
    }
');
?>

<div class="zonas-index">

    <h4><?= Html::encode($this->title) ?></h4>

    <!-- ====== TARJETAS DE TOTALES POR ZONA ====== -->
    <?php if (!empty($totales)): ?>
    <div class="zonas-resumen">
        <?php foreach ($totales as $t): ?>
        <div class="zona-card">
            <div class="zc-codigo"><?= Html::encode($t['zona']) ?></div>
            <div class="zc-und"><?= number_format($t['totalUnidades']) ?> und</div>
            <div class="zc-items"><?= $t['totalItems'] ?> ítem(s)</div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ====== FILTROS ====== -->
    <form method="get" action="<?= Url::to(['/programacion/zonas/index']) ?>" class="filtros-zona">
        <input type="hidden" name="r" value="programacion/zonas/index">
        <div class="row align-items-end">

            <div class="col-md-1 col-sm-4">
                <span class="fz-label">Zona</span>
                <select name="zona" class="form-control form-control-sm">
                    <option value="">Todas</option>
                    <?php foreach ($listaZonas as $cod): ?>
                        <option value="<?= Html::encode($cod) ?>" <?= $filtros['zona'] === $cod ? 'selected' : '' ?>>
                            <?= Html::encode($cod) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 col-sm-4">
                <span class="fz-label">Item</span>
                <input type="text" name="item" class="form-control form-control-sm"
                       placeholder="Ej: 342411"
                       value="<?= Html::encode($filtros['item']) ?>">
            </div>

            <div class="col-md-2 col-sm-4">
                <span class="fz-label">Orden de Compra</span>
                <input type="text" name="numeroOrden" class="form-control form-control-sm"
                       placeholder="Ej: OC-12345"
                       value="<?= Html::encode($filtros['numeroOrden']) ?>">
            </div>

            <div class="col-md-2 col-sm-6">
                <span class="fz-label">Fecha desde</span>
                <input type="date" name="fechaDesde" class="form-control form-control-sm"
                       value="<?= Html::encode($filtros['fechaDesde']) ?>">
            </div>

            <div class="col-md-2 col-sm-6">
                <span class="fz-label">Fecha hasta</span>
                <input type="date" name="fechaHasta" class="form-control form-control-sm"
                       value="<?= Html::encode($filtros['fechaHasta']) ?>">
            </div>

            <div class="col-md-1 col-sm-6">
                <button type="submit" class="btn btn-primary btn-sm btn-block">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>

            <div class="col-md-1 col-sm-6">
                <?= Html::a('<i class="fas fa-times"></i> Limpiar', ['/programacion/zonas/index'], [
                    'class' => 'btn btn-outline-secondary btn-sm btn-block'
                ]) ?>
            </div>

            <div class="col-md-1 col-sm-12 text-right">
                <?php echo ExportMenu::widget([
                    'dataProvider' => $dataProvider,
                    'columns'      => [
                        ['attribute' => 'zona',            'label' => 'Zona'],
                        ['attribute' => 'numeroOrden',     'label' => 'OC'],
                        ['attribute' => 'item',            'label' => 'Item'],
                        ['attribute' => 'descripcion',     'label' => 'Descripción'],
                        ['attribute' => 'color',           'label' => 'Color'],
                        ['attribute' => 'talla',           'label' => 'Talla'],
                        ['attribute' => 'codigoBarras',    'label' => 'EAN'],
                        ['attribute' => 'unidades',        'label' => 'Unidades'],
                        ['attribute' => 'empleado',        'label' => 'Empleado'],
                        ['attribute' => 'fechaAsignacion', 'label' => 'Fecha'],
                    ],
                    'fontAwesome'     => true,
                    'filename'        => 'Zonas_Conteo_' . $fecha_actual,
                    'dropdownOptions' => [
                        'label' => '<i class="fas fa-file-excel"></i>',
                        'class' => 'btn btn-success btn-sm',
                        'title' => 'Exportar a Excel',
                    ],
                    'exportConfig'    => [
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
                    return '<span class="badge-zona">' . Html::encode($m['zona']) . '</span>';
                },
                'width'     => '65px',
            ],
            [
                'attribute' => 'numeroOrden',
                'label'     => 'OC',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'width'     => '110px',
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
                'attribute'   => 'unidades',
                'label'       => 'Unidades',
                'hAlign'      => 'right',
                'vAlign'      => 'middle',
                'width'       => '80px',
                'pageSummary' => true,
                'format'      => ['decimal', 0],
            ],
            [
                'attribute' => 'empleado',
                'label'     => 'Empleado',
                'hAlign'    => 'left',
                'vAlign'    => 'middle',
            ],
            [
                'attribute' => 'fechaAsignacion',
                'label'     => 'Último Scan',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'width'     => '120px',
            ],
        ],
        'panel' => [
            'type'    => GridView::TYPE_DEFAULT,
            'heading' => '<i class="fas fa-map-marker-alt"></i>&nbsp; Asignaciones de Zona',
        ],
    ]); ?>

</div>
