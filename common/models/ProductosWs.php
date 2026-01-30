<?php

namespace common\models;

use Yii;

use yii\base\Model;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\RequestEvent;

use frontend\models\Categoria;
use frontend\models\Subcategoria;
use frontend\models\Talla;
use frontend\models\Color;
use frontend\models\Marca;
use frontend\models\Producto;
use frontend\models\Item;

class ProductosWs extends Model
{

	public $Item;
	public $Referencia;
	public $Descripcion;
	public $UM;
	public $Equivalencia_UM;
	public $Descripcion_Ext_1;
	public $Ext_1;
	public $Descripcion_Ext_2;
	public $Ext_2;
	public $criterio_PROVEEDOR;
	public $criterio_CATEGORIA;
	public $criterio_SUBCATEGORIA;
	public $criterio_Producto;
	public $criterio_Marca;
	public $UnidadOrden;
	public $UnidadEmpaque;
	public $CodigoBarras;
	public $Estado;

	public function rules()
	{
		return [
			[['Item', 'Referencia', 'Descripcion'], 'required'],
			[['Item'], 'integer'],
			[['Equivalencia_UM'], 'number'],
			[
				[
					'Referencia',
					'Descripcion',
					'Ext_1',
					'Descripcion_Ext_1',
					'Ext_2',
					'Descripcion_Ext_2',
					'UM',
					'criterio_PROVEEDOR',
					'criterio_CATEGORIA',
					'criterio_SUBCATEGORIA',
					'Estado',
					'criterio_Producto',
					'criterio_Marca',
					'UnidadOrden',
					'UnidadEmpaque',
					'CodigoBarras'
				],
				'string'
			],
		];
	}

	public function attributeLabels()
	{
		return [
			'Referencia' => 'Referencia',
			'Descripcion' => 'Descripción',
			'Ext_1' => 'Ext1',
			'Descripcion_Ext_1' => 'Talla',
			'Ext_2' => 'Ext2',
			'Descripcion_Ext_2' => 'Color',
			'Equivalencia_UM' => 'Equiv. UM',
			'UM' => 'UM',
			'criterio_PROVEEDOR' => 'Proveedor',
			'criterio_CATEGORIA' => 'Categoría',
			'criterio_SUBCATEGORIA' => 'Subcategoría',
			'criterio_Producto' => 'Producto',
			'criterio_Marca' => 'Marca',
			'UnidadEmpaque' => 'Unidad Empaque',
			'UnidadOrden' => 'Unidad Orden',
			'CodigoBarras' => 'Código Barras',
			'Estado' => 'Estado'
		];
	}

	public function getAllProductosWs($item = null)
	{
		$endpointConfig = Yii::$app->params['endpoints']['service'];
		$descripcion = 'Productos';

		$conniKey = $endpointConfig['conniKey'];
		$conniToken = $endpointConfig['conniToken'];
		$idCompania = $endpointConfig['idCompania'];

		$responseData = [];

		if ($item != null) {
			$this->Item = $item;
		}

		try {
			$cliente = new Client();

			$url = $endpointConfig['url'];

			$request = $cliente->createRequest()
				->setMethod('GET')
				->setUrl($url)
				->setData([
					'idCompania' => $idCompania,
					'descripcion' => 'Productos',
					'parametros' => 'Item=' . $this->Item,
				])
				->addHeaders([
					'conniKey' => $conniKey,
					'conniToken' => $conniToken,
				])
				->send();

			$response = json_decode($request->content, true);

			if ($response['codigo'] == 0) {
				$responseData = $response['detalle']['Table'];
			} else {
				$errorMessage = 'La solicitud no fue exitosa: ' . $response['codigo'] . ' - ' . $response['mensaje'];
			}

			//$responseData = $response['detalle']['Table'];
		} catch (\Exception $e) {
			// Capturar y manejar cualquier excepción que ocurra durante la solicitud
			$errorMessage = 'Error al realizar la solicitud: ' . $e->getMessage();
			die($errorMessage);
		}

		return $responseData;
	}

	public static function sincronizarERPGus($item = null)
	{

		$respuesta = true;

		if ($item) {
			$modelWS = new ProductosWs();

			$lista = $modelWS->getAllProductosWs($item);

			foreach ($lista as $producto) {

				$codigotalla = $producto['Ext_1'];
				$nombretalla = $producto['Descripcion_Ext_1'];

				$codigocolor = $producto['Ext_2'];
				$nombrecolor = $producto['Descripcion_Ext_2'];

				$estado = 'INACTIVO';
				if ($producto['Estado']) {
					$estado = $producto['Estado'];
				}

				$idtalla = Talla::actualizarRegistro($codigotalla, $nombretalla);
				$idcolor = Color::actualizarRegistro($codigocolor, $nombrecolor);


				if ($producto['CodigoBarras']) {
					$model = Item::findOne(['codigoBarras' => $producto['CodigoBarras']]);
					if ($model == null) {
						$model = new Item();
						$model->codigoBarras = $producto['CodigoBarras'];
					}
				} else {
					$model = Item::findOne([
						'item' => $producto['Item'],
						'idTalla' => $idtalla,
						'idColor' => $idcolor
					]);

					if ($model == null) {
						$model = new Item();
						$model->item = $producto['Item'];
						$model->codigoBarras = null;
					}

					$estado = 'INACTIVO';
				}

				$model->item = $producto['Item'];
				$model->referencia = $producto['Referencia'];
				$model->descripcion = $producto['Descripcion'];

				$model->idTalla = $idtalla;
				$model->idColor = $idcolor;

				$modelMarca = new Marca();
				$cadena = $producto['criterio_Marca'];

				if ($cadena !== null) {
					$tokens = explode('/', $cadena);
				} else {
					$tokens[0] = 'NA';
					$tokens[1] = 'NA';
				}

				if (isset($tokens[0])) {
					$modelMarca->codigo = trim($tokens[0]);
				} else {
					$modelMarca->codigo = 'NA';
				}

				if (isset($tokens[1])) {
					$modelMarca->nombre = trim($tokens[1]);
				} else {
					$modelMarca->nombre = 'NA';
				}

				$idMarca = Marca::actualizarRegistro($modelMarca);
				$model->idMarca = $idMarca;

				$modelProducto = new Producto();
				$cadena = $producto['criterio_Producto'];
				$tokens = explode('/', $cadena);

				if (isset($tokens[0]) && !empty($tokens[0])) {
					$modelProducto->codigo = trim($tokens[0]);
				}

				if (isset($tokens[1]) && !empty($tokens[1])) {
					$modelProducto->nombre = trim($tokens[1]);
				}

				if (($modelProducto->codigo == null) || ($modelProducto->nombre == null)) {
					die('Error en el producto del item: ' . $producto['Item']);
				}

				$idProducto = Producto::actualizarRegistro($modelProducto);
				$model->idProducto = $idProducto;

				$cadena = $producto['criterio_PROVEEDOR'];
				$tokens = explode('/', $cadena);
				$model->codigoProveedor = trim($tokens[0]);
				$model->nombreProveedor = trim($tokens[1]);

				$modelCategoria = new Categoria();
				$cadena = $producto['criterio_CATEGORIA'];
				$tokens = explode('/', $cadena);
				$modelCategoria->codigoERP = trim($tokens[0]);
				$modelCategoria->nombre = trim($tokens[1]);
				$idCategoria = Categoria::actualizarRegistro($modelCategoria);
				$model->idCategoria = $idCategoria;

				$modelSubcategoria = new Subcategoria();
				$cadena = $producto['criterio_SUBCATEGORIA'];
				$tokens = explode('/', $cadena);
				$modelSubcategoria->codigoERP = trim($tokens[0]);
				$modelSubcategoria->nombre = trim($tokens[1]);
				$modelSubcategoria->idCategoria = $idCategoria;
				$idSubcategoria = Subcategoria::actualizarRegistro($modelSubcategoria);
				$model->idSubcategoria = $idSubcategoria;

				$model->unidadEmpaque = $producto['UnidadEmpaque'];
				$model->unidadOrden = $producto['UnidadOrden'];
				$model->codigoBarras = $producto['CodigoBarras'];

				$model->idEstado = $estado;

				$respuesta = $model->save();

				if (!$respuesta) {
					break;
				}
			}
		}

		return $respuesta;
	}

	public static function sincronizarERP($item = null): array
	{
		$item = is_string($item) ? trim($item) : $item;

		if ($item === null || $item === '') {
			return ['ok' => false, 'message' => 'No se recibió Item para sincronizar.', 'procesados' => 0];
		}

		$procesados = 0;

		try {
			$modelWS = new ProductosWs();
			$lista = $modelWS->getAllProductosWs($item);

			if (empty($lista)) {
				return [
					'ok' => false,
					'message' => 'El WS no devolvió productos para el Item "' . $item . '". No hay nada para sincronizar.',
					'procesados' => 0
				];
			}

			foreach ($lista as $producto) {

				/** ---------------- TALLA / COLOR ---------------- */
				$codigotalla = $producto['Ext_1'] ?? null;
				$nombretalla = $producto['Descripcion_Ext_1'] ?? null;

				$codigocolor = $producto['Ext_2'] ?? null;
				$nombrecolor = $producto['Descripcion_Ext_2'] ?? null;

				$estado = $producto['Estado'] ?? 'INACTIVO';

				$idtalla = Talla::actualizarRegistro($codigotalla, $nombretalla);
				$idcolor = Color::actualizarRegistro($codigocolor, $nombrecolor);

				/** ---------------- ITEM ---------------- */
				$codigoBarras = $producto['CodigoBarras'] ?? null;
				$itemErp      = $producto['Item'] ?? null;

				if ($itemErp === null || $itemErp === '') {
					return [
						'ok' => false,
						'message' => 'Registro devuelto por WS sin campo Item. No se puede sincronizar.',
						'procesados' => $procesados
					];
				}

				if (!empty($codigoBarras)) {

					$model = Item::findOne(['codigoBarras' => $codigoBarras]);

					if ($model === null) {
						$model = new Item();
						$model->codigoBarras = $codigoBarras;
					}
				} else {

					$model = Item::findOne([
						'item'    => $itemErp,
						'idTalla' => $idtalla,
						'idColor' => $idcolor
					]);

					if ($model === null) {
						$model = new Item();
						$model->item = $itemErp;
						$model->codigoBarras = null;
					}

					// Tu regla actual: sin código de barras => INACTIVO
					$estado = 'INACTIVO';
				}

				$model->item        = $itemErp;
				$model->referencia  = $producto['Referencia'] ?? null;
				$model->descripcion = $producto['Descripcion'] ?? null;

				$model->idTalla = $idtalla;
				$model->idColor = $idcolor;

				/** ---------------- MARCA ---------------- */
				$cadenaMarca = trim((string)($producto['criterio_Marca'] ?? ''));
				$tokensMarca = array_map('trim', explode('/', $cadenaMarca));

				$modelMarca = new Marca();
				$modelMarca->codigo = $tokensMarca[0] ?? 'NA';
				$modelMarca->nombre = $tokensMarca[1] ?? 'NA';

				$model->idMarca = Marca::actualizarRegistro($modelMarca);

				/** ---------------- PRODUCTO (VALIDADO) ---------------- */
				$cadenaProducto = trim((string)($producto['criterio_Producto'] ?? ''));

				if ($cadenaProducto === '') {
					$valorCrudo = $producto['criterio_Producto'] ?? null;

					return [
						'ok' => false,
						'message' =>
						'Error sincronizando Item ' . $itemErp .
							': el campo del WS "criterio_Producto" está vacío/NULL. ' .
							'Valor recibido: ' . var_export($valorCrudo, true) . '. ' .
							'Se esperaba formato "CODIGO/NOMBRE".',
						'procesados' => $procesados
					];
				}


				$tokensProducto = array_map('trim', explode('/', $cadenaProducto));

				if (empty($tokensProducto[0]) || empty($tokensProducto[1])) {
					$valorCrudo = $producto['criterio_Producto'] ?? null;

					return [
						'ok' => false,
						'message' =>
						'Error sincronizando Item ' . $itemErp .
							': el campo del WS "criterio_Producto" es inválido. ' .
							'Valor recibido: ' . var_export($valorCrudo, true) . '. ' .
							'Se esperaba "CODIGO/NOMBRE".',
						'procesados' => $procesados
					];
				}


				$modelProducto = new Producto();
				$modelProducto->codigo = $tokensProducto[0];
				$modelProducto->nombre = $tokensProducto[1];

				$model->idProducto = Producto::actualizarRegistro($modelProducto);

				/** ---------------- PROVEEDOR ---------------- */
				$cadenaProv = trim((string)($producto['criterio_PROVEEDOR'] ?? ''));
				$tokensProv = array_map('trim', explode('/', $cadenaProv));
				$model->codigoProveedor = $tokensProv[0] ?? null;
				$model->nombreProveedor = $tokensProv[1] ?? null;

				/** ---------------- CATEGORIA ---------------- */
				$cadenaCat = trim((string)($producto['criterio_CATEGORIA'] ?? ''));
				$tokensCat = array_map('trim', explode('/', $cadenaCat));

				if (!empty($tokensCat[0]) && !empty($tokensCat[1])) {
					$modelCategoria = new Categoria();
					$modelCategoria->codigoERP = $tokensCat[0];
					$modelCategoria->nombre    = $tokensCat[1];
					$model->idCategoria = Categoria::actualizarRegistro($modelCategoria);
				}

				/** ---------------- SUBCATEGORIA ---------------- */
				$cadenaSub = trim((string)($producto['criterio_SUBCATEGORIA'] ?? ''));
				$tokensSub = array_map('trim', explode('/', $cadenaSub));

				if (!empty($tokensSub[0]) && !empty($tokensSub[1]) && !empty($model->idCategoria)) {
					$modelSubcategoria = new Subcategoria();
					$modelSubcategoria->codigoERP   = $tokensSub[0];
					$modelSubcategoria->nombre      = $tokensSub[1];
					$modelSubcategoria->idCategoria = $model->idCategoria;
					$model->idSubcategoria = Subcategoria::actualizarRegistro($modelSubcategoria);
				}

				/** ---------------- CAMPOS FINALES ---------------- */
				$model->unidadEmpaque = $producto['UnidadEmpaque'] ?? null;
				$model->unidadOrden   = $producto['UnidadOrden'] ?? null;
				$model->codigoBarras  = $codigoBarras;
				$model->idEstado      = $estado;

				if (!$model->save()) {
					return [
						'ok' => false,
						'message' => 'Error guardando el item ' . $itemErp . ': ' . json_encode($model->getErrors()),
						'procesados' => $procesados
					];
				}

				$procesados++;
			}

			return [
				'ok' => true,
				'message' => 'Sincronización OK. Registros procesados: ' . $procesados . '.',
				'procesados' => $procesados
			];
		} catch (\Throwable $e) {

			Yii::error([
				'msg' => 'Excepción en sincronizarERP',
				'item_param' => $item,
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			], 'syncERP');

			return [
				'ok' => false,
				'message' => 'Error inesperado en sincronización: ' . $e->getMessage(),
				'procesados' => $procesados
			];
		}
	}



	public static function sincronizarERPDesdeOC(
		$codigoBarras,
		$item,
		$referencia,
		$descripcion,
		$ext_1,
		$descripcion_ext_1,
		$ext_2,
		$descripcion_ext_2
	) {

		$model = Item::findOne(['codigoBarras' => $codigoBarras]);
		if ($model == null) {
			$model = new Item();
			$model->codigoBarras = $codigoBarras;
		}

		$model->item = $item;
		$model->referencia = $referencia;
		$model->descripcion = $descripcion;

		$modelTalla = new Talla();
		$modelTalla->codigo = $ext_1;
		$modelTalla->nombre = $descripcion_ext_1;
		$idTalla = Talla::actualizarRegistro($modelTalla);
		$model->idTalla = $idTalla;

		$modelColor = new Color();
		$modelTalla->codigo = $ext_2;
		$modelTalla->nombre = $descripcion_ext_2;
		$idColor = Color::actualizarRegistro($modelColor);
		$model->idColor = $idColor;

		$modelMarca = new Marca();
		$cadena = $producto['criterio_Marca'];
		$tokens = explode('/', $cadena);

		if (isset($tokens[0])) {
			$modelMarca->codigo = trim($tokens[0]);
		} else {
			$modelMarca->codigo = 'NA';
		}

		if (isset($tokens[1])) {
			$modelMarca->nombre = trim($tokens[1]);
		} else {
			$modelMarca->nombre = 'NA';
		}

		$idMarca = Marca::actualizarRegistro($modelMarca);
		$model->idMarca = $idMarca;

		$modelProducto = new Producto();
		$cadena = $producto['criterio_Producto'];
		$tokens = explode('/', $cadena);
		$modelProducto->codigo = trim($tokens[0]);
		$modelProducto->nombre = trim($tokens[1]);
		$idProducto = Producto::actualizarRegistro($modelProducto);
		$model->idProducto = $idProducto;

		$cadena = $producto['criterio_PROVEEDOR'];
		$tokens = explode('/', $cadena);
		$model->codigoProveedor = trim($tokens[0]);
		$model->nombreProveedor = trim($tokens[1]);

		$modelCategoria = new Categoria();
		$cadena = $producto['criterio_CATEGORIA'];
		$tokens = explode('/', $cadena);
		$modelCategoria->codigoERP = trim($tokens[0]);
		$modelCategoria->nombre = trim($tokens[1]);
		$idCategoria = Categoria::actualizarRegistro($modelCategoria);
		$model->idCategoria = $idCategoria;

		$modelSubcategoria = new Subcategoria();
		$cadena = $producto['criterio_SUBCATEGORIA'];
		$tokens = explode('/', $cadena);
		$modelSubcategoria->codigoERP = trim($tokens[0]);
		$modelSubcategoria->nombre = trim($tokens[1]);
		$modelSubcategoria->idCategoria = $idCategoria;
		$idSubcategoria = Subcategoria::actualizarRegistro($modelSubcategoria);
		$model->idSubcategoria = $idSubcategoria;

		$model->unidadEmpaque = $producto['UnidadEmpaque'];
		$model->unidadOrden = $producto['UnidadOrden'];
		$model->codigoBarras = $producto['CodigoBarras'];

		$model->idEstado = $producto['Estado'];

		$respuesta = $model->save();
	}
}
