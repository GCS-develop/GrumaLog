<?php

$this->registerCss('
    .mi-gridview {
        font-size: 12px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

    .btn-create {
        width: 300px;
    }

    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc; /* Color y grosor de la línea */
        margin: 10px 0; /* Espacio alrededor de la línea */
    }

    .centrar {
        text-align: center;
    }

    .mi-titulo-red {
        font-weight: bold;
        font-size: 18px;
        color: red;
    }

    .mi-titulo-black {
        font-weight: bold;
        font-size: 18px;
        color: black;
    }
');

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use kartik\grid\SerialColumn;
use yii\bootstrap4\Modal;


$this->title = 'Conteo - Curva de Tallas y Colores';
$this->params['breadcrumbs'][] = ['label' => 'Entrada Factura CDSC', 'url' => ['indexentrada']];
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");

$numerofactura = $modelfactura->numeroFactura;
$nit = $modelfactura->proveedor->nit;

$numero = $nit . '-' .$numerofactura ;

$filename = "Relacion_CDSCDestino_Matriz_CurvaTallasColores_" . $numero . "_" . $fecha_actual;
$filenameBD = "Relacion_CDSCDestino_BD_CurvaTallasColores_" . $numero . "_" . $fecha_actual;

?>

<?php
    Modal::begin([                
        'title'=>'<h4>Registro datos básicos entrada de factura</h4>',
        'id'=>'modaldata',
        'size'=>'modal-lg',
        'options' => [
            'tabindex' => false  // Importante para que funcione el Select
        ]
    ]);
        
    echo "<div id='modalContentData'></div>";
        
    Modal::end(); 
?>

<?php

// Obtener todas las tallas únicas
$tallasUnicas = [];
foreach ($dataProvider as $fila) {
    foreach (array_keys($fila) as $columna) {
        if ($columna !== 'radicado' && $columna !== 'item' && $columna !== 'descripcion' && $columna !== 'color' && $columna !== 'unidades' && $columna !== 'totalUnidadesConteo') {
            if (!in_array($columna, $tallasUnicas)) {
                $tallasUnicas[] = $columna;
            }
        }
    }
}

//					

$gridColumns = [
    
    'radicado',
    'razonSocial',
    'nit',
    'codigoProveedor',
    'tipoProveedor',
    'numeroFactura',
    'fecha',

    [
        'attribute' => 'idEstadoFactura',
        'value' => function($model) {
            $estado = '';
            switch($model->idEstadoFactura){
                case 1:
                    $estado = 'En Conteo'; break;
                case 2:
                    $estado = 'Finalizada'; break;
                default:
                    $estado = 'Sin Conteo'; break;
            }
            
            return $estado;
        },
    ],

    [
        'attribute' => 'idLegalizadoFactura',
        'value' => function($model) {
            $estado = '';
            switch($model->idLegalizadoFactura){
                case 1:
                    $estado = 'Legalizado'; break;
                default:
                    $estado = 'No Legalizado'; break;
            }
            
            return $estado;
        },
    ],

    'numeroCajas',
    'nombreEmpleado',

    'almacen',
    'codigoAlmacen',
    'total',
];

// Definir las columnas para el GridView
$columns = [
    [
        'attribute' => 'item', // Nombre del atributo en el modelo
        'label' => 'Item', // Etiqueta de la columna
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],

    [
        'attribute' => 'color', // Nombre del atributo en el modelo
        'label' => 'color', // Etiqueta de la columna
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],

    [
        'attribute' => 'descripcion', // Nombre del atributo en el modelo
        'label' => 'Descripción', // Etiqueta de la columna
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],

    [
        'attribute' => 'totalUnidadesConteo', // Nombre del atributo en el modelo
        'label' => 'Total UND', // Etiqueta de la columna
        'hAlign' => 'right', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
        'format' => ['decimal', 0], // Formato decimal con 0 decimales,
        'pageSummary' => true,

    ],
];

// Agregar las columnas por talla dinámicamente
foreach ($tallasUnicas as $talla) {
    $columns[] = [
        'label' => 'Talla: ' . $talla,
        'value' => function ($model) use ($talla) {
            //return isset($model[$talla]) ?  ($model[$talla]['unidadesConteo'])  : null;
            $unidades = 0;

            if (isset($model[$talla])) {
                if (isset($model[$talla]['unidades'])) {
                    // La clave 'unidadesConteo' está definida en la fila actual
                    $unidades = $model[$talla]['unidades'];
                }
            } 

            return $unidades;
        },
        'pageSummary' => true,
        'hAlign' => 'right', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ];
}

?>

<div class="ordendecompradetalle-index">

    <div class="row">

        <div class="col-lg-4 centrar">   
            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => new \yii\data\ArrayDataProvider([
                        'allModels' => $dataProvider,
                        'pagination' => false, // Opcional: desactiva la paginación si no la necesitas
                    ]),
                    'columns' => $columns,
                    'fontAwesome' => true,
                    'filename' => $filename,
                    'dropdownOptions' => [
                        'label' => 'Exportar Matriz',
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

        <div class="col-lg-4 centrar">
            <?php $url = Url::to(['entradafactura', 'idconteofactura' => $modelfactura->id]); ?>
            
            <p>
            <?= Html::button('Registrar Entrada', 
                        [   'value'=>  $url, 
                            'class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreate',
                        ]) 
            ?>
            </p>
        </div>

        <div class="col-lg-4 centrar">   
            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $dataProviderBD,
                    'columns' => $gridColumns,
                    'fontAwesome' => true,
                    'filename' => $filenameBD,
                    'dropdownOptions' => [
                        'label' => 'Exportar BD',
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

    <!-- Espacio -->
    <div class="row">
        <div class="col-md-12" style="margin-bottom: 20px;"></div>
    </div>

    <?php 
        echo '<div class="mi-titulo-black">Proveedor:' . $modelfactura->proveedor->nit . '-' . 
        $modelfactura->proveedor->razonSocial . '-' .
        $modelfactura->numeroFactura . '</div>';
    ?>

    <div class="row">
        <div class="col-md-12" style="margin-bottom: 20px;"></div>
    </div>

    <?= GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'allModels' => $dataProvider,
            //'allModels' => $data,
            'pagination' => false, // Opcional: desactiva la paginación si no la necesitas
        ]),

        'summary' => '',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],
        'showPageSummary' => true,

        'columns' => $columns,

        /*'rowOptions' => function ($model, $key, $index, $grid) {
            $options = [];
            $diferencia = $model['totalUnidadesAsignadas'] - $model['totalUnidadesConteo'];

            if ($diferencia != 0){
                $options['style'] = 'background-color: #ff9999;'; // Puedes cambiar el color aquí
            }

            return $options;
        },*/

    ]); ?>

    <?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

    <?= GridView::widget([
        'dataProvider' => $dataProviderBD,
        //'filterModel' => $searchModel,

        'summary' => '',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'showPageSummary' => true,

        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            'radicado',
            'id',
            'codigoAlmacen',
            'almacen',
            [
                'attribute' => 'created_at', // Nombre del atributo en el modelo
                //'label' => 'Desde', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d H:i'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],  

            [
                'attribute' => 'updated_at', // Nombre del atributo en el modelo
                //'label' => 'Desde', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d H:i'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'total', // Nombre del atributo en el modelo
                'label' => 'Total UND Conteo', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'pageSummary' => true,
        
            ],

        ],
    ]); ?>


</div>
