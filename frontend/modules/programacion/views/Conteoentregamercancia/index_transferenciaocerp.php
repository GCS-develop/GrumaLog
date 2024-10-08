<?php

$this->registerCss('
    .mi-gridview {
        font-size: 9px; /* Ajusta el tamaño de la fuente según sea necesario */
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

use frontend\models\Transferenciaordencompraexcel;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\TransferenciaordencompraexcelSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Transferencia SIESA';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transferenciaordencompraexcel-index">

    <div class="row">
        <div class="col-lg-12 centrar"> 
            <?= Html::a('Ejecutar Transferencia', [   '/siesa/transferenciaerp/transferencia', 
                                                'id' => $idtransferenciaerp,
                                                'origen' => 'Conteo'
                                            ], 
                                            [
                                                'class' => 'btn btn-success btn-lg btn-create',
                                            ]) ?>
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

        'showPageSummary' => true,

        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            //'id',
            //'idTransferenciaerp',
            'centroOperacionDocumento',
            'tipoDocumento',
            'consecutivoDocumento',
            'fechaDocumento',
            'tercero',
            'numeroFactura',
            'sucursal',
            'idTerceroComprador',
            'consignacion',
            'centroOperacionOrdenCompra',
            'tipoDocumentoOrdenCompra',
            'consecutivoOrdenCompra',
            'centroOperacionMovimiento',
            'tipoDocumentoMovimiento',
            'consecutivoMovimiento',
            'numeroRegistroMovimiento',
            'bodegaMovimiento',
            'unidadMovimiento',
            'fechaEntregaMovimiento',
            'cantidadBase',
            'item',
            'color',
            'talla',
            'rowid',
            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Transferenciaordencompraexcel $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
        ],
    ]); ?>


</div>
