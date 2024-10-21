<?php 

$this->registerCss('
    .mi-gridview {
        font-size: 10px; /* Ajusta el tamaño de la fuente según sea necesario */
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

    .titulo-seccion-FAA {
        background-color: #FFC658;
        border: none;
    } 

    .group-heading {
        background-color: #f0f0f0; /* Color de fondo para el encabezado del grupo */
        font-weight: bold; /* Hace que el texto del encabezado sea negrita */
        font-size: 16px; /* Tamaño de fuente para el encabezado */
        /* Añade otros estilos según sea necesario */
    }
');

use yii\helpers\Html;
//use yii\widgets\DetailView;
use kartik\grid\GridView;
use kartik\detail\DetailView;

$titulo = 'PLANILLA DE EMBARQUE GRUMA PRINCIPAL No '. $planillaembarque->id;

?>

<?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

<?php
$attributes = [

    [
        'columns' => [
            [
                'attribute' => 'fechaDespacho',
                'label' => 'Fecha',
                'displayOnly' => true,
                'labelColOptions' => ['style' => 'width:10%'],
                'valueColOptions' => ['style' => 'width:15%'],
                'value' => $planillaembarque->fechaDespacho . ' ' . $planillaembarque->horaDespacho,
            ],
            [
                'attribute' => 'nombreConductor',
                'label' => 'Conductor',
                'displayOnly' => true,
                'labelColOptions' => ['style' => 'width:10%'],
                'valueColOptions' => ['style' => 'width:25%'],
                'value' => $planillaembarque->idConductor . ' ' . $planillaembarque->nombreConductor,
            ],

            [
                'attribute' => 'idVehiculo',
                'label' => 'Vehículo',
                'displayOnly' => true,
                'labelColOptions' => ['style' => 'width:5%'],
                'valueColOptions' => ['style' => 'width:30%'],
                'value' => $planillaembarque->placa,
            ],

            [
                'attribute' => 'sello',
                'label' => 'Sello',
                'displayOnly' => true,
                'labelColOptions' => ['style' => 'width:5%'],
                'valueColOptions' => ['style' => 'width:10%'],
                'value' => $planillaembarque->sello,
            ],

        ],
    ],

];
?>

<?php

$gridColumns = [
    // 'id',
    // 'idPlanillaEmbarque',
    // 'idTraspaso',
    [
        'attribute' => 'consecutivoDocumento',
        'label' => 'Traspaso',
        'value' => function($model){
            return $model->tipoDocumento . ' - ' . $model->consecutivoDocumento;
        }
    ],
    [
        'attribute' => 'almacenOrigen',
        'label' => 'Origen',
        'value' => function($model){
            return $model->codAlmacenOrigen . ' - ' . $model->almacenOrigen;
        }
    ],
    [
        'attribute' => 'almacenDestino',
        'label' => 'Destino',
        'value' => function($model){
            return $model->codAlmacenDestino . ' - ' . $model->almacenDestino;
        }
    ],
    [
        'attribute' => 'unidades',
        'label' => 'Unidades',
        'format' => ['decimal', 0], // Formato decimal con 0 decimales,
        'pageSummary' => true,
    ],
];
?>

<h4 class="centrar"><?= Html::encode($titulo) ?></h4>

<div class="card">
    <div class="card-body">

        <!--
        <?php //if ($imagendocumentoadjunto !== null): ?>
            <img class="centrar" src="<?php // $imagendocumentoadjunto->path_server ?>"
                alt="<?php // $imagendocumentoadjunto->src_filename ?>">
        <?php //endif; ?>
        -->

        <?=
            DetailView::widget([
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                'options' => ['style' => 'font-size:18px;'],
                'model' => $planillaembarque,
                'attributes' => $attributes,
                'mode' => DetailView::MODE_VIEW,
                'bordered' => true,
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'hover' => true,
                'hAlign' => 'left',
                'vAlign' => 'top',
                /*'panel' => [
                    'type' => 'info', // Aquí puedes definir el tipo de panel (por ejemplo: default, primary, success, info, warning, danger)
                    'heading' => '<strong style="font-size: 14px;">INFORMACIÓN DATOS PERSONALES</strong>', // Este será el encabezado del panel
                    //'footer' => '<div class="text-center text-muted">This is a sample footer message for the detail view.</div>', // Este será el pie de página del panel
                    //'headingOptions' => ['class' => 'panel-info'],
                ],*/
                
            ]);
        ?>

<?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'showPageSummary' => true,
        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options' => [
            'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
        ],

        'columns' => array_merge(
            [
                ['class' => 'kartik\grid\SerialColumn'],
            ],
            $gridColumns,
        ),


    ]); ?>
    </div>
</div>
