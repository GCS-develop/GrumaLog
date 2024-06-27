<?php

$this->registerCss('
    .mi-gridview {
        font-size: 14px; /* Ajusta el tamaño de la fuente según sea necesario */
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

use frontend\modules\ventas\models\Facturadetalle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\search\FacturadetalleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Items de Factura';
$this->params['breadcrumbs'][] = ['label' => 'Facturas', 'url' => ['/ventas/factura/index']];
$this->params['breadcrumbs'][] = $this->title;

$totalDocumentoOK = Facturadetalle::totalDocumentoOK ($modelfactura->id);
if ($totalDocumentoOK == null){
    $totalDocumentoOK = 0;
}
$formateadoOK = number_format($totalDocumentoOK, 2, ',', '.');

$totalDocumentoError = Facturadetalle::totalDocumentoError ($modelfactura->id);
if ($totalDocumentoError == null){
    $totalDocumentoError = 0;
}
$formateadoError = number_format($totalDocumentoError, 2, ',', '.');

?>
<div class="facturadetalle-index">

    <?php echo $this->render('_search', ['model' => $searchModel, 'idfactura' => $modelfactura->id]); ?>

    <?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

    <div class="row">
        <div class="col-lg-4 titulonombre">
            <?= Html::encode('Total Documento OK: ' . $formateadoOK) ?>
        </div>

        <div class="col-lg-4 titulonombre">
            <?= Html::encode('Total Documento Error: ' . $formateadoError) ?>
        </div>
    </div>

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
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
            ],

            //'id',
            //'idFactura',

            [
                'attribute' => 'codigoBarra', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '10%',
            ],
            [
                'attribute' => 'item', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
            ],

            [
                'attribute' => 'referencia', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '10%',
            ],
            [
                'attribute' => 'descripcion', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '15%',
            ],

            [
                'attribute' => 'color', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '10%',
            ],
            [
                'attribute' => 'talla', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
            ],

            [
                'attribute' => 'unidadMedida', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
            ],
            /*[
                'attribute' => 'bodega', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
            ],
            [
                'attribute' => 'motivo', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
            ],*/

            [
                'attribute' => 'cantidadBase', // Nombre del atributo en el modelo
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'width' => '5%',
                'pageSummary' => true,
            ],

            [
                'attribute' => 'precioUnitario', // Nombre del atributo en el modelo
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'width' => '5%',
            ],

            [
                'attribute' => 'total', // Nombre del atributo en el modelo
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'value' => function ($model){
                    return $model->cantidadBase * $model->precioUnitario;
                },
                'width' => '5%',
                'pageSummary' => true,
            ],

            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Facturadetalle $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
        ],
    ]); ?>


</div>
