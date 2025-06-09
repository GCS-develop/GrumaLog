<?php

use common\models\OrdendecompraSIESA;

// Definir el estilo CSS directamente en la vista
$this->registerCss('
    .mi-gridview {
        font-size: 10px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

    .centrar {
        text-align: center;
    }
');

use frontend\models\Documentosiesa;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/** @var yii\web\View $this */
/** @var frontend\models\search\DocumentosiesaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

if ( $action == 'indexdctointerno'){
    $this->title = 'Documento Contable - Interno';
}else{
    $this->title = 'Documento Contable - SIESA';
}

$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");
$filename = "Relacion_DocumentoContable_" . $fecha_actual;

if ($searchModel->consecutivo){
    $filename = "Relacion_DocumentoContable_" . $searchModel->consecutivo;
}

?>

<?php
$gridColumns = [
    'idContable',
    'cia',
    'co',
    'idEstadoDocumento',
    'tipoDocumento',
    'consecutivo',
    'notas',
    'item',
    'codigoBarras',
    'referencia',
    'descripcion',
    'color',
    'talla',
    'cantidadBase',
    'costoPromedio',
    'bodega',
    'proveedor',
];

$estadodocumento =  "";
if ($searchModel->numeroDocumento && $searchModel->tipoDocumento){
    $puedesubir = OrdendecompraSIESA::puedeSubirArchivo ($searchModel->tipoDocumento, $searchModel->numeroDocumento, $origenconsulta);

    if ($puedesubir) {
        $estadodocumento = "Anulado";
    } else {
        $estadodocumento = "Activo";
    }
}

?>

<div class="documentosiesa-index">

    <?php echo $this->render('_search', ['model' => $searchModel, 'action' => $action]); ?>

    <?php if ($searchModel->consecutivo): ?>
        <div class="alert alert-info">
            <div class="row">
                <div class="col-lg-2">
                    <strong>Consecutivo:</strong> <?= Html::encode($searchModel->consecutivo) ?>
                </div>
                <div class="col-lg-5">
                    <strong>Nota:</strong> <?= Html::encode($searchModel->notas) ?>
                </div>
                <div class="col-lg-2">
                    <strong>Estado:</strong> <?= Html::encode($estadodocumento) ?>
                </div>
                <div class="col-lg-3">
                <strong>Total:</strong> <?= '$ ' . Yii::$app->formatter->asDecimal($searchModel->total, 0) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-12 centrar">   
            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $dataProvider,
                    'columns' => $gridColumns,
                    'fontAwesome' => true,
                    'filename' => $filename,
                    'dropdownOptions' => [
                        'label' => 'Exportar',
                        'class' => 'btn btn-success btn-lg btn-create',
                    ],
                    'exportConfig' => [
                        ExportMenu::FORMAT_TEXT => false,
                        ExportMenu::FORMAT_HTML => false,
                        ExportMenu::FORMAT_EXCEL => false,
                        ExportMenu::FORMAT_PDF => false,
                        ExportMenu::FORMAT_CSV => false,
                        ExportMenu::FORMAT_EXCEL_X => [
                            'label' => 'Excel 2007+',
                            'icon' => 'file-excel-o' ,
                            'iconOptions' => ['class' => 'text-success'],
                            'linkOptions' => [],
                            'options' => ['title' => 'Microsoft Excel 2007+ (xlsx)'],
                            'alertMsg' => 'Se va a generar un archivo en formato EXCEL 2007+ (xlsx).',
                            'mime' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'extension' => 'xlsx',
                            'writer' => ExportMenu::FORMAT_EXCEL_X
                        ],
                        
                    ]                            
                ]);
            ?>        
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'filterModel' => $searchModel,
        'showPageSummary' => true,

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'item',
            'codigoBarras',
            'referencia',
            'descripcion',
            'color',
            'talla',
            [
                'attribute' => 'cantidadBase', 
                'label' => 'Cantidad', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],
            [
                'attribute' => 'costoPromedio', 
                'label' => 'Cantidad', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],
            'bodega',
            'proveedor',
        ],
    ]); ?>


</div>
