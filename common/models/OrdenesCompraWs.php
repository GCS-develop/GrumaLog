<?php

namespace common\models;

use Yii;

use yii\base\Model;
use yii\httpclient\Client;

use frontend\models\Centrooperacion;
use frontend\models\Estadoordencompra;
use frontend\models\Tipodocumento;
use frontend\models\Ordendecompra;
use frontend\models\Ordendecompradetalle;
use frontend\models\Proveedor;
use frontend\models\Item;

/**
 *
 * @property string $CentroOperacion
 * @property string $TipoDocumento
 * @property string $Consecutivo
 * @property string $Fecha
 * @property string $IdTercero
 * @property string $RazonSocial
 * @property string $Estado_Doc
 * @property string $Item
 * @property string $Descripcion_Item
 * @property string $Referencia_Item
 * @property string $CantidadPedida
 * @property string $CantidadEntrada
 * @property string $CantidadPendiente
 * @property string $UnidadMedida
 * @property string $Fecha_Entrega
 */
//class OrdenesCompraWs extends \yii\db\ActiveRecord
class OrdenesCompraWs extends Model
{
    public $CentroOperacion;
    public $TipoDocumento;
    public $Consecutivo;
    public $Fecha;
    public $IdTercero;
    public $RazonSocial;
    public $Estado_Doc;
    public $Item;
    public $Referencia_Item;
    public $Descripcion_Item;
    public $CantidadPedida;
    public $CantidadEntrada;
    public $CantidadPendiente;
    public $UnidadMedida;
    public $Fecha_Entrega;
    public $criterio_CATEGORIA;
    public $criterio_SUBCATEGORIA;
    public $Ext_1;
    public $Descripcion_Ext_1;
    public $Ext_2;
    public $Descripcion_Ext_2;
    public $CodigoBarras;

    public function rules()
    {
        return [
            [['CantidadEntrada', 'CantidadPendiente', 'CantidadPedida', 'Consecutivo'], 'number'],
            [['CentroOperacion', 'TipoDocumento', 'Consecutivo', 'Fecha', 'IdTercero', 'RazonSocial',
            'Estado_Doc', 'Item', 'Referencia_Item', 'Descripcion_Item',  
            'UnidadMedida', 'Fecha_Entrega', 'criterio_CATEGORIA', 'criterio_SUBCATEGORIA',
            'Ext_1', 'Descripcion_Ext_1', 'Ext_2', 'Descripcion_Ext_2', 'CodigoBarras'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'CentroOperacion' => 'CO', 
            'TipoDocumento' => 'Tipo Dcto',
            'Consecutivo' => 'Consecutivo', 
            'IdTercero' => 'Tercero',
            'RazonSocial' => 'Razon Social', 
            'Fecha' => 'Fecha', 
            'Estado_Doc' => 'Estado',
            'Item' => 'Item',
            'Referencia_Item' => 'Referencia',
            'Descripcion_Item' => 'Descripción',
            'CantidadPedida' => 'Cant. Pedida',
            'CantidadEntrada' => 'Cant. Entrada',
            'CantidadPendiente' => 'Cant. Pendiente',
            'UnidadMedida' => 'UM',
            'Fecha_Entrega' => 'Fecha Entrega',
            'criterio_CATEGORIA' => 'Categoría', 
            'criterio_SUBCATEGORIA' => 'Subcategoría',
            'Ext_1' => 'Talla', 
            'Descripcion_Ext_1' => 'Talla', 
            'Ext_2' => 'Color', 
            'Descripcion_Ext_2' => 'Color', 
            'CodigoBarras' => 'Código Barras'
        ];
    }
	
	public function getAllOrdenesCompraWs ($co = null, $tipodocumento = null, $consecutivo = null){
		
        if ($co){
			$this->CentroOperacion = $co;
		}

        if ($tipodocumento){
            $this->TipoDocumento = $tipodocumento;
		}

        if ($consecutivo){
            $this->Consecutivo = $consecutivo;
		}

		$endpointConfig = Yii::$app->params['endpoints']['service'];
		$descripcion = 'OrdendeCompra';

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
                    'descripcion' => 'OrdendeCompra',
                    'parametros' => 'CO=' .$this->CentroOperacion . '|' . 'TIPODOCTO=' . $this->TipoDocumento . '|' . 'CONSECUTIVO=' . $this->Consecutivo,
                ])
                ->addHeaders([
                    'conniKey' => $conniKey,
                    'conniToken' => $conniToken,
                ])
                ->send();
                
            $response = json_decode($request->content, true);

			if ($response !== null && json_last_error() === JSON_ERROR_NONE) {
				if (is_array($response)) {
					if (isset($response['detalle']['Table'])) {
						$responseData = $response['detalle']['Table'];
					}
				}
			}else{
				$errorMessage = 'La solicitud no fue exitosa: ' . $response['codigo'] . ' - ' . $response['mensaje'];
			}
        } catch (\Exception $e) {
			// Capturar y manejar cualquier excepción que ocurra durante la solicitud
			$errorMessage = 'Error al realizar la solicitud: ' . $e->getMessage();
		}
			
		return $responseData;

	}

    public static function sincronizarERP ($co = null, $tipodocumento = null, $consecutivo = null){

        $respuesta = false;

		if ($co && $tipodocumento && $consecutivo){
			$respuesta = true;
			$modelWS = new OrdenesCompraWs();

			$lista = $modelWS->getAllOrdenesCompraWs($co, $tipodocumento, $consecutivo);

            $encabezado = 1;
            $totalCantidadPedida = 0;
            $totalCantidadEntrada = 0;
            $totalCantidadPendiente = 0;
            $idOrdenCompra = null;

            $array = array();

			foreach($lista as $ordendecompra){

                $codigobarras = $ordendecompra['CodigoBarras'];
                if ($codigobarras == null){
                    $respuesta = false;
                    break;
                }

                if ($encabezado == 1){
                    $modelCO = new Centrooperacion ();
                    $modelCO->codigo = $ordendecompra['CentroOperacion'];
                    $modelCO->nombre = $ordendecompra['CentroOperacion'];
                    $idCO = Centrooperacion::actualizarRegistro($modelCO);
        
                    $modelTD = new Tipodocumento();
                    $modelTD->codigo = $ordendecompra['TipoDocumento'];
                    $modelTD->nombre = $ordendecompra['TipoDocumento'];
                    $idTipoDocumento = Tipodocumento::actualizarRegistro($modelTD);

                    $model = Ordendecompra::findOne(['idCO' => $idCO,
                                                'idTipoDocumento' => $idTipoDocumento,
                                                'consecutivo' => $consecutivo
                                            ]);
                    if ($model == null){
                        $model = new Ordendecompra();
                        $model->idCO = $idCO;
                        $model->idTipoDocumento = $idTipoDocumento;
                        $model->consecutivo =$ordendecompra['Consecutivo'];
                    }

                    $modelEO = new Estadoordencompra();
                    $modelEO->nombre = $ordendecompra['Estado_Doc'];
                    $idEstado = Estadoordencompra::actualizarRegistro($modelEO);

                    /*
                    $modelPR = new Proveedor();
                    $modelPR->nit = $ordendecompra['IdTercero'];
                    $modelPR->razonSocial =  $ordendecompra['RazonSocial'];
                    $idProveedor = Proveedor::actualizarRegistro($modelPR);
                    */

                    $idProveedor = null;
                    $nit = $ordendecompra['IdTercero'];

                    if ($nit){
                        $modelPR = new Proveedor();
                        $respuesta = ProveedoresWs::sincronizarERP($nit);
                        if ($respuesta){
                            $modelPR = Proveedor::findOne(['nit' => $nit]);
                        }
                        if ($modelPR){
                            $idProveedor = $modelPR->id;
                        }
                    }


                    $model->idEstado = $idEstado;
                    $model->idProveedor = $idProveedor;
                    $model->fecha = null;

                    $dateString = $ordendecompra['Fecha'];
                    $date = \DateTime::createFromFormat('Ymd', $dateString);

                    if ($date !== false) {
                        $model->fecha = $date->format('Y-m-d'); 
                    }

                    $model->fechaEntrega = $ordendecompra['Fecha_Entrega'];

                    $respuesta = $model->save();

                    if ($model->getErrors()){
                        var_dump($model->getErrors()); die("hola");
                    }
                    $encabezado = 0;
                    $idOrdenCompra = $model->id;

                    $numRegistrosBorrados = Ordendecompradetalle::deleteAll(['idOrdenCompra' => $idOrdenCompra]);
                }

				if (!$respuesta){
					break;
				}

                $modeldetalle = new Ordendecompradetalle();
                $modeldetalle->idOrdenCompra = $idOrdenCompra;

                if (!in_array($ordendecompra['Item'], $array)) {
                    $array[] = $ordendecompra['Item'];

                    $respuesta = ProductosWs::sincronizarERP ($ordendecompra['Item']);
                }                

                /*$modeldetalle->idItem = Item::actualizarRegistro( $ordendecompra['Item'], 
                                                    $ordendecompra['Referencia_Item'],
                                                    $ordendecompra['Descripcion_Item'],
                                                    $ordendecompra['criterio_CATEGORIA'],
                                                    $ordendecompra['criterio_SUBCATEGORIA'],
                                                    $ordendecompra['Ext_1'],
                                                    $ordendecompra['Descripcion_Ext_1'],
                                                    $ordendecompra['Ext_2'],
                                                    $ordendecompra['Descripcion_Ext_2'],
                                                    $ordendecompra['CodigoBarras']
                                                );*/
                
                /*$respuesta = ProductosWs::sincronizarERP ($ordendecompra['Item']);*/

                $modelItem = Item::findOne(['codigoBarras' => $codigobarras]);

                $modeldetalle->idItem = $modelItem->id;
                $modeldetalle->idCategoria = $modelItem->idCategoria;
                $modeldetalle->idSubcategoria = $modelItem->idSubcategoria;

                $modeldetalle->fechaEntrega = $ordendecompra['Fecha_Entrega'];
                $modeldetalle->cantidadPedida = $ordendecompra['CantidadPedida'];
                $modeldetalle->cantidadEntrada = $ordendecompra['CantidadEntrada'];
                $modeldetalle->cantidadPendiente = $ordendecompra['CantidadPendiente'];

                if (!$modeldetalle->save()){
                    var_dump($modeldetalle->getErrors()); die("hola");
                }

                $totalCantidadPedida = $totalCantidadPedida + $ordendecompra['CantidadPedida'];
                $totalCantidadEntrada = $totalCantidadEntrada + $ordendecompra['CantidadEntrada'];
                $totalCantidadPendiente = $totalCantidadPendiente + $ordendecompra['CantidadPendiente'];
			}

            if ($idOrdenCompra != null){
                $model = Ordendecompra::findOne(['id' => $idOrdenCompra]);
                if ($model){
                    $model->totalCantidadPedida = $totalCantidadPedida;
                    $model->totalCantidadEntrada = $totalCantidadEntrada;
                    $model->totalCantidadPendiente = $totalCantidadPendiente;

                    $model->save();

                    return $model;
                }
            }


		}

        return $respuesta;
    }

    public static function sincronizarERPAgenda ($co = null, $tipodocumento = null, $consecutivo = null){

        $respuesta = false;
        $idOrdenCompra = null;

		if ($co && $tipodocumento && $consecutivo){
			$respuesta = true;
			$modelWS = new OrdenesCompraWs();

			$lista = $modelWS->getAllOrdenesCompraWs($co, $tipodocumento, $consecutivo);

            //var_dump($lista);die("hola 3");

            $encabezado = 1;
            $totalCantidadPedida = 0;
            $totalCantidadEntrada = 0;
            $totalCantidadPendiente = 0;
            $idOrdenCompra = null;

            $array = array();

			foreach($lista as $ordendecompra){

                $codigobarras = $ordendecompra['CodigoBarras'];
                if ($codigobarras == null){
                    continue;
                }

                if ($encabezado == 1){
                    $modelCO = new Centrooperacion ();
                    $modelCO->codigo = $ordendecompra['CentroOperacion'];
                    $modelCO->nombre = $ordendecompra['CentroOperacion'];
                    $idCO = Centrooperacion::actualizarRegistro($modelCO);
        
                    $modelTD = new Tipodocumento();
                    $modelTD->codigo = $ordendecompra['TipoDocumento'];
                    $modelTD->nombre = $ordendecompra['TipoDocumento'];
                    $idTipoDocumento = Tipodocumento::actualizarRegistro($modelTD);

                    $model = Ordendecompra::findOne(['idCO' => $idCO,
                                                'idTipoDocumento' => $idTipoDocumento,
                                                'consecutivo' => $consecutivo
                                            ]);
                    if ($model == null){
                        $model = new Ordendecompra();
                        $model->idCO = $idCO;
                        $model->idTipoDocumento = $idTipoDocumento;
                        $model->consecutivo =$ordendecompra['Consecutivo'];
                    }

                    $modelEO = new Estadoordencompra();
                    $modelEO->nombre = $ordendecompra['Estado_Doc'];
                    $idEstado = Estadoordencompra::actualizarRegistro($modelEO);

                    /*$modelPR = new Proveedor();
                    $modelPR->nit = $ordendecompra['IdTercero'];
                    $modelPR->razonSocial =  $ordendecompra['RazonSocial'];
                    $idProveedor = Proveedor::actualizarRegistro($modelPR);*/

                    $idProveedor = null;
                    $nit = $ordendecompra['IdTercero'];

                    if ($nit){
                        $modelPR = new Proveedor();
                        $respuesta = ProveedoresWs::sincronizarERP($nit);
                        if ($respuesta){
                            $modelPR = Proveedor::findOne(['nit' => $nit]);
                        }
                        if ($modelPR){
                            $idProveedor = $modelPR->id;
                        }
                    }

                    $model->idEstado = $idEstado;
                    $model->idProveedor = $idProveedor;
                    $model->fecha = null;

                    $dateString = $ordendecompra['Fecha'];
                    $date = \DateTime::createFromFormat('Ymd', $dateString);

                    if ($date !== false) {
                        $model->fecha = $date->format('Y-m-d'); 
                    }

                    $model->fechaEntrega = $ordendecompra['Fecha_Entrega'];

                    $respuesta = $model->save();

                    if ($model->getErrors()){
                        var_dump($model->getErrors()); die("hola");
                    }
                    $encabezado = 0;
                    $idOrdenCompra = $model->id;

                    $numRegistrosBorrados = Ordendecompradetalle::deleteAll(['idOrdenCompra' => $idOrdenCompra]);
                }

				if (!$respuesta){
					break;
				}

                $modeldetalle = new Ordendecompradetalle();
                $modeldetalle->idOrdenCompra = $idOrdenCompra;       

                $modeldetalle->idItem = Item::actualizarRegistro( $ordendecompra['Item'], 
                                                    $ordendecompra['Referencia_Item'],
                                                    $ordendecompra['Descripcion_Item'],
                                                    $ordendecompra['criterio_CATEGORIA'],
                                                    $ordendecompra['criterio_SUBCATEGORIA'],
                                                    $ordendecompra['Ext_1'],
                                                    $ordendecompra['Descripcion_Ext_1'],
                                                    $ordendecompra['Ext_2'],
                                                    $ordendecompra['Descripcion_Ext_2'],
                                                    $ordendecompra['CodigoBarras']
                                                );

                $modelitem = Item::findOne(['codigoBarras' => $ordendecompra['CodigoBarras']]);
                
                $modeldetalle->idCategoria = $modelitem->categoria->id;
                $modeldetalle->idSubcategoria = $modelitem->subcategoria->id;

                $modeldetalle->fechaEntrega = $ordendecompra['Fecha_Entrega'];
                $modeldetalle->cantidadPedida = $ordendecompra['CantidadPedida'];
                $modeldetalle->cantidadEntrada = $ordendecompra['CantidadEntrada'];
                $modeldetalle->cantidadPendiente = $ordendecompra['CantidadPendiente'];

                if (!$modeldetalle->save()){
                    var_dump($modeldetalle->getErrors()); die("hola");
                }

                $totalCantidadPedida = $totalCantidadPedida + $ordendecompra['CantidadPedida'];
                $totalCantidadEntrada = $totalCantidadEntrada + $ordendecompra['CantidadEntrada'];
                $totalCantidadPendiente = $totalCantidadPendiente + $ordendecompra['CantidadPendiente'];
			}

            if ($idOrdenCompra != null){
                $model = Ordendecompra::findOne(['id' => $idOrdenCompra]);
                if ($model){
                    $model->totalCantidadPedida = $totalCantidadPedida;
                    $model->totalCantidadEntrada = $totalCantidadEntrada;
                    $model->totalCantidadPendiente = $totalCantidadPendiente;

                    $model->save();

                    return $model;
                }
            }


		}

        return $idOrdenCompra;
    }
		
}
