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

use frontend\models\Conteobylecturacodigo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;


/** @var yii\web\View $this */
/** @var frontend\models\search\ConteobylecturacodigoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Conteo por Usuario';
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");
$filename = "Relacion_ConteoMercanciaUsuario_" . $fecha_actual;
?>

<?php 
$gridColumns = [
    'idUserConteo',
    'nombreEmpleado',
    'fechaConteo', 
    [
        'attribute' => 'OC', // Nombre del atributo en el modelo
        'label' => 'Orden Compra', // Etiqueta de la columna
        'value' => function ($model){
            return $model->codigoTipodocumento . '-' . $model->consecutivo;
        }
    ],

    'idItem',
    'item',
    'color',
    'talla',
    'codigoBarras',
    'unidadEmpaque',

    [
        'attribute' => 'totalUnidades', // Nombre del atributo en el modelo
        'label' => 'Unidades', // Etiqueta de la columna
    ],
];
?>


<div class="conteobylecturacodigo-index">

    <div class="row">
        <div class="col-lg-12 centrar">
            <?php echo $this->render('_search_usuario', ['model' => $searchModel]); ?>
        </div>
    </div>

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
            ['class' => 'kartik\grid\SerialColumn'],

            'idUserConteo',
            'nombreEmpleado',
            'fechaConteo',

            [
                'attribute' => 'OC', // Nombre del atributo en el modelo
                'label' => 'Orden Compra', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->codigoTipodocumento . '-' . $model->consecutivo;
                }
            ],

            //'idItem',
            'item',
            'color',
            'talla',
            'codigoBarras',
            'unidadEmpaque',

            [
                'attribute' => 'totalUnidades', // Nombre del atributo en el modelo
                'label' => 'Unidades', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Conteobylecturacodigo $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
        ],
    ]); ?>


</div>
