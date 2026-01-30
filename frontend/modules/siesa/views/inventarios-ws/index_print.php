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
use common\widgets\Alert;


/** @var yii\web\View $this */
/** @var common\models\search\InventariosWsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Inventarios';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="inventarios-index">

	<?php echo $this->render('_search-print', ['model' => $searchModel]); ?>

	<?= Alert::widget() ?>

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
				'attribute' => 'PrecioVenta',
				'label' => 'Precio Venta',
				'value' => function ($model) {
					return \common\models\InventariosWs::getPrecioValor($model);
				},
				'format' => ['currency'],
				'contentOptions' => ['style' => 'text-align:right;'],
			],


			// [
			// 	'label' => 'Campo precio',
			// 	'value' => function ($model) {
			// 		return \common\models\InventariosWs::getPrecioCampo($model);
			// 	},
			// 	'contentOptions' => ['style' => 'font-size:10px; color:#777;'],
			// ],





			// En index_print.php, dentro de 'columns' => [ ... ]
			[
				'class' => \yii\grid\ActionColumn::class,
				'header' => 'Acción',
				'template' => '{print} {printajax} {printajaxhalf}',
				'buttons' => [
					'print' => function ($url, $model) {
						// OJO: $model es array
						$ean     = $model['EAN'] ?? null;
						$exist   = (int)($model['CantidadExistente'] ?? 0);
						$precio  = (float)($model['CostoPromedioUnitario'] ?? 0);
						$printUrl = \yii\helpers\Url::to(['print']); // enviamos ean por GET

						return Html::a(
							'<i class="fa fa-print text-danger"></i>',
							['print', 'ean' => $ean],     // ✅ ruta relativa al mismo controlador
							[
								'class' => 'btn btn-default',
								'title' => 'Imprimir TODAS las existencias',
								'data' => [
									'confirm' => "¿Imprimir todas las existencias? cantidad: {$exist} EAN: {$ean}",
									'method'  => 'post',
								],
							]
						);
					},
					'printajax' => function ($url, $model) {
						$ean    = $model['EAN'] ?? null;
						// usar el mismo valor que ve el usuario en la columna "Precio Venta"
						$precio = \common\models\InventariosWs::getPrecioValor($model);
						$printUrl = \yii\helpers\Url::to(['printajax']); // misma acción AJAX

						return \yii\helpers\Html::a(
							'<i class="fa fa-print text-success"></i>',
							'#',
							[
								'class' => 'btn btn-default',
								'title' => 'Imprimir',
								'onclick' => "openPrintModal('{$ean}', '{$precio}', '{$printUrl}')",
							]
						);
					},

					'printajaxhalf' => function ($url, $model) {
						$ean          = $model['EAN'] ?? null;
						$precioFull   = \common\models\InventariosWs::getPrecioValor($model);
						$precioMitad  = $precioFull / 2;
						$printUrl     = \yii\helpers\Url::to(['printajax']);

						return \yii\helpers\Html::a(
							'<i class="fa fa-print text-info"></i>',
							'#',
							[
								'class' => 'btn btn-default',
								'title' => 'Imprimir a mitad de precio',
								'onclick' => "openPrintModal('{$ean}', '{$precioMitad}', '{$printUrl}')",
							]
						);
					},

				],
			],

		],
	]); ?>


</div>

<link rel="stylesheet" href="css/shared.css">
<link rel="stylesheet" href="<?= Yii::$app->request->baseUrl ?>/css/swal.css">
<script src="<?= Yii::$app->request->baseUrl ?>/js/sweetalert2@11.js"></script>
<script>
	function openPrintModal(ean, precio, url) {
		Swal.fire({
			title: '¿Cuántos precios desea imprimir?',
			input: 'number',
			inputAttributes: {
				min: 1
			},
			showCancelButton: true,
			confirmButtonText: 'Aceptar',
			cancelButtonText: 'Cancelar',
			buttonsStyling: false,
			inputValidator: (v) => {
				if (!v) return '¡Debes ingresar un valor!';
			}
		}).then((r) => {
			if (r.isConfirmed) {
				Swal.fire({
					title: 'Imprimiendo...',
					allowOutsideClick: false,
					didOpen: () => Swal.showLoading()
				});
				$.post(url, {
						ean: ean,
						precio: precio,
						input: r.value
					})
					.done(function(resp) {
						Swal.close();
						if (resp.status === 'success') Swal.fire('¡Éxito!', resp.message, 'success');
						else Swal.fire('¡Error!', resp.message || 'Error desconocido', 'error');
					})
					.fail(function() {
						Swal.close();
						Swal.fire('¡Error!', 'Hubo un problema con la conexión.', 'error');
					});
			}
		});
	}
</script>