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

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendaentregamercanciaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Conteo Recibo Mercancía';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="agendaentregamercancia-index">

    <?= Alert::widget() ?>

    <?php echo $this->render('_search_conteo_agenda', ['model' => $searchModel]); ?>

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

            [
                'attribute' => 'id', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'fechaCita', // Nombre del atributo en el modelo
                //'label' => 'Desde', // Etiqueta de la columna
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
                'attribute' => 'nit', // Nombre del atributo en el modelo
                'label' => 'Nit', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],
            [
                'attribute' => 'razonSocial', // Nombre del atributo en el modelo
                'label' => 'Razón Social', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'totalCantidadPendiente', // Nombre del atributo en el modelo
                'label' => 'UND OC', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'value' => function ($model) {
                    return Ordendecompradetalle::totalCantidadPendiente($model->idOrdenCompra, $model->idCategoria);
                },
                'pageSummary' => true,
            ],

            [
                'attribute' => 'unidades', // Nombre del atributo en el modelo
                'label' => 'UND Asignadas', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'value' => function ($model) {
                    return Programacionentregamercancia::totalUnidadesAsignadas ($model->id);
                    //return array_sum(array_column($model->programacionentregamercancia, 'unidadesAsignadas'));
                },
                'pageSummary' => true,
            ],

            [
                'attribute' => 'unidadesConteo', // Nombre del atributo en el modelo
                'label' => 'UND Conteo', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'value' => function ($model){
                    return Conteoentregamercancia::totalCantidadConteo (null,null,null, $model->id);
                },
                'pageSummary' => true,
            ],

            /*[
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
            */
 
            //'numeroGuia',
            //'observacion',
            [
                'attribute' => 'idEstadoConteo', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->estadoconteo->nombre;
                }
            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{view} {end}',

                'buttons' => [

                    'view' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-search"></i>',
                                [   'indexconteoprogramacion', 'idagenda' => $model->id, ], 
                                [
                                    'title' => 'Ver Programación Conteos',
                                    'class' => 'btn btn-default btn_asignar_cita',
                                ]
                        );
                    },

                    'end' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-window-close"></i>', 
                                [   '/programacion/conteoentregamercancia/indexagenda', 
                                                            'idagenda' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Finalizar Conteo',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Finalizar Conteo? ( Radicado:' . $model->id . 
                                                                                        ' - Serie: ' . $model->codigoTipoDocumento . 
                                                                                        ' - Orden de Compra: ' . $model->numeroOrdenCompra . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },

                    /*'index' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-users"></i>',
                                [   'index', 'id' => $model->id, ], 
                                [
                                    'title' => 'Asignar Personas Entrega Mercancía',
                                    'class' => 'btn btn-default btn_asignar_cita',
                                ]
                        );
                    },*/

                ],

            ],
        ],
    ]); ?>


</div>
