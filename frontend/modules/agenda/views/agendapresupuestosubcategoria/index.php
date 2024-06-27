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

    /* Hacer las dos primeras columnas sticky */

    th[data-col-seq="0"] ,td[data-col-seq="0"]{
      position: sticky;
      left: 1px;
      z-index: 3; 
      width: 10.93%;
      background-color: #f2f2f2;  
      background: linear-gradient(180deg, #fefefe , #efefef ) ;
      border: 1px solid #dee2e6;
    }

    th[data-col-seq="1"],td[data-col-seq="1"] {
        position: sticky;
        left: 126px;
        z-index: 3; 
        width: 10.93%;
        background-color: #f2f2f2;  
        background: linear-gradient(180deg, #fefefe , #efefef ) ;
        border: 1px solid #dee2e6;
    }
    
    // th[data-col-seq="0"], th[data-col-seq="1"], td[data-col-seq="0"], td[data-col-seq="1"] {
    //   min-width: 100px; /* Puedes ajustar el ancho según tus necesidades */
    // }

    /* Mantener los colores de las filas con la clase .table-striped */
    .table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, .05); 
    }

    /* Mantener los colores de las filas impares para las dos primeras columnas */
    .table-striped tbody tr:nth-of-type(odd) > td:nth-child(1),
    .table-striped tbody tr:nth-of-type(odd) > td:nth-child(2) {
        background-color: #e8e9ec !important; 
    }

    th[data-col-seq="1"],td[data-col-seq="1"] {
        background-color: red !important;
        left: 0 !important;
        position: block;
    }
    
');

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Agendapresupuestosubcategoria;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

use common\models\ProcedimientosGenerales;
use common\widgets\Alert;
use yii\bootstrap4\Modal;

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendapresupuestosubcategoriaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

if ($menu == 'subcategoria'){
    //$this->title = 'Fecha recibo mercancia: ' . $model->periodoAnio . ' - ' . ProcedimientosGenerales::nombreMes($model->periodoMes);
    $this->title = 'Período: ' . $model->periodoAnio . ' - ' . ProcedimientosGenerales::nombreMes($model->periodoMes);
    $this->params['breadcrumbs'][] = ['label' => 'Fecha recibo mercancia', 'url' => ['/agenda/agendapresupuestocategoria/indexperiodo']];
}else{
    $this->title = 'Agenda Presupuesto Categoría: ' . $model->periodoAnio . ' - ' . ProcedimientosGenerales::nombreMes($model->periodoMes);
    $this->params['breadcrumbs'][] = ['label' => 'Consolidado', 'url' => ['/agenda/agendapresupuestodetalle/index', 'idagendapresupuesto' => $model->id]];
}

$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");
$filename = "Resumen_AgendamientoPresupuesto_Subcategoria_" . $model->periodoAnio . '_' . ProcedimientosGenerales::nombreMes($model->periodoMes);
?>

<?php
$gridColumns = [
    [
        'attribute' => 'crossDocking', // Nombre del atributo en el modelo
    ],

    [
        'attribute' => 'categoria',
        'label' => 'Categoría',
    ],
    [
        'attribute' => 'subcategoria',
        'label' => 'Subcategoría',
    ],
    'dia1',
    'dia2',
    'dia3',
    'dia4',
    'dia5',
    'dia6',
    'dia7',
    'dia8',
    'dia9',
    'dia10',
    'dia11',
    'dia12',
    'dia13',
    'dia14',
    'dia15',
    'dia16',
    'dia17',
    'dia18',
    'dia19',
    'dia20',
    'dia21',
    'dia22',
    'dia23',
    'dia24',
    'dia25',
    'dia26',
    'dia27',
    'dia28',
    'dia29',
    'dia30',
    'dia31',
    [
        //'attribute' => 'dia1', // Nombre del atributo en el modelo
        'label' => 'Total', // Etiqueta de la columna
        'value' => function ($model) {
            return  $model['dia1'] + $model['dia2'] + $model['dia3'] + $model['dia4'] + $model['dia5'] +
                    $model['dia6'] + $model['dia7'] + $model['dia8'] + $model['dia9'] + $model['dia10'] +
                    $model['dia11'] + $model['dia12'] + $model['dia13'] + $model['dia14'] + $model['dia15'] +
                    $model['dia16'] + $model['dia17'] + $model['dia18'] + $model['dia19'] + $model['dia20'] +
                    $model['dia21'] + $model['dia22'] + $model['dia23'] + $model['dia24'] + $model['dia25'] +
                    $model['dia26'] + $model['dia27'] + $model['dia28'] + $model['dia29'] + $model['dia30'] +
                    $model['dia31'];
        },
    ],
];
?>

<?php
    Modal::begin([                
        'title'=>'<h4>Datos Básicos Fechas recibo mercancía</h4>',
        'id'=>'modaldata',
        'size'=>'modal-lg',
        'options' => [
            'tabindex' => false  // Importante para que funcione el Select
        ]
    ]);
        
    echo "<div id='modalContentData'></div>";
        
    Modal::end(); 
?>

<div class="agendapresupuestosubcategoria-index">

    <div class="row">

        <div class="col-lg-12 centrar">   
            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $dataProviderDia,
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

    <?php 
        // Mostrar un GridView por cada bodega
    foreach ($dataByCrossDocking as $crossdocking => $data) {
    echo "<h2>Destino: $crossdocking</h2>";
    ?>

    <?= GridView::widget([
        'dataProvider' => new yii\data\ArrayDataProvider([
            'allModels' => $data,
            'pagination' => false,
        ]),

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'showPageSummary' => true,

        'columns' => [
            //'crossDocking',
            'categoria',
            'subcategoria',

            [
                'attribute' => 'dia1', // Nombre del atributo en el modelo
                'label' => '01', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia2', // Nombre del atributo en el modelo
                'label' => '02', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia3', // Nombre del atributo en el modelo
                'label' => '03', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia4', // Nombre del atributo en el modelo
                'label' => '04', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia5', // Nombre del atributo en el modelo
                'label' => '05', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia6', // Nombre del atributo en el modelo
                'label' => '06', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia7', // Nombre del atributo en el modelo
                'label' => '07', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia8', // Nombre del atributo en el modelo
                'label' => '08', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia9', // Nombre del atributo en el modelo
                'label' => '09', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia10', // Nombre del atributo en el modelo
                'label' => '10', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia11', // Nombre del atributo en el modelo
                'label' => '11', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia12', // Nombre del atributo en el modelo
                'label' => '12', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia13', // Nombre del atributo en el modelo
                'label' => '13', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia14', // Nombre del atributo en el modelo
                'label' => '14', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia15', // Nombre del atributo en el modelo
                'label' => '15', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia16', // Nombre del atributo en el modelo
                'label' => '16', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia17', // Nombre del atributo en el modelo
                'label' => '17', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia18', // Nombre del atributo en el modelo
                'label' => '18', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia19', // Nombre del atributo en el modelo
                'label' => '19', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia20', // Nombre del atributo en el modelo
                'label' => '20', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia21', // Nombre del atributo en el modelo
                'label' => '21', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia22', // Nombre del atributo en el modelo
                'label' => '22', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia23', // Nombre del atributo en el modelo
                'label' => '23', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia24', // Nombre del atributo en el modelo
                'label' => '24', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia25', // Nombre del atributo en el modelo
                'label' => '25', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia26', // Nombre del atributo en el modelo
                'label' => '26', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia27', // Nombre del atributo en el modelo
                'label' => '27', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia28', // Nombre del atributo en el modelo
                'label' => '28', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia29', // Nombre del atributo en el modelo
                'label' => '29', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia30', // Nombre del atributo en el modelo
                'label' => '30', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia31', // Nombre del atributo en el modelo
                'label' => '31', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'pageSummary' => true
            ],

            [
                //'attribute' => 'dia1', // Nombre del atributo en el modelo
                'label' => 'Total', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'value' => function ($model) {
                    return  $model['dia1'] + $model['dia2'] + $model['dia3'] + $model['dia4'] + $model['dia5'] +
                            $model['dia6'] + $model['dia7'] + $model['dia8'] + $model['dia9'] + $model['dia10'] +
                            $model['dia11'] + $model['dia12'] + $model['dia13'] + $model['dia14'] + $model['dia15'] +
                            $model['dia16'] + $model['dia17'] + $model['dia18'] + $model['dia19'] + $model['dia20'] +
                            $model['dia21'] + $model['dia22'] + $model['dia23'] + $model['dia24'] + $model['dia25'] +
                            $model['dia26'] + $model['dia27'] + $model['dia28'] + $model['dia29'] + $model['dia30'] +
                            $model['dia31'];
                },
                'pageSummary' => true
            ],

        ],
    ]); ?>

    <?php } ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'showPageSummary' => true,

        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '2%',
            ],

            //'id',
            //'idAgendaPresupuesto',
            //'periodoAnio',
            //'periodoMes',
            [
                'attribute' => 'crossDocking', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '15%',
            ],

            [
                'attribute' => 'categoria', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '20%',
            ],
            [
                'attribute' => 'subcategoria', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '20%',
            ],

            [
                'attribute' => 'fechaLlegada',
                //'label' => 'Date',
                'format' => ['date', 'php:Y-m-d'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '10%',
                // También puedes ajustar otras propiedades de estilo según tus necesidades
                //'contentOptions' => ['style' => 'font-weight:bold;'],
            ],

            [
                'attribute' => 'cantidad', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'format' => ['decimal', 0], // Formato decimal con 2 decimales
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
                'pageSummary' => true,
                'filter' => ''
            ],

            [
                'attribute' => 'cantidadAgendada', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'format' => ['decimal', 0], // Formato decimal con 2 decimales
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
                'pageSummary' => true,
                'filter' => ''
            ],

            [
                'attribute' => 'Disponible', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'value' => function ($model){
                    return $model->cantidad - $model->cantidadAgendada;
                },
                'format' => ['decimal', 0], // Formato decimal con 2 decimales
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '10%',
                'pageSummary' => true
            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{update} {delete}',

                'buttons' => [

                    'update' => function ($url, $model) {                                
                        $t = Url::to([  'update', 
                                        'id' => $model->id
                                    ]);

                        return Html::button('<i class="fa fa-edit"></i>',[
                                    'value'=> $t,
                                    'title' => 'Actualizar Fecha mercancia categoría',
                                    'class' => 'btn btn-default btn_update',
                        ]);
                    },

                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Fecha mercancia categoría',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar este Registro? ( ' . $model->fechaLlegada . ' - ' .
                                                                                        $model->crossDocking . ' - ' .
                                                                                        $model->categoria . ' - ' .
                                                                                        $model->subcategoria . ' - ' .
                                                                                        $model->cantidad . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    }, 

                ],

            ],


        ],
    ]); ?>


</div>
