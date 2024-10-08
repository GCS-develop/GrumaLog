
<?php

// Definir el estilo CSS directamente en la vista
$this->registerCss('
    .mi-gridview {
        font-size: 10px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

	.btn-create {
        width: 300px;
    }    
');

use common\models\OrdenesCompraWs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var common\models\search\OrdenesCompraWsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Orden de Compra';
$this->params['breadcrumbs'][] = $this->title;

$co = null;
if (isset($params["OrdenesCompraWsSearch"]["CentroOperacion"])) {
    $co = $params["OrdenesCompraWsSearch"]["CentroOperacion"];
}

$tipodocumento = null;
if (isset($params["OrdenesCompraWsSearch"]["TipoDocumento"])) {
    $tipodocumento = $params["OrdenesCompraWsSearch"]["TipoDocumento"];
}

$consecutivo = null;
if (isset($params["OrdenesCompraWsSearch"]["Consecutivo"])) {
    $consecutivo = $params["OrdenesCompraWsSearch"]["Consecutivo"];
}

?>
<div class="ordenescompraws-index">

	<?= Alert::widget() ?> 

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

	<p align="center">
        <?= Html::a('Sincronizar ERP', ['sincronizarerp', 
										'co' => $co, 
										'tipodocumento' => $tipodocumento, 
										'consecutivo' => $consecutivo
										], ['class' => 'btn btn-success btn-lg btn-create']) 
		?>
    </p>   

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

			[
				'attribute' => 'CentroOperacion',
				'label' => 'CO',
			],				[
				'attribute' => 'TipoDocumento',
				'label' => 'Tipo Documento',
			],	
			[
				'attribute' => 'Consecutivo',
				'label' => 'Consecutivo',
			],	
			[
				'attribute' => 'Fecha',
				'label' => 'Fecha',
			],			
			[
				'attribute' => 'IdTercero',
				'label' => 'Tercero',
			],			
			[
				'attribute' => 'RazonSocial',
				'label' => 'Razon Social',
			],		
			[
				'attribute' => 'Estado_Doc',
				'label' => 'Estado',
			],
			[
				'attribute' => 'Item',
				'label' => 'Item',
			],
			[
				'attribute' => 'Referencia_Item',
				'label' => 'Referencia',
			],
			[
				'attribute' => 'Descripcion_Item',
				'label' => 'Descripción',
			],
			[
				'attribute' => 'CodigoBarras',
				'label' => 'Código Barras',
			],
			[
				'attribute' => 'CantidadPedida',
				'label' => 'Pedida',
                'format' => ['decimal', 0],
			],
			[
				'attribute' => 'CantidadEntrada',
				'label' => 'Entrada',
                'format' => ['decimal', 0],
			],
			[
				'attribute' => 'CantidadPendiente',
				'label' => 'Pendiente',
                'format' => ['decimal', 0],
			],
			[
				'attribute' => 'UnidadMedida',
				'label' => 'UM',
			],
			[
				'attribute' => 'Fecha_Entrega',
				'label' => 'Fecha Entrega',
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
                'urlCreator' => function ($action, OrdenesCompraWs $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'Item' => $model->Item]);
                 }
            ],*/
        ],
    ]); 
	?>


</div>
