<?php

use frontend\models\Inventario;
use frontend\models\Item;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/** @var yii\web\View $this */
/** @var frontend\models\search\InventarioSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Inventarios';
$this->params['breadcrumbs'][] = $this->title;
$fecha_actual = date("Y-m-d");
$filename = "Relacion_Inventario_" . $fecha_actual;



$gridColumns = [
    'id',
    'item',
    'codigoBarras',
    'color',
    'talla',
    'codigoBodega',
    [
        'label' => 'Existencia grumalog',
        'attribute' => 'existencia',
        'value' => function ($model) {
            return $model->existencia;
        },
        'format' => ['decimal', 0], // Formato decimal con 0 decimales
        'pageSummary' => true,
    ],
    [
        'label' => 'unidades inventario Siesa',
        'value' => function ($model) {
            return Item::getInventario($model->codigoBarras, $model->codigoBodega);
        },
        'format' => ['decimal', 0], // Formato decimal con 0 decimales
        'pageSummary' => true,
    ],
];

?>

<link rel="stylesheet" href="css/shared.css">

<div class="inventario-index">

    <div class="row">

        <?php
        $totalCantidadSiesa = (int) Inventario::getTotalExistenciasSiesa($searchModel->codigoBodega);
        $totalCantidadGruma = (int) Inventario::getotalExistenciasGruma($searchModel->codigoBodega);
        ?>
        <div class="col-lg-12 centrar">

            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $dataProvider,
                    'columns' => $gridColumns,
                    'fontAwesome' => true,
                    'filename' => $filename,
                    'dropdownOptions' => [
                        'label' => 'Exportar',
                        'class' => 'btn btn-primary btn-lg btn-create ',
                        // btn disabled',
                    ],
                    'exportConfig' => [
                        ExportMenu::FORMAT_TEXT => false,
                        ExportMenu::FORMAT_HTML => false,
                        ExportMenu::FORMAT_EXCEL => false,
                        ExportMenu::FORMAT_PDF => false,
                        ExportMenu::FORMAT_CSV => false,
                        ExportMenu::FORMAT_EXCEL_X => [
                            'label' => 'Excel 2007+',
                            'icon' => 'file-excel-o',
                            'iconOptions' => ['class' => 'text-success btn-create'],
                            'linkOptions' => [],
                            'options' => ['title' => 'Microsoft Excel 2007+ (xlsx)'],
                            'alertMsg' => 'Se va a generar un archivo en formato EXCEL 2007+ (xlsx).',
                            'mime' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'extension' => 'xlsx',
                            'writer' => ExportMenu::FORMAT_EXCEL_X
                        ],
                    ]
                ]
            );
            ?>
        </div>
    </div>
</div>


<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,

    'beforeRow' => function ($model, $key, $index, $grid) use ($totalCantidadSiesa, $totalCantidadGruma) {
    if ($index === 0) { // Primera fila de cada página
        return "<tr>
                    <td colspan='7'><strong>Gran Total</strong></td>
                    <td><strong>$totalCantidadGruma</strong></td>
                    <td><strong>$totalCantidadSiesa</strong></td>
                </tr>";
    }
},


    'showPageSummary' => true,
    'columns' => array_merge(
        [
            ['class' => 'kartik\grid\SerialColumn'],
        ],

        $gridColumns,

    ),

]); ?>