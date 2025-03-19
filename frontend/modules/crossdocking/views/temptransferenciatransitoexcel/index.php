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

    .titulo {
        color: black;
        font-weight: bold;
        font-size: 18px;
    }

    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc; /* Color y grosor de la línea */
        margin: 10px 0; /* Espacio alrededor de la línea */
    }
');

use frontend\models\Temptransferenciatransitoexcel;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\TemptransferenciatransitoexcelSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

// echo $modeldestino->centrooperacion->nombre;

$this->title = 'Transferencia SIESA';
$this->params['breadcrumbs'][] = ['label' => 'Conteo por Destino', 
                                    'url' => ['/crossdocking/conteocdscdestino/indextraspaso',
                                            'idconteofactura' => $modeldestino->idConteocdscdestinofactura
                                            ]];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
        $totalCantidad = Temptransferenciatransitoexcel::getTotalUnidadesConteo($idtransferenciaerp);
        $totalConteoEmpaque = Temptransferenciatransitoexcel::getTotalUnidadesConteoEmpaque( $idtransferenciaerp);
?>

<div class="temptransferenciatransitoexcel-index">

    <div class="row">
        <div class="col-lg-2 titulo">
            <?= Html::encode('OC: ' . $modeldestino->factura->ordenCompra->tipoDocumento->codigo . '-' . $modeldestino->factura->ordenCompra->consecutivo) ?>
        </div>

        <div class="col-lg-2 titulo">
            <?= Html::encode('Factura: ' . $modeldestino->factura->numeroFactura) ?>
        </div>

        <div class="col-lg-4 titulo">
            <?= Html::encode('Bodega: ' . $modeldestino->centrooperacion->nombre) ?>
        </div>
    </div>

    <?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

    <div class="row">
        <div class="col-lg-12 centrar">
            <?= Html::a(
                'Ejecutar Transferencia',
                [
                    '/crossdocking/temptransferenciatransitoexcel/ejecutartransferencia',
                    'id' => $idtransferenciaerp,
                    'origen' => 'Traspaso CDSC',
                    'idconteofactura' => $modeldestino->idConteocdscdestinofactura,
                    'idconteodestino' => $modeldestino->id
                ],
                [
                    'class' => 'btn btn-success btn-lg btn-create',
                ]
            ) ?>

        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,

        'beforeRow' => function ($model, $key, $index, $grid) use ($totalCantidad, $totalConteoEmpaque) {
            if ($index === 0) { // Primera fila de cada página
                return "<tr><td colspan='11'><strong>Gran Total</strong></td><td><strong>$totalCantidad</strong></td><td colspan='5'></td><td><strong>$totalConteoEmpaque</strong></td></tr>";
            }
        },
    
        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options' => [
            'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
        ],

        'showPageSummary' => true,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn'
            ],

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
            [
                'attribute' => 'unidadesConteoEmpaque',
                'pageSummary' => true,
                'label' => 'No. UND Emp.',
            ],
            [
                'attribute' => 'codigoUnidadEmpaque',
                'label' => 'UND Emp.',
            ],
            /*'procesado',
            'fila',
            'notas',
            'codigoBarras',
            
            'unidadesConteoEmpaque',
            */
            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Temptransferenciatransitoexcel $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
        ],
    ]); ?>


</div>
