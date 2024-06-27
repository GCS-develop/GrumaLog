<?php

namespace common\models;

use Yii;

use yii\base\Model;
use yii\httpclient\Client;

/**
 *
 * @property string $Co
 * @property string $Tipo_docto
 * @property string $ConsecutivoERP
 * @property string $Tercero
 * @property string $Fecha
 * @property string $Notas
 */
//class TransferenciasWs extends \yii\db\ActiveRecord
class TransferenciasWs extends Model
{
    public $Co;
    public $Tipo_docto;
    public $ConsecutivoERP;
    public $Tercero;
    public $Fecha;
    public $Notas;

    public function rules()
    {
        return [
            [['ConsecutivoERP'], 'number'],
            [['Co', 'Tipo_docto', 'Tercero', 'Fecha', 'Notas'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'Co' => 'CO', 
            'Tipo_docto' => 'Tipo Documento', 
            'Tercero' => 'Tercero', 
            'Fecha' => 'Fecha', 
            'Notas' => 'Notas'
        ];
    }
	
	public function getAllTransferenciasWs (){
		
		$endpointConfig = Yii::$app->params['endpoints']['service'];
		$descripcion = 'Transferencias';

		$conniKey = $endpointConfig['conniKey'];
		$conniToken = $endpointConfig['conniToken'];
        $idCompania = $endpointConfig['idCompania'];
		
        $responseData = [];

        try {
            $cliente = new Client();
            
            $url = $endpointConfig['url'];

            $request = $cliente->createRequest()
                ->setMethod('GET')
                ->setUrl($url)
                ->setData([
                    'idCompania' => $idCompania,
                    'descripcion' => 'Transferencias',
                    'parametros' => 'CO=' .$this->Co . '|' . 'NOTAS=' . $this->Notas,
                ])
                ->addHeaders([
                    'conniKey' => $conniKey,
                    'conniToken' => $conniToken,
                ])
                ->send();
                
            $response = json_decode($request->content, true);

			if ($response['codigo'] == 0) {
				$responseData = $response['detalle']['Table'];
			}else{
				$errorMessage = 'La solicitud no fue exitosa: ' . $response['codigo'] . ' - ' . $response['mensaje'];
			}
        } catch (\Exception $e) {
			// Capturar y manejar cualquier excepción que ocurra durante la solicitud
			$errorMessage = 'Error al realizar la solicitud: ' . $e->getMessage();
		}
			
		return $responseData;

	}

    public static function sincronizarERP (){
        
        $query = TransferenciasWs::getAllTransferenciasWs();
    }

    public function conectorTransferenciasWs ($idinterface, $jsonData){
		
        $endpointConfig = Yii::$app->params['endpoints']['service'];
		$descripcion = 'Transferencias';

		$conniKey = $endpointConfig['conniKey'];
		$conniToken = $endpointConfig['conniToken'];
        $idCompania = $endpointConfig['idCompania'];
		
        $responseData = [];

        try {
            $cliente = new Client();
            
            $url ="https://connektaqa.siesacloud.com/api/v3/conectoresimportar";

            $request = $cliente->createRequest()
                ->setHeaders([
                    'conniKey' => $conniKey,
                    'conniToken' => $conniToken,
                ])
                ->setFormat(Client::FORMAT_JSON)
                ->setMethod('POST')
                ->setUrl($url)
                ->setData($jsonData)
                ->addParams([
                    'idCompania' => $idCompania,
                    'idInterface' => $idinterface,
                    'idDocumento' => 165613,
                    'nombreDocumento' => 'TRANSFERENCIA_SALIDA',
                ])
                ->send();
                
            $response = json_decode($request->content, true);

			if ($response['codigo'] == 0) {
				$responseData = $response['detalle']['Table'];
			}else{
				$errorMessage = 'La solicitud no fue exitosa: ' . $response['codigo'] . ' - ' . $response['mensaje'];
			}
        } catch (\Exception $e) {
			// Capturar y manejar cualquier excepción que ocurra durante la solicitud
			$errorMessage = 'Error al realizar la solicitud: ' . $e->getMessage();
		}
			
		return $responseData;

	}
		
}
