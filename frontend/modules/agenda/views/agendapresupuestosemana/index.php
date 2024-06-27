<?php

// Definir el estilo CSS directamente en la vista
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
');

use frontend\models\Agendapresupuestosemana;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\models\ProcedimientosGenerales;

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendapresupuestosemanaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Agenda Presupuesto Semana: ' . $model->periodoAnio . ' - ' . ProcedimientosGenerales::nombreMes($model->periodoMes);
$this->params['breadcrumbs'][] = ['label' => 'Consolidado', 'url' => ['/agenda/agendapresupuestodetalle/index', 'idagendapresupuesto' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agendapresupuestosemana-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

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
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '2%',
            ],

            //'id',
            //'idAgendaPresupuesto',
            //'periodoAnio',
            //'periodoMes',
            
            [
                'attribute' => 'crossDocking', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '15%',
            ],

            [
                'attribute' => 'categoria', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '20%',
            ],

            [
                'attribute' => 'numeroSemanaAnio', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'format' => ['decimal', 0], // Formato decimal con 2 decimales
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '10%',
            ],

            [
                'attribute' => 'cantidad', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'format' => ['decimal', 0], // Formato decimal con 2 decimales
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '10%',
                'pageSummary' => true
            ],

            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Agendapresupuestosemana $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
        ],
    ]); ?>


</div>
