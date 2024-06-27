<?php

namespace common\models;

use Yii;

use yii\base\Model;
use yii\httpclient\Client;

use frontend\models\Bodegas;

/**
 *
 * @property string $Codigo_bodega
 * @property string $Descripcion_bodega
 */
//class BodegasWs extends \yii\db\ActiveRecord
class BodegasWs extends Model
{

    public function rules()
    {
        return [
            [['Codigo_bodega', 'Descripcion_bodega'], 'required'],
            [['Codigo_bodega'], 'string', 'max' => 5],
            [['Descripcion_bodega'], 'string', 'max' => 255],
            [['Codigo_bodega'], 'unique'],
			[['Descripcion_bodega'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'Codigo_bodega' => 'Código',
            'Descripcion_bodega' => 'Descripción',
        ];
    }
	
	public function getAllBodegasWs (){
		
        $endpointConfig = Yii::$app->params['endpoints']['service'];
		$descripcion = 'Bodegas';

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
                    'descripcion' => 'Bodegas',
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
        
        $respuesta = true;
        $modelWS = new BodegasWs();

        $lista = $modelWS->getAllBodegasWs();

        foreach($lista as $bodega){

            $model = Bodegas::findOne(['codigo' => $bodega['Codigo_bodega']]);
            if ($model == null){
                $model = new Bodegas();
                $model->codigo = $bodega['Codigo_bodega'];
            }

            $model->nombre = $bodega['Descripcion_bodega'];

            $respuesta = $model->save(); 
            if (!$respuesta){
                break;
            }
        }

        return $respuesta;
    }
		
}
