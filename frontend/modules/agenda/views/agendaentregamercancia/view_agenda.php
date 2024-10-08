<?php

$this->registerCss('
    .mi-gridview {
        font-size: 11px; /* Ajusta el tamaño de la fuente según sea necesario */
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
$filename = "Relacion_AgendaEntregaMercancia_" . date('Y-m-d', strtotime($modelagenda->desde)) . 
            '_' . date('Y-m-d', strtotime($modelagenda->hasta)) . '_' . $fecha_actual;

?>

<?php 
$gridColumns = [
    [
        'attribute' => 'desde',
    ],
    [
        'attribute' => 'hasta',
    ],
    [
        'attribute' => 'radicado',
        'label' => 'Radicado'
    ],

    [
        'attribute' => 'fechaCita', // Nombre del atributo en el modelo
        'label' => 'Fecha Cita',
    ],
    [
        'attribute' => 'horaCita',
    ],
   
    'contacto',
    'fechaContacto',
    'horaContacto',

    [
        'attribute' => 'codigoProveedor', // Nombre del atributo en el modelo
        'label' => 'Cod. Proveedor',
    ],

    'nitProveedor',
    'proveedor',
    //'tipoProveedor',
    'modeloLogistico',

    [
        'attribute' => 'nombreCategoria', // Nombre del atributo en el modelo
        'label' => 'Categoría',
    ],
    [
        'attribute' => 'nombreSubcategoria', // Nombre del atributo en el modelo
        'label' => 'Subcategoría',
    ],

    'serie',
    [
        'attribute' => 'numeroOrdenCompra', // Nombre del atributo en el modelo
        'label' => 'Número Orden',
    ],
    [
        'attribute' => 'nombreTransportadora', // Nombre del atributo en el modelo
        'label' => 'Transportadora',
    ],
    [
        'attribute' => 'numeroGuia', // Nombre del atributo en el modelo
        'label' => 'No. Guía',
    ],
    [
        'attribute' => 'nroPaquetes', // Nombre del atributo en el modelo
        'label' => 'Und. Empaque', // Etiqueta de la columna
    ],
    [
        'attribute' => 'unidadesOC', // Nombre del atributo en el modelo
        'label' => 'Unidades',
    ],
    [
        'attribute' => 'programadas', // Nombre del atributo en el modelo
        'label' => 'Und. Programadas',
    ],
    [
        'attribute' => 'unidadesConteo', // Nombre del atributo en el modelo
        'label' => 'Und. Conteo',
    ],
    [
        'attribute' => 'nombreEstadoAgenda', // Nombre del atributo en el modelo
        'label' => 'Estado',
    ],
    [
        'attribute' => 'nombreEstadoLegaliza', // Nombre del atributo en el modelo
        'label' => 'Legaliza',
    ],
    
    'observacion',

    [
        'attribute' => 'minFechaConteo', // Nombre del atributo en el modelo
        'label' => 'Fecha Inicio Conteo',
    ],
    [
        'attribute' => 'maxFechaConteo', // Nombre del atributo en el modelo
        'label' => 'Fecha Fin Conteo',
    ],
    [
        'attribute' => 'usuariosConteo', // Nombre del atributo en el modelo
        'label' => 'Usuarios Conteo',
    ],

];
?>

<div class="agendaentregamercancia-view">

    <?php echo $this->render('_search_agenda', ['model' => $searchModel, 'idagenda' => $modelagenda->id]); ?>

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
        //'filterModel' => $searchModel,
        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],
        'showPageSummary' => true,
        'columns' => [
            
            [
                'attribute' => 'radicado',
                'label' => 'Radicado'
            ],

            [
                'attribute' => 'fechaCita', // Nombre del atributo en el modelo
                'label' => 'Fecha Cita',
            ],
            [
                'attribute' => 'horaCita',
            ],
           
            'contacto',
            'fechaContacto',
            'horaContacto',

            [
                'attribute' => 'codigoProveedor', // Nombre del atributo en el modelo
                'label' => 'Cod. Proveedor',
            ],

            'nitProveedor',
            'proveedor',
            'modeloLogistico',
            //'tipoProveedor',

            [
                'attribute' => 'nombreCategoria', // Nombre del atributo en el modelo
                'label' => 'Categoría',
            ],
            [
                'attribute' => 'nombreSubcategoria', // Nombre del atributo en el modelo
                'label' => 'Subcategoría',
            ],

            'serie',
            [
                'attribute' => 'numeroOrdenCompra', // Nombre del atributo en el modelo
                'label' => 'Número Orden',
            ],
            [
                'attribute' => 'nombreTransportadora', // Nombre del atributo en el modelo
                'label' => 'Transportadora',
            ],
            [
                'attribute' => 'numeroGuia', // Nombre del atributo en el modelo
                'label' => 'No. Guía',
            ],
            [
                'attribute' => 'unidadesOC', // Nombre del atributo en el modelo
                'label' => 'Unidades',
                'pageSummary' => true,
                'format' => ['decimal',0]
            ],
            [
                'attribute' => 'nroPaquetes', // Nombre del atributo en el modelo
                'label' => 'Und. Empaque', // Etiqueta de la columna
                'pageSummary' => true,
                'format' => ['decimal',0]
            ],

            [
                'attribute' => 'programadas', // Nombre del atributo en el modelo
                'label' => 'Und. Programadas',
                'pageSummary' => true,
                'format' => ['decimal',0]
            ],
            [
                'attribute' => 'unidadesConteo', // Nombre del atributo en el modelo
                'label' => 'Und. Conteo',
                'pageSummary' => true,
                'format' => ['decimal',0]
            ],
            [
                'attribute' => 'nombreEstadoAgenda', // Nombre del atributo en el modelo
                'label' => 'Estado',
            ],
            [
                'attribute' => 'nombreEstadoLegaliza', // Nombre del atributo en el modelo
                'label' => 'Legaliza',
            ],
            
            'observacion'
            
        ],
    ]) ?>

</div>
