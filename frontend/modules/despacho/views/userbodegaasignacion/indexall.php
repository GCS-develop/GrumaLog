<?php

// Definir el estilo CSS directamente en la vista
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

    .izquierda {
        text-align: left;
    }

    .derecha {
        text-align: right;
    }
');

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Userbodegaasignacion;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

use common\models\ProcedimientosGenerales;
use common\widgets\Alert;
use yii\bootstrap4\Modal;

/** @var yii\web\View $this */
/** @var frontend\models\search\UserbodegaasignacionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Usuarios Planillas';
$this->params['breadcrumbs'][] = ['label' => 'Usuarios Planillas', 'url' => ['/despacho/userbodega/index']];
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");
$filename = "Relacion_UsuariosPlanillas_" . $fecha_actual;

?>

<?php
$gridColumns = [
    [
        'attribute' => 'identificacion', 
    ],

    [
        'attribute' => 'nombreEmpleado', 
    ],
    
    [
        'attribute' => 'username', 

    ],

    [
        'attribute' => 'codigoAlmacen', 
    ],

    [
        'attribute' => 'almacen', 
    ],

    [
        'attribute' => 'idEstado',
        'value' => function($model){
            return $model->idEstado == 0 ? 'Inactivo' : 'Activo';
        },
    ],
];
?>

<div class="userbodegaasignacion-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
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
        'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            [
                'attribute' => 'identificacion', 
                'format' => ['decimal', 0], 
                'hAlign' => 'right', 
                'vAlign' => 'middle', 
            ],

            [
                'attribute' => 'nombreEmpleado', 
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
            ],
            
            [
                'attribute' => 'username', 
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
            ],

            [
                'attribute' => 'codigoAlmacen', 
                'hAlign' => 'center', 
                'vAlign' => 'middle', 
            ],

            [
                'attribute' => 'almacen', 
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
            ],

            [
                'attribute' => 'idEstado',
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
                'filter' => ['0' => 'Inactivo', '1' => 'Activo'],
                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Seleccione una opción'],
                'value' => function($model){
                    return $model->idEstado == 0 ? 'Inactivo' : 'Activo';
                },
            ],

            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            /*[
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{delete}',

                'buttons' => [

                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Almacén',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar este Registro? ( ' . $model->identificacion . ' - ' . 
                                                                                        $model->nombreEmpleado . ' - ' .
                                                                                        $model->codigoAlmacen . ' - ' . 
                                                                                        $model->almacen . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },

                ],

            ],*/
        ],
    ]); ?>


</div>
