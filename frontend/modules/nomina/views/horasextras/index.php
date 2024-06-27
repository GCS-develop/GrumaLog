<?php
// Definir el estilo CSS directamente en la vista
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
');

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Horasextras;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

use yii\bootstrap4\Modal;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\HorasextrasSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Horas Extras';
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");
$filename = "Relacion_HorasExtras_" . $fecha_actual;

?>

<?php
    Modal::begin([                
        'title'=>'<h4>Registro de Horas Extras</h4>',
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
$gridColumns = [
    [
        'attribute' => 'fecha',
        'format' => ['date', 'php:Y-m-d'],
    ],

    [
        'attribute' => 'identificacion', // Nombre del atributo en el modelo
        'format' => ['decimal', 0], // Formato decimal con 2 decimales
    ],

    [
        'attribute' => 'nombreEmpleado', // Nombre del atributo en el modelo
    ],
    [
        'attribute' => 'codigoCO', // Nombre del atributo en el modelo
    ],

    [
        'attribute' => 'nombreCO', // Nombre del atributo en el modelo
    ],

    [
        'attribute' => 'numeroHoras', // Nombre del atributo en el modelo
        'format' => ['decimal', 2], // Formato decimal con 2 decimales
    ],

    [
        'attribute' => 'observacion', // Nombre del atributo en el modelo
    ],  

];
?>

<div class="horasextras-index">

    <?= Alert::widget() ?>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="row">
        <div class="col-lg-6 centrar">
            <?php $url = Url::to(['create']); ?>
            <p>
            <?= Html::button('Registrar', 
                        ['value'=>  $url, 'class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreate']) 
            ?>
            </p>
        </div>

        <div class="col-lg-6 centrar">   
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
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
            ],

            //'id',
            [
                'attribute' => 'fecha',
                //'label' => 'Date',
                'format' => ['date', 'php:Y-m-d'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '10%',
                // También puedes ajustar otras propiedades de estilo según tus necesidades
                //'contentOptions' => ['style' => 'font-weight:bold;'],
            ],

            //'idEmpleado',
            [
                'attribute' => 'identificacion', // Nombre del atributo en el modelo
                //'label' => 'Identificación', // Etiqueta de la columna
                'format' => ['decimal', 0], // Formato decimal con 2 decimales
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
            ],

            [
                'attribute' => 'nombreEmpleado', // Nombre del atributo en el modelo
                //'label' => 'Nombre Empleado', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '20%',
            ],
            //'idCO',
            [
                'attribute' => 'codigoCO', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
            ],

            [
                'attribute' => 'nombreCO', // Nombre del atributo en el modelo
                //'label' => 'Nombre Empleado', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '15%',
            ],

            [
                'attribute' => 'numeroHoras', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'format' => ['decimal', 2], // Formato decimal con 2 decimales
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
            ],

            [
                'attribute' => 'observacion', // Nombre del atributo en el modelo
                //'label' => 'Observación', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '15%',
            ],  
            
            [
                'attribute' => 'idEstado', // Nombre del atributo en el modelo
                //'label' => 'Observación', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
                'value' => function ($model) {
                    switch ($model->idEstado) {
                        case 0:
                            return 'Anulado';
                        case 1:
                            return 'Sin Contabilizar';
                        case 2:
                            return 'Contabilizado';
                        default:
                            return '';
                    }
                },
            ],  
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '10%'],
                'template' => '{update} {delete}',

                'buttons' => [

                    'update' => function ($url, $model) {                                
                        $t = Url::to([  'update', 
                                        'id' => $model->id
                                    ]);

                        return Html::button('<i class="fa fa-edit"></i>',[
                                    'value'=> $t,
                                    'title' => 'Actualizar Horas Extras',
                                    'class' => 'btn btn-default btn_update',
                        ]);
                    },

                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Período',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar este Registro? ( ' . $model->fecha . ' - ' . 
                                                                                        $model->nombreCO . ' - ' .
                                                                                        $model->nombreEmpleado . ' )',
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
