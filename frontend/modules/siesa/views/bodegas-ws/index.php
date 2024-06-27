<?php

$this->registerCss('
    .mi-gridview {
        font-size: 10px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

	.btn-create {
        width: 300px;
    }    
');

use common\models\BodegasWs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var common\models\search\BodegasSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Bodegas - ERP';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="bodegas-index">

    <?= Alert::widget() ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p align="center">
        <?= Html::a('Sincronizar ERP', ['sincronizarerp'], ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </p>    

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

            'Codigo_bodega',
            'Descripcion_bodega',
            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, BodegasWs $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'Codigo_Bodega' => $model->Codigo_Bodega]);
                 }
            ],*/
        ],
    ]); 
	?>


</div>
