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

use frontend\models\Transferenciaerperror;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\TransferenciaerperrorSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Errores Ejecutar WS Transferencia';
$this->params['breadcrumbs'][] = ['label' => 'Transferencia ERP', 'url' => ['/siesa/transferenciaerp/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transferenciaerperror-index">

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
            //'idTransferenciaerp',
            'centroOperacionDocumento',
            'tipoDocumento',
            'fechaDocumento',
            'numero',
            'numeroLinea',
            'tipoRegistro',
            'subTipoRegistro',
            'version',
            'nivel',
            'valor',
            'detalle:ntext',
            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Transferenciaerperror $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
        ],
    ]); ?>


</div>
