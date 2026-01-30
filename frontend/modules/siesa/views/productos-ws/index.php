
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

use common\models\ProductosWs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var common\models\search\ProductosWsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Productos';
$this->params['breadcrumbs'][] = $this->title;

$item = null;
if (isset($params["ProductosWsSearch"]["Item"])) {
    $item = $params["ProductosWsSearch"]["Item"];
}

?>
<div class="productosws-index">

	<?= Alert::widget() ?>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <!-- <p align="center">
        <?= Html::a('Sincronizar ERP', ['sincronizarerp', 'item' => $item], ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </p>     -->

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

			'CodigoBarras',
			'Item', 
            'Referencia', 
			'Descripcion', 
			[
				'attribute' => 'UM',
				'label' => 'UM',
			],

			[
				'attribute' => 'Equivalencia_UM',
				'label' => 'Equiv. UM',
			],
			//'Ext_1', 
			
			[
				'attribute' => 'Descripcion_Ext_1',
				'label' => 'Talla',
			],				
			//'Ext_2', 
			
			[
				'attribute' => 'Descripcion_Ext_2',
				'label' => 'Color',
			],			
			
			[
				'attribute' => 'criterio_PROVEEDOR',
				'label' => 'Proveedor',
			],
			[
				'attribute' => 'criterio_CATEGORIA',
				'label' => 'Categoría',
			],
			[
				'attribute' => 'criterio_SUBCATEGORIA',
				'label' => 'SubCategoría',
			],
			[
				'attribute' => 'criterio_Producto',
				'label' => 'Producto',
			],
			[
				'attribute' => 'criterio_Marca',
				'label' => 'Marca',
			],

			[
				'attribute' => 'UnidadOrden',
				'label' => 'Unidad Orden',
			],

			[
				'attribute' => 'UnidadEmpaque',
				'label' => 'Unidad Empaque',
			],

			[
				'attribute' => 'Estado',
				'label' => 'Estado',
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
                'urlCreator' => function ($action, ProductosWs $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'Item' => $model->Item]);
                 }
            ],*/
        ],
    ]); 
	?>


</div>
