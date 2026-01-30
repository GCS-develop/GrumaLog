<?php

namespace common\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

use yii\base\Model;
use yii\httpclient\Client;

class InventariosWs extends Model
{

	public $Item;
	public $Referencia;
	public $Descripcion;
	public $EAN;
	public $Extension1;
	public $Extension2;
	public $Bodega;
	public $NombreBodega;
	public $CantidadExistente;
	public $CantidadDisponible;
	public $CantidadComprometida;
	public $CostoPromedioUnitario;
	public $CantidadDisponible_POS;

	public function rules()
	{
		return [
			[['Item', 'Referencia', 'Descripcion'], 'required'],
			[['CantidadExistente', 'CantidadDisponible', 'CantidadComprometida', 'CantidadDisponible_POS'], 'integer'],
			[['CostoPromedioUnitario'], 'number'],
			[['Referencia', 'Descripcion', 'Extension1', 'Extension2', 'Bodega', 'NombreBodega', 'EAN'], 'string'],
		];
	}

	public function attributeLabels()
	{
		return [
			'Referencia' => 'Referencia',
			'Descripcion' => 'Descripción',
			'Extension1' => 'Color',
			'Extension2' => 'Talla',
			'EAN' => 'EAN',
			'Bodega' => 'Bodega',
			'NombreBodega' => 'Bodega',
			'CantidadExistente' => 'Existencia',
			'CantidadDisponible' => 'Disponible',
			'CantidadComprometida' => 'Comprometida',
			'CostoPromedioUnitario' => 'Costo Promedio',
			'CantidadDisponible_POS' => 'Disponible POS',
		];
	}


	public function getAllInventariosSiesa($ean = null, $item = null, $paramKey = 'Item', $descripcion = 'Inventarios')
	{
		$endpointConfig = Yii::$app->params['endpoints']['service'];
		$conniKey   = $endpointConfig['conniKey'];
		$conniToken = $endpointConfig['conniToken'];
		$idCompania = $endpointConfig['idCompania'];

		if ($ean) {
			$this->EAN  = $ean;
		}
		if ($item) {
			$this->Item = $item;
		}

		$parametros = $this->EAN
			? ('EAN=' . $this->EAN)
			: ($paramKey . '=' . $this->Item);

		try {
			$cliente = new \yii\httpclient\Client();
			$request = $cliente->createRequest()
				->setMethod('GET')
				->setUrl($endpointConfig['url'])
				->setData([
					'idCompania'  => $idCompania,
					'descripcion' => $descripcion,         // 👈 ahora variable
					'parametros'  => $parametros,
				])
				->addHeaders([
					'conniKey'   => $conniKey,
					'conniToken' => $conniToken,
				])
				->send();

			$response = json_decode($request->content, true);
			if (is_array($response) && isset($response['detalle']['Table'])) {
				return $response['detalle']['Table'];
			}
		} catch (\Exception $e) {
			\Yii::error('WS error: ' . $e->getMessage(), __METHOD__);
		}
		return [];
	}


	public function getAllInventariosSiesa1($ean = null)
	{
		$endpointConfig = Yii::$app->params['endpoints']['service'];
		$descripcion = 'Inventarios';

		$conniKey = $endpointConfig['conniKey'];
		$conniToken = $endpointConfig['conniToken'];
		$idCompania = $endpointConfig['idCompania'];

		$responseData = [];

		if ($ean) {
			$this->EAN = $ean;
		}

		try {
			$cliente = new Client();

			$url = $endpointConfig['url'];

			$request = $cliente->createRequest()
				->setMethod('GET')
				->setUrl($url)
				->setData([
					'idCompania' => $idCompania,
					'descripcion' => 'Inventarios',
					'parametros' => 'EAN=' . $this->EAN,
				])
				->addHeaders([
					'conniKey' => $conniKey,
					'conniToken' => $conniToken,
				])
				->send();

			$response = json_decode($request->content, true);

			//if ($response['codigo'] == 0) {
			if ($response !== null && json_last_error() === JSON_ERROR_NONE) {
				if (is_array($response)) {
					if (isset($response['detalle']['Table'])) {
						$responseData = $response['detalle']['Table'];
					}
				}
			} else {
				$errorMessage = 'La solicitud no fue exitosa: ' . $response['codigo'] . ' - ' . $response['mensaje'];
			}
		} catch (\Exception $e) {
			// Capturar y manejar cualquier excepción que ocurra durante la solicitud
			$errorMessage = 'Error al realizar la solicitud: ' . $e->getMessage();
		}

		return $responseData;
	}

	public function getUltimoPrecioSiesa($ean = null, $item = null)
	{
		// Debe llegar al menos uno
		if (empty($ean) && empty($item)) {
			return [];
		}

		// ⚠️ AJUSTA ESTE NOMBRE según tu config (dbSiesa, db_siesa, dbSqlServer, etc.)
		$db = Yii::$app->dbSiesa;

		$conditions = [];
		$params     = [];

		if (!empty($item)) {
			$conditions[]   = 'a.f120_id = :item';
			$params[':item'] = (int)$item;
		}

		if (!empty($ean)) {
			$conditions[]    = 'b.f121_id_barras_principal = :ean';
			$params[':ean']  = (string)$ean;
		}

		// Si por alguna razón no hay condiciones, no tiene sentido ejecutar
		if (empty($conditions)) {
			return [];
		}

		$where = implode(' OR ', $conditions);

		$sql = "
        WITH UltimaFechaPorCodigoBarras AS (
            SELECT 
                a.f120_id,
                b.f121_rowid,
                a.f120_descripcion,
                b.f121_id_ext1_detalle,
                b.f121_id_ext2_detalle,
                b.f121_id_barras_principal,
                exis.f400_costo_prom_uni,
                d.f126_fecha_activacion,
                d.f126_precio,
                ROW_NUMBER() OVER (
                    PARTITION BY b.f121_id_barras_principal
                    ORDER BY d.f126_fecha_activacion DESC
                ) AS RowNum
            FROM t120_mc_items a
            JOIN t121_mc_items_extensiones b 
                ON a.f120_rowid = b.f121_rowid_item
            JOIN t400_cm_existencia exis 
                ON exis.f400_rowid_item_ext = b.f121_rowid
            JOIN t126_mc_items_precios d 
                ON d.f126_rowid_item = a.f120_rowid
            WHERE {$where}
        )
        SELECT 
            f120_id,
            f120_descripcion AS ProductId,
            f121_id_ext1_detalle AS Color,
            f121_id_ext2_detalle AS Talla,
            f121_id_barras_principal AS EAN,
            f400_costo_prom_uni AS Cost,
            f126_fecha_activacion AS Fecha,
            f126_precio AS Price
        FROM UltimaFechaPorCodigoBarras
        WHERE RowNum = 1
        ORDER BY EAN DESC;
    ";

		try {
			return $db->createCommand($sql, $params)->queryAll();
		} catch (\Throwable $e) {
			Yii::error('Error SQL getUltimoPrecioSiesa: ' . $e->getMessage(), __METHOD__);
			return [];
		}
	}


	public static function mapPreciosPorEANDesdeUltimoPrecio(array $rows): array
	{
		$map = [];

		foreach ($rows as $r) {
			$ean = $r['EAN'] ?? $r['f121_id_barras_principal'] ?? null;
			if (!$ean) {
				continue;
			}

			$precio = isset($r['Price']) && is_numeric($r['Price'])
				? (float)$r['Price']
				: 0.0;

			$map[$ean] = [
				'precio_venta' => $precio,
				'campo_precio' => 'Price (UltimaFechaPorCodigoBarras)',
				'costo'        => isset($r['Cost']) && is_numeric($r['Cost']) ? (float)$r['Cost'] : 0.0,
				'fecha'        => $r['Fecha'] ?? null,
			];
		}

		return $map;
	}


	public static function getPrecioValor(array $row): float
	{
		if (isset($row['Price']) && is_numeric($row['Price']) && $row['Price'] > 0) {
			return (float)$row['Price'];
		}

		if (isset($row['PrecioVenta']) && is_numeric($row['PrecioVenta']) && $row['PrecioVenta'] > 0) {
			return (float)$row['PrecioVenta'];
		}

		if (isset($row['CostoPromedioUnitario']) && is_numeric($row['CostoPromedioUnitario'])) {
			return (float)$row['CostoPromedioUnitario'];
		}

		return 0.0;
	}

	public static function getPrecioCampo(array $row): string
	{
		if (isset($row['Price']) && is_numeric($row['Price']) && $row['Price'] > 0) {
			return 'Price (UltimaFechaPorCodigoBarras)';
		}

		if (!empty($row['PrecioCampo'])) {
			return $row['PrecioCampo'] . ' (Productos)';
		}

		if (isset($row['PrecioVenta']) && is_numeric($row['PrecioVenta'])) {
			return 'PrecioVenta (Productos)';
		}

		if (isset($row['CostoPromedioUnitario']) && is_numeric($row['CostoPromedioUnitario'])) {
			return 'CostoPromedioUnitario (Inventarios)';
		}

		return 'SIN_PRECIO';
	}
}
