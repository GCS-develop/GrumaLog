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

    .titulonombre {
        color: black;
        font-weight: bold;
        font-size: 20px;
    }
');

use frontend\modules\ventas\models\Facturaitem;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\search\FacturaitemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Factura items';
$this->params['breadcrumbs'][] = ['label' => 'Facturas', 'url' => ['/ventas/factura/index']];
$this->params['breadcrumbs'][] = $this->title;

$total = Facturaitem::numeroItems($modelfactura->id);
$totalUnidades_Formato = number_format($total, 0, '.', ',');

$total = Facturaitem::totalPesos($modelfactura->id);
$totalPesos_Formato = number_format($total, 2, '.', ',');

?>
<div class="facturaitem-index">

    <?= Alert::widget() ?>

    <div class="row">
        <div class="col-lg-3 titulonombre">
            <?= Html::encode('Documento: ' . $modelfactura->prefijoDocumentoProveedor . '-' .
                                            $modelfactura->consecutivoDocumentoProveedor) ?>
        </div>

        <div class="col-lg-3 titulonombre">
            <?= Html::encode('Proveedor: ' . $modelfactura->proveedor->razonSocial) ?>
        </div>

        <div class="col-lg-3 titulonombre">
            <?= Html::encode('No. Items: ' . $totalUnidades_Formato) ?>
        </div>

        <div class="col-lg-3 titulonombre">
            <?= Html::encode('Total Documento: ' . $totalPesos_Formato) ?>
        </div>
    </div>

    <?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

    <?php echo $this->render('_search', ['model' => $searchModel, 'idfactura' => $modelfactura->id]); ?>

    <?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

    <div class="row">
        <div class="col-lg-6 derecha">
            <?= Html::a('Sincronizar Bodegas', ['sincronizarbodega', 'idfactura' => $modelfactura->id], ['class' => 'btn btn-success btn-lg btn-create']) ?>
        </div>

        <div class="col-lg-6 izquierda">
            <?= Html::a('Ver Archivo Transferencia', ['/ventas/transferencia/index', 'idfactura' => $modelfactura->id], ['class' => 'btn btn-success btn-lg btn-create']) ?>
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
            //['class' => 'yii\grid\SerialColumn'],

            //'id',
            //'idFactura',
            'codigoBarra',
            'item',
            'referencia',
            'descripcion',
            'color',
            'talla',
            [
                'attribute' => 'precioUnitario', // Nombre del atributo en el modelo
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                //'pageSummary' => true,
            ],

            [
                'attribute' => 'totalUnidadesFactura', // Nombre del atributo en el modelo
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'totalUnidadesSiesa', // Nombre del atributo en el modelo
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'totalFactura', // Nombre del atributo en el modelo
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'value' => function($model){
                    return $model->totalUnidadesFactura * $model->precioUnitario;
                },
                'pageSummary' => true,
            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                //'template' => '{update} {updatedatabasic} {change} {cancel} {delete}',
                'template' => '{detalle} {delete}',

                'buttons' => [

                    'detalle' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-list"></i>',
                                [   '/ventas/facturadetalle/index',
                                    'idfacturaitem' => $model->id
                                ], 
                                [
                                    'title' => 'Visualizar Detalle Ventas',
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
                                        'confirm' => 'Esta Seguro de Eliminar Este Registro? ( OC:' . $model->codigoBarra . '-' . 
                                                                                        $model->item . '-' .
                                                                                        $model->color . '-' .
                                                                                        $model->talla .  ' - Unidades:' .
                                                                                        $model->totalUnidadesFactura . ' - Precio: ' .
                                                                                        $model->precioUnitario .  ' )',
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
