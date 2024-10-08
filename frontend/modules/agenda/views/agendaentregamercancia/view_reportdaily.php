<?php

$this->registerCss('
    .mi-gridview {
        font-size: 14px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

    .btn-create {
        width: 300px;
    }
    
    .centrar {
        text-align: center;
    }

    .izquierda {
        text-align: left;
    }

    .derecha {
        text-align: right;
    }

    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc; /* Color y grosor de la línea */
        margin: 10px 0; /* Espacio alrededor de la línea */
    }

    .negative-balance {
        background-color: #ff4d4d; /* Rojo claro para una mejor visibilidad */
        color: white; /* Texto blanco */
    }
');


use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/** @var yii\web\View $this */
/** @var frontend\models\Agendaentregamercancia $model */

$this->title = 'Consultar Agendamiento';
$this->params['breadcrumbs'][] = ['label' => 'Agendamiento', 'url' => ['indexagendaperiodo']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$fecha_actual = date("Y-m-d");
$filename = "Relacion_AgendaEntregaMercancia_Diario_" .  $fecha_actual;

$filenameVMI = "Relacion_AgendaEntregaMercanciaVMI_Diario_" .  $fecha_actual;

?>

<?php 
$gridColumns = [

    [
        'attribute' => 'fecha', // Nombre del atributo en el modelo
        'label' => 'Fecha',
    ],
    [
        'attribute' => 'total', // Nombre del atributo en el modelo
        'label' => 'Total Presupuestado',
    ],
    [
        'attribute' => 'agendado', // Nombre del atributo en el modelo
        'label' => 'Total Agendado',
    ],
    [
        'attribute' => 'sinagendar', // Nombre del atributo en el modelo
        'label' => 'Total Sin Agendar',
    ],
];

$gridColumnsVMI = [

    [
        'attribute' => 'fecha', // Nombre del atributo en el modelo
        'label' => 'Fecha',
    ],
    [
        'attribute' => 'total', // Nombre del atributo en el modelo
        'label' => 'Total Presupuestado',
    ],
    [
        'attribute' => 'agendado', // Nombre del atributo en el modelo
        'label' => 'Total Agendado',
    ],
    [
        'attribute' => 'sinagendar', // Nombre del atributo en el modelo
        'label' => 'Total Sin Agendar',
    ],
];
?>

<div class="agendaentregamercancia-view">

    <div class="row">

        <div class="col-lg-6 centrar">   
            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $dataProvider,
                    'columns' => $gridColumns,
                    'fontAwesome' => true,
                    'filename' => $filename,
                    'dropdownOptions' => [
                        'label' => 'Exportar CEDI+TIENDAS',
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

        <div class="col-lg-6 centrar">   
            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $dataProviderVMI,
                    'columns' => $gridColumnsVMI,
                    'fontAwesome' => true,
                    'filename' => $filenameVMI,
                    'dropdownOptions' => [
                        'label' => 'Exportar VMI',
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

    <?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

    <div class="row">

        <div class="col-lg-6 centrar">

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                //'filterModel' => $searchModel,
                //'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
                'summary' => '',
	        	'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
	        	'options' => [
	        		'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
	        	],
                'showPageSummary' => true,
            
                'panel' => [
                    'heading' => Html::encode('Saldos Diarios Presupuesto Agendamiento - CEDI'),
                    'type' => 'primary',
                    'footer' => false
                ],

                'toolbar' => [
                    //'{toggleData}', // Excluye '{export}' para ocultar la opción de descarga
                ],
            
                'rowOptions' => function ($model) {
                    $options = [];
                
                    if ($model['sinagendar'] < 0){
                        $options['style'] = 'background-color: #ff4d4d; color:white; font-weight: bold;'; // Puedes cambiar el color aquí
                    }
                
                    return $options;
                },
            
                'columns' => [

                    [
                        'attribute' => 'fecha', // Nombre del atributo en el modelo
                        'label' => 'Fecha',
                        'hAlign' => 'center',
                        'vAlign' => 'middle',
                    ],
                
                    [
                        'attribute' => 'total', // Nombre del atributo en el modelo
                        'label' => 'Total Presupuestado',
                        'hAlign' => 'right',
                        'vAlign' => 'middle',
                        'format' => ['decimal', 0], // Formato decimal con 0 decimales
                        'pageSummary' => true,
                    ],
                    [
                        'attribute' => 'agendado', // Nombre del atributo en el modelo
                        'label' => 'Total Agendado',
                        'hAlign' => 'right',
                        'vAlign' => 'middle',
                        'format' => ['decimal', 0], // Formato decimal con 0 decimales
                        'pageSummary' => true,
                    ],
                    [
                        'attribute' => 'sinagendar', // Nombre del atributo en el modelo
                        'label' => 'Total Sin Agendar',
                        'hAlign' => 'right',
                        'vAlign' => 'middle',
                        'format' => ['decimal', 0], // Formato decimal con 0 decimales
                        'pageSummary' => true,
                    ],

                ],
            
            ]) ?>

        </div>

        <div class="col-lg-6 centrar">

            <?= GridView::widget([
                'dataProvider' => $dataProviderVMI,
                //'filterModel' => $searchModel,
                // 'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
                'summary' => '',
	        	'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
	        	'options' => [
	        		'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
	        	],
                'showPageSummary' => true,
            
                'panel' => [
                    'heading' => Html::encode('Saldos Diarios Presupuesto Agendamiento - VMI'),
                    'type' => 'primary',
                    'footer' => false
                ],

                'toolbar' => [
                    //'{toggleData}', // Excluye '{export}' para ocultar la opción de descarga
                ],
            
                'rowOptions' => function ($model) {
                    $options = [];
                
                    if ($model['sinagendar'] < 0){
                        $options['style'] = 'background-color: #ff4d4d; color:white; font-weight: bold;'; // Puedes cambiar el color aquí
                    }
                
                    return $options;
                },
            
                'columns' => [

                    [
                        'attribute' => 'fecha', // Nombre del atributo en el modelo
                        'label' => 'Fecha',
                        'hAlign' => 'center',
                        'vAlign' => 'middle',
                    ],
                
                    [
                        'attribute' => 'total', // Nombre del atributo en el modelo
                        'label' => 'Total Presupuestado',
                        'hAlign' => 'right',
                        'vAlign' => 'middle',
                        'format' => ['decimal', 0], // Formato decimal con 0 decimales
                        'pageSummary' => true,
                    ],
                    [
                        'attribute' => 'agendado', // Nombre del atributo en el modelo
                        'label' => 'Total Agendado',
                        'hAlign' => 'right',
                        'vAlign' => 'middle',
                        'format' => ['decimal', 0], // Formato decimal con 0 decimales
                        'pageSummary' => true,
                    ],
                    [
                        'attribute' => 'sinagendar', // Nombre del atributo en el modelo
                        'label' => 'Total Sin Agendar',
                        'hAlign' => 'right',
                        'vAlign' => 'middle',
                        'format' => ['decimal', 0], // Formato decimal con 0 decimales
                        'pageSummary' => true,
                    ],

                ],
            
            ]) ?>

        </div>

    </div>

</div>
