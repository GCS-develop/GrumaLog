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

use frontend\models\Transferenciatransitoexcel;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\TransferenciatransitoexcelSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Transferencia SIESA';
$this->params['breadcrumbs'][] = ['label' => 'Traspaso CDSC', 'url' => ['/crossdocking/conteocdscdestinofactura/indextraspaso']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transferenciatransitoexcel-index">

    <div class="row">
        <div class="col-lg-12 centrar">
            <?= Html::a(
                'Ejecutar Transferencia',
                [
                    '/siesa/transferenciaerp/transferencia',
                    'id' => $idtransferenciatraspasoerp,
                    'origen' => 'Traspaso CDSC',
                    'idconteofactura' => $idconteofactura
                ],
                [
                    'class' => 'btn btn-success btn-lg btn-create',
                ]
            ) ?>

        </div>
    </div>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php
        $totalCantidad = Transferenciatransitoexcel::getTotalUnidadesConteo($idtransferenciatraspasoerp);
        $totalConteoEmpaque = Transferenciatransitoexcel::getTotalUnidadesConteoEmpaque( $idtransferenciatraspasoerp);

        //var_dump($idtransferenciatraspasoerp); die("hola");
        echo $idtransferenciatraspasoerp;
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,

        'beforeRow' => function ($model, $key, $index, $grid) use ($totalCantidad, $totalConteoEmpaque) {
            if ($index === 0) { // Primera fila de cada página
                return "<tr><td colspan='10'><strong>Gran Total</strong></td><td><strong>$totalCantidad</strong></td><td colspan='5'></td><td><strong>$totalConteoEmpaque</strong></td></tr>";
            }
        },
    
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
            'fechaDocumento',
            'bodegaSalidaDocumento',
            'bodegaEntradaDocumento',
            'centroOperacion',
            'tipoDocumentoMovimiento',
            'bodegaSalidaMovimiento',
            'centroOperacionMovimiento',
            'unidadSalida',
            [
                'attribute' => 'cantidadBase',
                'pageSummary' => true,
            ],
            'costoPromedioUnitario',
            'item',
            'color',
            'talla',
            'numero',

            //'codigoUnidadEmpaque',
            [
                'attribute' => 'unidadesConteoEmpaque',
                'pageSummary' => true,
            ],
 
            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, transferenciatransitoexcel $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
        ],
    ]); ?>


</div>