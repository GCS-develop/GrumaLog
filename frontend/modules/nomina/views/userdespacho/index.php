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
');

use frontend\models\Userdespacho;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\UserdespachoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Usuarios Despacho';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="userdespacho-index">

    <!--
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Userdespacho', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    -->

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
                'width' => '5%',
            ],

            [
                'attribute' => 'identificacion', 
                'format' => ['decimal', 0], 
                'hAlign' => 'right', 
                'vAlign' => 'middle', 
                'width' => '15%',
            ],

            [
                'attribute' => 'nombreEmpleado', 
                'label' => 'Nombre Empleado',
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
                'width' => '35%',
            ],
            
            [
                'attribute' => 'username', 
                'label' => 'Nombre Usuario',
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
                'width' => '15%',
            ],  

            [
                'attribute' => 'status',
                'label' => 'Estado',
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
                'filter' => ['0' => 'Inactivo', '10' => 'Activo'],
                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Seleccione una opción'],
                'value' => function($model){
                    return $model->status != 10 ? 'Inactivo' : 'Activo';
                },
                'width' => '15%',
            ],

            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Userdespacho $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
        ],
    ]); ?>


</div>
