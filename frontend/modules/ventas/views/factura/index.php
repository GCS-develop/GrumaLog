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

use frontend\modules\ventas\models\Factura;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use yii\bootstrap4\Modal;
use common\widgets\Alert;
use common\models\ProcedimientosGenerales;


/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\search\FacturaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Facturas';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
    Modal::begin([                
        'title'=>'<h4>Registro datos básicos factura proveedor</h4>',
        'id'=>'modaldata',
        'size'=>'modal-lg',
        'options' => [
            'tabindex' => false  // Importante para que funcione el Select
        ]
    ]);
        
    echo "<div id='modalContentData'></div>";
        
    Modal::end(); 
?>

<div class="factura-index">

    <div class="row">
        <div class="col-lg-12 centrar">
            <?php $url = Url::to(['create']); ?>

            <p>
            <?= Html::button('Registrar', 
                        ['value'=>  $url, 'class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreate']) 
            ?>
            </p>
        </div>
    </div>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

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
                'class' => 'yii\grid\SerialColumn'
            ],

            //'id',
            [
                'attribute' => 'documentoSIESA',
                'label' => 'Documento SIESA',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->centroOperacion . ' - ' . $model->tipoDocumento . ' - ' . $model->consecutivoDocumento;
                }
            ],

            [
                'attribute' => 'fechaDocumento',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'fechaDesde',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'fechaHasta',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'idProveedor',
                'label' => 'Proveedor',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->proveedor->codigo . ' - ' . $model->proveedor->nit . ' - ' . $model->proveedor->razonSocial;
                }
            ],

            //'idProveedor',
            [
                'attribute' => 'codigoSucursal',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'documentoProveedor',
                'label' => 'Documento Proveedor',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->prefijoDocumentoProveedor . ' - ' . $model->consecutivoDocumentoProveedor;
                }
            ],

            [
                'attribute' => 'fechaDocumentoProveedor',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            //'condicionPago',
            //'tipoProveedor',
            [
                'attribute' => 'valorDocumento', // Nombre del atributo en el modelo
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],
            //'porcentajeCuota',
            //'fechaVencimientoCuota',
            //'fechaProntoPago',

            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                //'template' => '{update} {updatedatabasic} {change} {cancel} {delete}',
                'template' => '{update} {detalle} {delete}',

                'buttons' => [

                    'update' => function ($url, $model) {                                
                        $t = Url::to([  'update', 
                                        'id' => $model->id
                                    ]);

                        return Html::button('<i class="fa fa-edit"></i>',[
                                    'value'=> $t,
                                    'title' => 'Actualizar Datos Factura',
                                    'class' => 'btn btn-default btn_update',
                        ]);
                    },

                    'detalle' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-list"></i>',
                                [   '/ventas/facturaitem/index',
                                    'idfactura' => $model->id
                                ], 
                                [
                                    'title' => 'Ver Items de la Factura',
                                    'class' => 'btn btn-default btn_detalle',
                                ]
                        );
                    },

                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Registro',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar Este Registro? ( OC:' . $model->proveedor->razonSocial . '-' . 
                                                                                        $model->prefijoDocumentoProveedor . '-' .
                                                                                        $model->consecutivoDocumentoProveedor .  ' - Fecha Cita:' .
                                                                                        $model->fechaDocumentoProveedor . ' )',
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
