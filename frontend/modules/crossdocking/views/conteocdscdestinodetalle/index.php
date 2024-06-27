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

use frontend\models\Conteocdscdestinodetalle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\detail\DetailView;


/** @var yii\web\View $this */
/** @var frontend\models\search\ConteocdscdestinodetalleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Referencias';

switch ($origen){
    case 'legaliza':
        $this->params['breadcrumbs'][] = ['label' => 'Legalizar - Conteo por Destino', 'url' => ['/crossdocking/conteocdscdestinofactura/indexalmacen', 'idconteofactura' => $modeldestino->idConteocdscdestinofactura, 'origen' => $origen]]; 
        break;
    case 'entrada':
        $this->params['breadcrumbs'][] = ['label' => 'Entrada - Conteo por Destino', 'url' => ['/crossdocking/conteocdscdestinofactura/indexalmacen', 'idconteofactura' => $modeldestino->idConteocdscdestinofactura, 'origen' => $origen]]; 
        break;
    case 'traspaso':
        $this->params['breadcrumbs'][] = ['label' => 'Traspaso - Conteo por Destino', 'url' => ['/crossdocking/conteocdscdestinofactura/indexalmacen', 'idconteofactura' => $modeldestino->idConteocdscdestinofactura, 'origen' => $origen]]; 
        break;
    case 'inicio':
        $this->params['breadcrumbs'][] = ['label' => 'Factura - Conteo por Destino', 'url' => ['/crossdocking/conteocdscdestinofactura/indexalmacen' , 'idconteofactura' => $modeldestino->idConteocdscdestinofactura, 'origen' => $origen]];
        break;
    default :
        $this->params['breadcrumbs'][] = ['label' => 'Factura CDSC', 'url' => ['index']]; 
        break;

}

$this->params['breadcrumbs'][] = $this->title;


?>

<?php
$attributes = [
    [
        'group'=>true,
        'label'=>'SECCIÓN 1: Información Orden de Compra',
        'rowOptions'=>['class'=>'table-info']
    ],
    [
        'columns' => [
            [
                'attribute'=>'nit', 
                'label'=>'Nit Proveedor',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:15%'],
                'value' => $modeldestino->factura->proveedor->nit,
            ],

            [
                'attribute'=>'razonSocial', 
                'label'=>'Nombre Proveedor',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:30%'],
                'value' => $modeldestino->factura->proveedor->razonSocial,
            ],

            [
                'attribute'=>'numeroFactura', 
                'label'=>'Orden de Compra',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:15%'],
                'value' => $modeldestino->factura->numeroFactura,
            ],

        ],
    ],
    [
        'group'=>true,
        'label'=>'SECCIÓN 2: Información Almacén Destino',
        'rowOptions'=>['class'=>'table-info']
    ],
    [
        'columns' => [
            [
                'attribute'=>'nit', 
                'label'=>'Código Almacén',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:15%'],
                'value' => $modeldestino->centrooperacion->codigo,
            ],

            [
                'attribute'=>'razonSocial', 
                'label'=>'Almacén',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:30%'],
                'value' => $modeldestino->centrooperacion->nombre,
            ],

        ],
    ],

];
?>

<div class="conteocdscdestinodetalle-index">

    <div class="row">
        <div class="col-lg-12">

        <p>
        <?=
            DetailView::widget([
                'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '-'],
                'options' => ['style' => 'font-size:14px;'],
                'model' => $modeldestino,
                'attributes' => $attributes,
                'mode' => DetailView::MODE_VIEW,
                'bordered' => true,
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'hover' => true,
                'hAlign'=> 'left',
                'vAlign'=> 'top',
            ]);
        ?>
        </p>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,

        //'filterModel' => $searchModel,
        'summary' => '<h3 style="background-color: #f0f0f0; padding: 10px; text-align: center; margin-bottom: 20px;">REFERENCIAS</h3>',
        //'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],
        
        'showPageSummary' => true,

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'codigoBarras', // Nombre del atributo en el modelo
                'label' => 'EAN', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'idItem', // Nombre del atributo en el modelo
                'label' => 'Item', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->item->item;
                }
            ],

            [
                'attribute' => 'idItem', // Nombre del atributo en el modelo
                'label' => 'Descripción', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->item->descripcion;
                }
            ],
            [
                'attribute' => 'color', // Nombre del atributo en el modelo
                'label' => 'Color', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->item->color->codigo;
                }
            ],
            [
                'attribute' => 'idItem', // Nombre del atributo en el modelo
                'label' => 'Talla', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->item->talla->codigo;
                }
            ],

            [
                'attribute' => 'totalUnidades', // Nombre del atributo en el modelo
                'label' => 'Unidades', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'pageSummary' => true,
            ],

            //'id',
            //'idConteocdscdestino',
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Conteocdscdestinodetalle $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
        ],
    ]); ?>


</div>
