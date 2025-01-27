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

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Agendaentregamercancia;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use common\widgets\Alert;

use common\models\ProcedimientosGenerales;
use frontend\models\Transportadora;

use frontend\models\Ordendecompradetalle;
use frontend\models\Programacionentregamercancia;
use frontend\models\Conteoentregamercancia;
use frontend\models\Estadoconteo;

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendaentregamercanciaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Programación';
$this->params['breadcrumbs'][] = ['label' => 'Conteo Recibo Mercancía', 'url' => ['indexconteoagenda']];
$this->params['breadcrumbs'][] = $this->title;

$modelestado = Estadoconteo::find()->where(['codigo' => 2])->one();
$estadofinal = $modelestado->id;

?>

<div class="agendaentregamercancia-index">

    <?= Alert::widget() ?>

    <?php echo $this->render('_search_conteo_programacion', ['model' => $searchModel, 'idagenda' => $idagenda]); ?>

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
            //['class' => 'yii\grid\SerialColumn'],
            // 'id',

            [
                'attribute' => 'idAgenda', // Nombre del atributo en el modelo
                'label' => 'Radicado',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'idProgramacion', // Nombre del atributo en el modelo
                'label' => 'Programación',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'created_at', // Nombre del atributo en el modelo
                'label' => 'Fecha', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d H:i'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            
            [
                'attribute' => 'codigoCentroOperacion',
                'label' => 'Almacen',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'codigoTipoDocumento',
                'label' => 'Serie',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'numeroOrdenCompra', // Nombre del atributo en el modelo
                'label' => 'Número Orden', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro

            ],

            [
                'attribute' => 'nombreCategoria', // Nombre del atributo en el modelo
                'label' => 'Categoría', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro

            ], 

            [
                'attribute' => 'item', // Nombre del atributo en el modelo
                'label' => 'Item', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro

            ], 

            [
                'attribute' => 'descripcion', // Nombre del atributo en el modelo
                'label' => 'Descripción', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro

            ],

            [
                'attribute' => 'nombreUsuario', // Nombre del atributo en el modelo
                'label' => 'Nombre Usuario', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'unidadesEmpaque', // Nombre del atributo en el modelo
                'label' => 'UND Emp', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'unidadesConteo', // Nombre del atributo en el modelo
                'label' => 'UND Conteo', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'value' => function ($model){
                    return Conteoentregamercancia::totalCantidadConteo ($model->idProgramacion,$model->item,$model->idUserConteo);
                },
                'pageSummary' => true,
            ],


            /*
            
            [
                'attribute' => 'razonSocial', // Nombre del atributo en el modelo
                'label' => 'Razón Social', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'numeroCajas', // Nombre del atributo en el modelo
                'label' => 'Cajas', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales

            ],

            [
                'attribute' => 'idTransportadora', // Nombre del atributo en el modelo
                'label' => 'Transportadora', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    $nombre = '-';
                    if ($model->idTransportadora){
                        $nombre = $model->transportadora->nombre;
                    }
                    return $nombre;
                }
            ],

            [
                'attribute' => 'contacto', // Nombre del atributo en el modelo
                //'label' => 'Contacto', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],  
            [
                'attribute' => 'fechaContacto', // Nombre del atributo en el modelo
                //'label' => 'Fecha Contacto', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],  
 
            //'numeroGuia',
            //'observacion',
            */

            [
                'attribute' => 'nombreEstadoProgramacion', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{viewconteo} {end} {habilitarconteo}',

                'buttons' => [

                    'viewconteo' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-qrcode"></i>',
                                [   '/programacion/conteoentregamercancia/indexprogramacion', 
                                                                        'idprogramacion' => $model->idProgramacion,], 
                                [
                                    'title' => 'Visualizar Datos Conteo',
                                    'class' => 'btn btn-default btn_asignar_cita',
                                ]
                        );
                    },

                    'end' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-window-close"></i>', 
                                [   '/programacion/conteoentregamercancia/indexprogramacion', 
                                                            'idprogramacion' => $model->idProgramacion,
                                ], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Finalizar Conteo',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Finalizar Conteo? ( Programación:' . $model->idProgramacion . 
                                                                                        ' - Orden de Compra: ' . $model->numeroOrdenCompra . 
                                                                                        ' - Item: ' . $model->item . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },

                    'habilitarconteo' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-reply"></i>', 
                                [   'habilitarconteoprogramacion', 'idprogramacion' => $model->idProgramacion], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Habilitar Conteo',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Habilitar Este Conteo? ( OC:' . $model->codigoTipoDocumento . '-' .
                                                                                        $model->numeroOrdenCompra .  ' - Item:' .
                                                                                        $model->item . ' - Usuario:' . 
                                                                                        $model->nombreUsuario . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },

                ],

            ],
        ],

        'rowOptions' => function ($model, $key, $index, $grid) use ($estadofinal) {
            $options = [];

            if ($model->idEstadoConteo == $estadofinal){
                $options['style'] = 'background-color: #B7E3A8;'; // Puedes cambiar el color aquí
            }

            return $options;
        },      
    ]); ?>


</div>
