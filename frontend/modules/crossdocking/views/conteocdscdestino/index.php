<?php

$this->registerCss('
    .mi-gridview {
        font-size: 12px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

    .btn-create {
        width: 300px;
    }
    
    .centrar {
        text-align: center;
    }
');

use frontend\models\Conteocdscdestino;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/** @var yii\web\View $this */
/** @var frontend\models\search\ConteocdscdestinoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Exportar CDSC por Destino';
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");

$filename = "Relacion_CDSCDestino_General_"  . $fecha_actual;

?>

<?php 
$gridColumns = [
    'radicado',
    'id',
    'codigoAlmacenLegaliza',
    'nombreAlmacenLegaliza',
    'codigoAlmacen',
    'almacen',
    [
        'attribute' => 'created_at', // Nombre del atributo en el modelo
        'format' => ['date', 'php:Y-m-d H:i'],
    ],  

    [
        'attribute' => 'updated_at', // Nombre del atributo en el modelo
        'format' => ['date', 'php:Y-m-d H:i'],
    ],
    'codigoProveedor',
    'nit',
    'razonSocial',
    'tipoProveedor',
    'numeroFactura',
    'total',

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
        }
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
        }
    ],
    [
        'attribute' => 'idEstadoEntrada',           
        'value' => function($model) {
            $estado = '';
            switch($model->idEstadoEntrada){
                case 1:
                    $estado = 'Autorizada'; break;
                case 2:
                    $estado = 'Generada'; break;
                default:
                    $estado = 'Sin Entrada'; break;
            }
            
            return $estado;
        }
    ],
    [
        'attribute' => 'idEstadoTraspaso',           
        'value' => function($model) {
            $estado = '';
            switch($model->idEstadoTraspaso){
                case 1:
                    $estado = 'Pendiente'; break;
                case 2:
                    $estado = 'Autorizada'; break;
                case 3:
                    $estado = 'Generada'; break;
                default:
                    $estado = 'Sin Traspaso'; break;
            }
            
            return $estado;
        }
    ],

    'nombreEmpleado'
];
?>

<div class="conteocdscdestino-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

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

        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'radicado',
                'vAlign'=>'middle',
                'hAlign'=>'right',  
            ],
            [
                'attribute' => 'id',
                'vAlign'=>'middle',
                'hAlign'=>'right',  
            ],
            [
                'attribute' => 'codigoAlmacenLegaliza',
                'vAlign'=>'middle',
                'hAlign'=>'center',  
            ],
            [
                'attribute' => 'nombreAlmacenLegaliza',
                'vAlign'=>'middle',
                'hAlign'=>'left',  
            ],
            [
                'attribute' => 'codigoAlmacen',
                'vAlign'=>'middle',
                'hAlign'=>'center',  
            ],
            [
                'attribute' => 'almacen',
                'vAlign'=>'middle',
                'hAlign'=>'left',  
            ],
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
                'attribute' => 'codigoProveedor',
                'vAlign'=>'middle',
                'hAlign'=>'right',  
            ],
            [
                'attribute' => 'nit',
                'vAlign'=>'middle',
                'hAlign'=>'right',  
            ],

            [
                'attribute' => 'razonSocial',
                'vAlign'=>'middle',
                'hAlign'=>'left',  
            ],
            [
                'attribute' => 'tipoProveedor',
                'vAlign'=>'middle',
                'hAlign'=>'left',  
            ],
            [
                'attribute' => 'numeroFactura',
                'vAlign'=>'middle',
                'hAlign'=>'right',  
            ],

            //'total',

            [
                'attribute' => 'idEstadoFactura',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
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
                }
            ],

            [
                'attribute' => 'idLegalizadoFactura',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                    $estado = '';
                    switch($model->idLegalizadoFactura){
                        case 1:
                            $estado = 'Legalizado'; break;
                        default:
                            $estado = 'No Legalizado'; break;
                    }
                    
                    return $estado;
                }
            ],

            [
                'attribute' => 'nombreEmpleado',
                'vAlign'=>'middle',
                'hAlign'=>'left',  
            ],

            //'idItemUltimoConteo',
            //'total',
            //'idEstado',
            //'idLegalizado',
            //
            //'created_by',
            //'updated_at',
            //'updated_by',
            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Conteocdscdestino $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
        ],
    ]); ?>


</div>
