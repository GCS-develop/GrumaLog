<?php

// Definir el estilo CSS directamente en la vista
$this->registerCss('
    .mi-gridview {
        font-size: 12px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

    .btn-create {
        width: 300px;
    }
    
    .centrar {
        text-align: center;
    }
');

use frontend\models\Transferencialogws;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\TransferencialogwsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'LOG Ejecutar WS Transferencia';
$this->params['breadcrumbs'][] = ['label' => 'Transferencia ERP', 'url' => ['/siesa/transferenciaerp/index']];

$this->params['breadcrumbs'][] = $this->title;

?>
<div class="transferencialogws-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'showPageSummary' => true,

        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'center',
                'vAlign' => 'middle',
            ],

            [
                'attribute' => 'centroOperacionDocumento', // Nombre del atributo en el modelo
                'hAlign' => 'center',
                'vAlign' => 'middle',
            ],
            [
                'attribute' => 'tipoDocumento', // Nombre del atributo en el modelo
                'hAlign' => 'center',
                'vAlign' => 'middle',
            ],

            [
                'attribute' => 'numero', // Nombre del atributo en el modelo
                'hAlign' => 'center',
                'vAlign' => 'middle',
            ],
            [
                'attribute' => 'fechaDocumento', // Nombre del atributo en el modelo
                'hAlign' => 'center',
                'vAlign' => 'middle',
            ],

            //'id',

            [
                'attribute' => 'consecutivoOrdenCompra',
                'hAlign' => 'center',
                'vAlign' => 'middle',
            ],

            [
                'attribute' => 'bodegaSalidaDocumento', // Nombre del atributo en el modelo
                'hAlign' => 'center',
                'vAlign' => 'middle',
                //'visible' => !empty($model->consecutivoOrdenCompra) ? true : false,
            ],
            [
                'attribute' => 'bodegaEntradaDocumento', // Nombre del atributo en el modelo
                'hAlign' => 'center',
                'vAlign' => 'middle',
                //'visible' => !empty($model->consecutivoOrdenCompra) ? true : false,
            ],
            [
                'attribute' => 'startDate',
                'hAlign' => 'center',
                'vAlign' => 'middle',
            ],
            [
                'attribute' => 'endDate',
                'hAlign' => 'center',
                'vAlign' => 'middle',
            ],

            [
                'attribute' => 'diferencia',
                'value' => function ($model){
                    $fecha1 = new DateTime($model->startDate);
                    $fecha2 = new DateTime($model->endDate);

                    $diferencia = $fecha1->diff($fecha2);
                    $segundos = $diferencia->s + ($diferencia->i * 60) + ($diferencia->h * 3600) + ($diferencia->d * 86400);

                    return $segundos;
                },
                'pageSummary' => true,
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],
            [
                'attribute' => 'numeroRegistros', // Nombre del atributo en el modelo
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],
            'mensaje:ntext',
            //'idConectorDinamico',
            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Transferencialogws $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
        ],
    ]); ?>


</div>
