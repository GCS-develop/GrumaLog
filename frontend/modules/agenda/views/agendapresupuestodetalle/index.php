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

    .izquierda {
        text-align: left;
    }

    .derecha {
        text-align: right;
    }
');

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Agendapresupuestodetalle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\models\ProcedimientosGenerales;

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendapresupuestodetalleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Consolidado: ' . $model->periodoAnio . ' - ' . ProcedimientosGenerales::nombreMes($model->periodoMes);
$this->params['breadcrumbs'][] = ['label' => 'Presupuesto Agenda', 'url' => ['/agenda/agendapresupuesto/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agendapresupuestodetalle-index">

    <div class="row">
        <div class="col-lg-4 derecha">
        <p>
            <?= Html::a('Resumen x Semana', ['/agenda/agendapresupuestosemana/index', 
                                                'idagendapresupuesto' => $model->id], ['class' => 'btn btn-success btn-lg btn-create']) ?>
        </p>
        </div>

        <div class="col-lg-4 centrar">
        <p>
            <?= Html::a('Resumen x Categoría', ['/agenda/agendapresupuestocategoria/index',
                                                'idagendapresupuesto' => $model->id], ['class' => 'btn btn-success btn-lg btn-create']) ?>
        </p>
        </div>

        <div class="col-lg-4 izquierda">
        <p>
        <?= Html::a('Resumen x SubCategoría', ['/agenda/agendapresupuestosubcategoria/index',
                                                'idagendapresupuesto' => $model->id], ['class' => 'btn btn-success btn-lg btn-create']) ?>
        </p>
        </div>
    </div>   

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            //'idAgendaPresupuesto',
            'codigo',
            'categoria',
            'subcategoria',
            'consumidor',
            'universo',
            'producto',
            'tendencia',
            'talla',
            'tipo',
            'modelo',
            'cantidad',
            'fechaLlegada',
            'crossDocking',
        ],
    ]); ?>


</div>
