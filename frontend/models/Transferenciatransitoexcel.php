<?php

namespace frontend\models;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "transferenciatransitoexcel".
 *
 * @property int $id
 * @property int $idTransferenciaerp
 * @property string $centroOperacionDocumento
 * @property string $tipoDocumento
 * @property string $fechaDocumento
 * @property string $bodegaSalidaDocumento
 * @property string $bodegaEntradaDocumento
 * @property string $centroOperacion
 * @property string $tipoDocumentoMovimiento
 * @property string $bodegaSalidaMovimiento
 * @property string $centroOperacionMovimiento
 * @property string $unidadSalida
 * @property int $cantidadBase
 * @property float $costoPromedioUnitario
 * @property int $item
 * @property string $color
 * @property string $talla
 * @property int $cajas
 *
 * @property Transferenciaerp $transferenciaerp
 */
class Transferenciatransitoexcel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transferenciatransitoexcel';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idTransferenciaerp', 'centroOperacionDocumento', 'tipoDocumento', 'fechaDocumento', 'bodegaSalidaDocumento', 'bodegaEntradaDocumento', 'centroOperacion', 'tipoDocumentoMovimiento', 'bodegaSalidaMovimiento', 'centroOperacionMovimiento', 'unidadSalida', 'cantidadBase', 'costoPromedioUnitario', 'item', 'color', 'talla'], 'required'],
            [['idTransferenciaerp', 'cantidadBase', 'item', 'cajas'], 'integer'],
            [['costoPromedioUnitario'], 'number'],
            /*[['centroOperacionDocumento', 'tipoDocumento', 'bodegaSalidaDocumento', 
            'bodegaEntradaDocumento', 'centroOperacion', 'tipoDocumentoMovimiento', 
            'bodegaSalidaMovimiento', 'centroOperacionMovimiento'], 'string', 'max' => 5],*/
            //[['fechaDocumento', 'unidadSalida'], 'string', 'max' => 10],
            [['color'], 'string', 'max' => 50],
            [['numero'], 'string', 'max' => 50],
            [['notas', 'unidadesConteoEmpaque', 'codigoUnidadEmpaque'], 'safe'],
            [['idTransferenciaerp'], 'exist', 'skipOnError' => true, 'targetClass' => Transferenciaerp::class, 'targetAttribute' => ['idTransferenciaerp' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idTransferenciaerp' => 'Id Transferenciaerp',
            'centroOperacionDocumento' => 'Centro Operacion Documento',
            'tipoDocumento' => 'Tipo Documento',
            'fechaDocumento' => 'Fecha Documento',
            'bodegaSalidaDocumento' => 'Bodega Salida Documento',
            'bodegaEntradaDocumento' => 'Bodeg Entrada Documento',
            'centroOperacion' => 'Centro Operacion',
            'tipoDocumentoMovimiento' => 'Tipo Documento Movimiento',
            'bodegaSalidaMovimiento' => 'Bodega Salida Movimiento',
            'centroOperacionMovimiento' => 'Centro Operacion Movimiento',
            'unidadSalida' => 'Unidad Salida',
            'cantidadBase' => 'Cantidad Base',
            'costoPromedioUnitario' => 'Costo Promedio Unitario',
            'item' => 'Item',
            'color' => 'Color',
            'talla' => 'Talla',
            'numero' => 'Número',
        ];
    }

    /**
     * Gets query for [[Transferenciaerp]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransferenciaerp()
    {
        return $this->hasOne(Transferenciaerp::class, ['id' => 'idTransferenciaerp']);
    }

    public static function transferenciaSalidaWS ($id, $username = null){

        $error = 0;

        $modeltransferencia = Transferenciaerp::findOne(['id' => $id]);
        if ($modeltransferencia == null){
            $model = new Transferenciaerperror();
            $model->idTransferenciaerp = $id;
            $model->centroOperacionDocumento = null;
            $model->tipoDocumento = null;
            $model->detalle = 'No Existe Modelo Transferencia ERP';
            $model->save();
            $error = 1;
        }

        $modelconector = Conectoresdinamicos::findOne(['id' => $modeltransferencia->idConectorDinamico]);

        if ($modelconector == null){
            $model = new Transferenciaerperror();
            $model->idTransferenciaerp = $id;
            $model->centroOperacionDocumento = null;
            $model->tipoDocumento = null;
            $model->detalle = 'No Existe Conector Dinamico SIESA';
            $model->save();
            $error = 1;
        }

        if ($error == 0){

            // $numRegistrosBorrados = Transferencialogws::deleteAll(['idTransferenciaerp' => $id]);
            // $numRegistrosBorrados = Transferenciaerperror::deleteAll(['idTransferenciaerp' => $id]);

            $tiposdocumentos = Transferenciatransitoexcel::find()
                            ->select([
                                'numero',
                                'centroOperacionDocumento', 
                                'tipoDocumento',
                                'fechaDocumento',
								'bodegaSalidaDocumento',
								'bodegaEntradaDocumento'
                                ])
                            ->distinct()
                            ->where(['idTransferenciaerp' => $id, 'procesado' => 0])
                            ->orderBy([
                                'numero' => SORT_ASC, 
                                'centroOperacionDocumento' => SORT_ASC, 
                                'tipoDocumento' => SORT_ASC,
                                'fechaDocumento' => SORT_ASC,
                                'bodegaSalidaDocumento' => SORT_ASC,
								'bodegaEntradaDocumento' => SORT_ASC
                            ])->all();

            $error = Transferenciatransitoexcel::transferenciasalidaxtipodocumento ($id, 
                                                                        $tiposdocumentos,
                                                                        $modelconector,
																		$username);
																		
        }

        return $error;
    }

    public static function transferenciasalidaxtipodocumento ($id, $tiposdocumentos, $modelconector, $username = null){

        $error = 0;
		$conta = 1;

        ini_set('memory_limit', '8G'); // Aumentar el límite de memoria a 256 MB (puedes ajustar este valor según tus necesidades)
        ini_set('max_execution_time', '7200'); //300 seconds = 5 minutes

        foreach($tiposdocumentos as $registro){

            $numeroRegistros = Transferenciatransitoexcel::find()
                            ->where(['idTransferenciaerp' => $id,
                                    'numero' => $registro->numero,
                                    'centroOperacionDocumento' => $registro->centroOperacionDocumento,
                                    'tipoDocumento' => $registro->tipoDocumento,
                                    'fechaDocumento' => $registro->fechaDocumento,
									'bodegaSalidaDocumento' => $registro->bodegaSalidaDocumento,
									'bodegaEntradaDocumento' => $registro->bodegaEntradaDocumento]
                            )->count();

            $numRegistrosBorrados = Transferencialogws::deleteAll(['idTransferenciaerp' => $id, 'numero' => $registro->numero]);
            $numRegistrosBorrados = Transferenciaerperror::deleteAll(['idTransferenciaerp' => $id, 'numero' => $registro->numero]);
                
            $modellog = new Transferencialogws();
            $modellog->idTransferenciaerp = $id;
            $modellog->centroOperacionDocumento = $registro->centroOperacionDocumento;
            $modellog->tipoDocumento = $registro->tipoDocumento;
            $modellog->fechaDocumento = $registro->fechaDocumento;
            $modellog->bodegaSalidaDocumento = $registro->bodegaSalidaDocumento;
            $modellog->bodegaEntradaDocumento = $registro->bodegaEntradaDocumento;
            $modellog->startDate = date('Y-m-d H:i:s');
            $modellog->idConectorDinamico = $modelconector->id;
            $modellog->numeroRegistros = $numeroRegistros;
            $modellog->numero = $registro->numero;

            $json = Transferenciatransitoexcel::transferenciaTransitoERP ($id, 
                                                                $registro->numero,
                                                                $registro->centroOperacionDocumento,
                                                                $registro->tipoDocumento,
                                                                $registro->fechaDocumento,
																$registro->bodegaSalidaDocumento,
																$registro->bodegaEntradaDocumento,
																$username
                                                            );

            if ($json == null){
                $model = new Transferenciaerperror();
                $model->idTransferenciaerp = $id;
                $model->centroOperacionDocumento = $registro->centroOperacionDocumento;
                $model->tipoDocumento = $registro->tipoDocumento;
                $model->fechaDocumento = $registro->fechaDocumento;
                $model->numero = $registro->numero;
                $model->detalle = 'No Existen Datos Para Importar (JSON): ' . $registro->centroOperacionDocumento . '-' . $registro->tipoDocumento;
                $model->save();
                continue;
            }

            $endpointConfig = Yii::$app->params['endpoints']['service'];
            $conniKey = $endpointConfig['conniKey'];
		    $conniToken = $endpointConfig['conniToken'];
            $idCompania = $endpointConfig['idCompania'];

            $baseurl = $endpointConfig['urlConector'];

            $url = $baseurl . '?' . 'idCompania=' . $idCompania . '&' .
                    'idInterface=' . $modelconector->idInterface . '&' . 
                    'idDocumento=' . $modelconector->idDocumento . '&' . 
                    'nombreDocumento=' . $modelconector->nombreSIESA . '&' . 
                    'validarEstructura=false';

            $headers = [
                'conniKey: Connikey-grupomayorista-QJBYOFU3',
                'conniToken: QJBYOFU3RTFVNKMWRDFRNUEWSDJSNVQ2SJNJMLU3RZJAOESZVJDLMW',
                'content-type' => 'application/json'
                // Agrega aquí otros headers si es necesario
            ];

            $respuesta = Transferenciaerp::ejecutartransferenciaWS ($url, $headers, $json);

            $filasActualizadas = Transferenciatransitoexcel::updateAll(
                                                                    [   'procesado' => 1], 
                                                                    [   
                                                                        'idTransferenciaerp' => $id,
                                                                        'numero' => $registro->numero,
                                                                        'centroOperacionDocumento' => $registro->centroOperacionDocumento,
                                                                        'tipoDocumento' => $registro->tipoDocumento,
                                                                        'fechaDocumento' => $registro->fechaDocumento,
                                                                        'bodegaSalidaDocumento' => $registro->bodegaSalidaDocumento,
                                                                        'bodegaEntradaDocumento' => $registro->bodegaEntradaDocumento
                                                                    ]
                                                                );

            $codigo = Transferenciaerp::errortransferenciaWS ($id, 
                                                    $registro->centroOperacionDocumento,
                                                    $registro->tipoDocumento,
                                                    $respuesta, 
                                                    $registro->fechaDocumento,                                                    
                                                    null,
                                                    $registro->numero);

            $modellog->endDate = date('Y-m-d H:i:s'); 
            $modellog->mensaje = $codigo;
            $modellog->save();
            
            /*if(!$modellog->save()){
                var_dump($modellog->getErrors());die("hola");
            };*/

            if ($codigo != 0){
                $error = 1;
            }
        }

        return $error;
    }
	
	public static function transferenciaTransitoERP ($id, $numero, $co, $tipodocumento, $fechadocumento, $bodegasalida, $bodegaentrada, $username = null){

        $registros = Transferenciatransitoexcel::find()
                            ->where(['idTransferenciaerp' => $id,
                                    'numero' => $numero,
                                    'centroOperacionDocumento' => $co,
                                    'tipoDocumento' => $tipodocumento,
                                    'fechaDocumento' => $fechadocumento,
									'bodegaSalidaDocumento' => $bodegasalida,
									'bodegaEntradaDocumento' => $bodegaentrada
									]
                                    )
                            ->orderBy([
                                'centroOperacionDocumento' => SORT_ASC, 
                                'tipoDocumento' => SORT_ASC,
                                'fechaDocumento' => SORT_ASC,
                                'bodegaSalidaDocumento' => SORT_ASC,
                                'bodegaEntradaDocumento' => SORT_ASC,
                            ])->all();

        // Arreglo para almacenar los datos
        $jsonArray = [];
        $nroregistro = 0;
		
		if ($username == null){
			$username = 'WS SIESA';
		}

        $notas = null;
        $modeltransferencia = Transferenciaerp::findOne(['id' => $id]);
        if ($modeltransferencia->notas){
            $buscar = 'Traspaso CDSC';
            $posicion = strpos($modeltransferencia->notas, $buscar);

            if ($posicion !== false){
                $notas = $modeltransferencia->notas . ' - Documento => ' . $modeltransferencia->documento;
            }
        }

        // Recorrer los registros y construir el JSON
        foreach ($registros as $registro) {

            $documentoKey = $registro->numero . '-' .
                            $registro->centroOperacion . '-' . 
                            $registro->tipoDocumentoMovimiento . '-' .
                            $registro->transferenciaerp->documento . '-' .
                            $registro->fechaDocumento . '-' . 
                            $registro->bodegaSalidaDocumento . '-' . 
                            $registro->bodegaEntradaDocumento;
                            
            $nroregistro = $nroregistro + 1;

            if ($notas == null){
                $notas = 'No. Traspaso => ' . $registro->numero . ' - Fecha => ' . $registro->fechaDocumento . ' - ' . 'Usuario => ' . $username;
            }

            $movimiento = [
                'f470_id_co' => $registro->centroOperacion,
                'f470_id_tipo_docto' => $registro->tipoDocumentoMovimiento,
                'f470_consec_docto' => $registro->transferenciaerp->documento,
                'f470_nro_registro' => $nroregistro,
                'f470_id_bodega' => $registro->bodegaSalidaMovimiento,
                'f470_id_motivo' => '01',
                'f470_id_co_movto' => $registro->centroOperacionMovimiento,
                'f470_id_unidad_medida' => $registro->unidadSalida,
                'f470_cant_base' => $registro->cantidadBase,
                'f470_costo_prom_uni' => $registro->costoPromedioUnitario,
                //'f470_notas' => 'No. Traspaso => ' . $registro->numero . ' - Fecha => ' . $registro->fechaDocumento . ' - ' . 'Usuario => ' . $username,
                'f470_notas' => $notas,
                'f470_id_item' => $registro->item,
                'f470_id_ext1_detalle' => $registro->color,
                'f470_id_ext2_detalle' => $registro->talla,
                'f470_id_un_movto' => '03',
            ];

            if (!isset($jsonArray[$documentoKey])) {
                $registros = 0;
                $jsonArray[$documentoKey] = [
                    'Documentos' => [
                        'f350_id_co' => $registro->centroOperacionDocumento,
                        'f350_id_tipo_docto' => $registro->tipoDocumento,
                        'f350_consec_docto' => $registro->transferenciaerp->documento,
                        'f350_fecha' => $registro->fechaDocumento,
                        'f350_id_tercero' => '',
                        // 'f350_notas' => 'No. Traspaso => ' . $registro->numero . ' - Fecha => ' . $registro->fechaDocumento . ' - ' . 'Usuario => ' . $username,
                        'f350_notas' => $notas,
                        'f450_id_bodega_salida' => $registro->bodegaSalidaDocumento,
                        'f450_id_bodega_entrada' => $registro->bodegaEntradaDocumento,
                    ],
                    
                    'Movimientos' => [],
                ];
            }

            $documento = [
                'f350_id_co' => $registro->centroOperacionDocumento,
                'f350_id_tipo_docto' => $registro->tipoDocumento,
                'f350_consec_docto' => $registro->transferenciaerp->documento,
                'f350_fecha' => $registro->fechaDocumento,
                'f350_id_tercero' => '',
                // 'f350_notas' => 'No. Traspaso => ' . $registro->numero . ' - Fecha => ' . $registro->fechaDocumento . ' - ' . 'Usuario => ' . $username,
                'f350_notas' => $notas,
                'f450_id_bodega_salida' => $registro->bodegaSalidaDocumento,
                'f450_id_bodega_entrada' => $registro->bodegaEntradaDocumento,
            ];
                
            //$jsonArray[$documentoKey]['Documentos'][] = $documento;
            $jsonArray[$documentoKey]['Movimientos'][] = $movimiento;
        }

        $documentosJsonArray = [];

        foreach ($jsonArray as $documento) {
            $documentosJsonArray[] = [
                'Documentos' => [$documento['Documentos']],
                'Movimientos' => $documento['Movimientos'],
            ];
        }

        // Convertir el arreglo a JSON
        //$json = Json::encode(array_values($jsonArray));
        $json = substr(Json::encode(array_values($documentosJsonArray)), 1, -1);

        return $json;
    }

    public static function getTotalUnidadesConteo($id)
    {
        return self::find()
            ->where(['idTransferenciaerp' => $id])
            ->sum('cantidadBase');
    }

    public static function getTotalUnidadesConteoEmpaque($id)
    {
        return self::find()
            ->where(['idTransferenciaerp' => $id])
            ->sum('unidadesConteoEmpaque');
    }
}
