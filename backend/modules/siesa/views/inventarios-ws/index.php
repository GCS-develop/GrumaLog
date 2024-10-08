
<?php

// Definir el estilo CSS directamente en la vista
$this->registerCss('
    .mi-gridview {
        font-size: 10px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }
');

use common\models\InventariosWs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var common\models\search\InventariosWsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Inventarios';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="inventarios-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
		
		'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],
		
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

			'Item', 
            'Referencia', 
			'Descripcion', 
			'EAN',
			[
				'attribute' => 'Extension1',
				'label' => 'Color',
			],				
			
			[
				'attribute' => 'Extension2',
				'label' => 'Talla',
			],			
			
			[
				'attribute' => 'Bodega',
				'label' => 'Cod. Bodega',
			],
			[
				'attribute' => 'NombreBodega',
				'label' => 'Bodega',
			],
			[
				'attribute' => 'CantidadExistente',
				'label' => 'Existencia',
			],
			[
				'attribute' => 'CantidadDisponible',
				'label' => 'Disponible',
			],
			[
				'attribute' => 'CantidadComprometida',
				'label' => 'Comprometida',
			],
			[
				'attribute' => 'CantidadDisponible_POS',
				'label' => 'Disponible POS',
			],
			[
				'attribute' => 'CostoPromedioUnitario',
				'label' => 'Costo Promedio',
			],
			
			/*'columns' => [
				// Otras columnas aquí
				[
					'class' => 'yii\grid\ActionColumn',
					'template' => '{view} {update} {delete}',
					'buttons' => [
						'view' => function ($url, $model, $key) {
							return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['view', 'id' => $model['id']]);
						},
						'update' => function ($url, $model, $key) {
							return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['update', 'id' => $model['id']]);
						},
						'delete' => function ($url, $model, $key) {
							return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model['id']], [
								'data' => [
									'confirm' => 'Are you sure you want to delete this item?',
									'method' => 'post',
								],
							]);
						},
					],
				],
			],
			*/
            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, InventariosWs $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'Item' => $model->Item]);
                 }
            ],*/
        ],
    ]); 
	?>


</div>
