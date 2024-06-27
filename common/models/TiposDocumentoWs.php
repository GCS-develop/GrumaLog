<?php

namespace common\models;

use Yii;

use yii\base\Model;
use yii\httpclient\Client;

use frontend\models\Tipodocumento;

/**
 *
 * @property string $Id_tipodocto
 * @property string $Descripción_Id_tipodocto
 */
//class TiposDocumentoWs extends \yii\db\ActiveRecord
class TiposDocumentoWs extends Model
{

    //public $Id_tipodocto;

    public function rules()
    {
        return [
            [['Id_tipodocto', 'Descripción_Id_tipodocto', 'Consecutivo_Proximo'], 'required'],
            [['Id_tipodocto'], 'string', 'max' => 5],
            [['Descripción_Id_tipodocto'], 'string', 'max' => 255],
            [['Id_tipodocto'], 'unique'],
			[['Descripción_Id_tipodocto'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'Id_tipodocto' => 'ID',
            'Descripción_Id_tipodocto' => 'Descripción',
            'Consecutivo_Proximo' => 'Próximo Consecutivo'
        ];
    }
	
	public function getAllTiposDocumentoWs ($tipoDocto = null){
		
		$endpointConfig = Yii::$app->params['endpoints']['service'];
		$descripcion = 'TiposdeDocumentos';

		$conniKey = $endpointConfig['conniKey'];
		$conniToken = $endpointConfig['conniToken'];
        $idCompania = $endpointConfig['idCompania'];
		
        $responseData = [];

        if ($tipoDocto == null){
            $tipoDocto = '-1';
        }   

        try {
            $cliente = new Client();
            
            $url = $endpointConfig['url'];
            
            $request = $cliente->createRequest()
                ->setMethod('GET')
                ->setUrl($url)
                ->setData([
                    'idCompania' => $idCompania,
                    'descripcion' => 'TiposdeDocumentos',
                    'parametros' => 'TipoDocto=' .$tipoDocto,
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
        $modelWS = new TiposDocumentoWs();

        $lista = $modelWS->getAllTiposDocumentoWs();

        foreach($lista as $tipodocumento){

            $model = TipoDocumento::findOne(['codigo' => $tipodocumento['Id_tipodocto']]);
            if ($model == null){
                $model = new TipoDocumento();
                $model->codigo = $tipodocumento['Id_tipodocto'];
                $model->nombre = $tipodocumento['Descripción_Id_tipodocto'];
            }

            $model->consecutivoProximo = $tipodocumento['Consecutivo_Proximo'];

            $respuesta = $model->save(); 
            if (!$respuesta){
                break;
            }
        }

        return $respuesta;
    }
		
}
