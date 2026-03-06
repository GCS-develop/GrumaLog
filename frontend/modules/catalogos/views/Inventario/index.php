<?php

use frontend\models\Bodegas;
use frontend\models\Inventario;
use frontend\models\Item;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use yii\bootstrap5\Modal;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\widgets\Alert;



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

<div class="inventario-index mb-3">

    <div class="row">

        <?php
        $totalCantidadSiesaBruto = (int) Inventario::getTotalExistenciasSiesa($searchModel->codigoBodega);
        $totalCantidadGrumaBruto = (int) Inventario::getotalExistenciasGruma($searchModel->codigoBodega);

        $totalCantidadSiesaReal = (int) Inventario::getTotalExistenciasSiesaReal($searchModel->codigoBodega);
        $totalCantidadGrumaReal = (int) Inventario::getotalExistenciasGrumaReal($searchModel->codigoBodega);        ?>
        <div class="col-lg-6 derecha">

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

        <div class="col-lg-6 izquierda">
            <!-- Botón Sincronizar (abre modal) -->
            <?= Html::button('Sincronizar', [
                'class' => 'btn btn-success btn-lg btn-create',
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#modalSyncInventario',
            ]) ?>
        </div>



    </div>
</div>

<?= Alert::widget() ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,

    'beforeRow' => function ($model, $key, $index, $grid) use (
        $totalCantidadSiesaBruto,
        $totalCantidadGrumaBruto,
        $totalCantidadSiesaReal,
        $totalCantidadGrumaReal
    ) {
        if ($index === 0) {
            return "
        <tr>
            <td colspan='7'><strong>Gran Total (BRUTO - por EAN)</strong></td>
            <td><strong>{$totalCantidadGrumaBruto}</strong></td>
            <td><strong>{$totalCantidadSiesaBruto}</strong></td>
        </tr>
        <tr>
            <td colspan='7'><strong>Gran Total (REAL - por SKU)</strong></td>
            <td><strong>{$totalCantidadGrumaReal}</strong></td>
            <td><strong>{$totalCantidadSiesaReal}</strong></td>
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



<?php Modal::begin([
    'title' => '<strong>Sincronizar inventario por bodega</strong>',
    'id' => 'modalSyncInventario',
    'size' => Modal::SIZE_DEFAULT,
]); ?>

<?php $form = ActiveForm::begin([
    'action' => ['inventario/sincronizar-inventario'],
    'method' => 'post',
]); ?>

<?= $form->field($searchModel, 'codigoBodega')->dropDownList(
    Bodegas::getListaDataCodigo(),
    ['prompt' => 'Seleccione una bodega...']
)->label('Bodega a sincronizar') ?>

<div class="d-flex justify-content-end gap-2">
    <?= Html::button('Cancelar', [
        'class' => 'btn btn-secondary',
        'data-bs-dismiss' => 'modal'
    ]) ?>

    <?= Html::submitButton('Ejecutar sincronización', [
        'class' => 'btn btn-primary',
        'data' => [
            'confirm' => '¿Confirmas ejecutar la sincronización para la bodega seleccionada?',
            'method' => 'post',
        ],
    ]) ?>
</div>

<?php ActiveForm::end(); ?>
<?php Modal::end(); ?>