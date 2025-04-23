<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\Json;

use yii\httpclient\Client;
use yii\httpclient\Request;


use common\models\User;
use common\models\OrdendecompraSIESA;

/**
 * This is the model class for table "transferenciaerp".
 *
 * @property int $id
 * @property string $descripcion
 * @property int $documento
 * @property int $numeroRegistros
 * @property int $enviadoWS
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Transferenciatransitoexcel[] $transferenciatransitoexcels
 */
class Transferenciaerp extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transferenciaerp';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function ($event) {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['descripcion', 'numeroRegistros', 'documento',], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [
                [
                    'documento',
                    'numeroRegistros',
                    'enviadoWS',
                    'created_by',
                    'updated_by',
                    'idConectorDinamico'
                ],
                'integer'
            ],
            [['created_at', 'updated_at'], 'safe'],
            [['descripcion', 'notas'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'descripcion' => 'Descripción',
            'documento' => 'Documento',
            'numeroRegistros' => 'Numero Registros',
            'idConectorDinamico' => 'Conector SIESA',
            'notas' => 'Notas Movimiento',
            'enviadoWS' => 'Enviado Ws',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Transferenciatransitoexcels]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransferenciatransitoexcels()
    {
        return $this->hasMany(Transferenciatransitoexcel::class, ['idTransferenciaerp' => 'id']);
    }

    public function getUsercreated()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getConectordinamico()
    {
        return $this->hasOne(Conectoresdinamicos::class, ['id' => 'idConectorDinamico']);
    }

    public static function crearRegistro($iddocumento, $descripcion, $documento, $notas, $origen = null)
    {

        $modelconector = Conectoresdinamicos::find()->where(['idDocumento' => $iddocumento])->one();

        $model = new Transferenciaerp();
        $model->descripcion = $descripcion;
        $model->documento = $documento;
        $model->numeroRegistros = 0;
        $model->enviadoWS = 0;
        $model->idConectorDinamico = $modelconector->id;
        $model->notas = $notas;

        if ($origen != null) {
            $model->origen = $origen;
        }

        $model->save();

        return $model;

    }

    public static function transferenciaSalidaWS($id)
    {

        $error = 0;

        $modeltransferencia = Transferenciaerp::findOne(['id' => $id]);
        if ($modeltransferencia == null) {
            $model = new Transferenciaerperror();
            $model->idTransferenciaerp = $id;
            $model->centroOperacionDocumento = null;
            $model->tipoDocumento = null;
            $model->detalle = 'No Existe Modelo Transferencia ERP';
            $model->save();
            $error = 1;
        }

        $modelconector = Conectoresdinamicos::findOne(['id' => $modeltransferencia->idConectorDinamico]);


        if ($modelconector == null) {
            $model = new Transferenciaerperror();
            $model->idTransferenciaerp = $id;
            $model->centroOperacionDocumento = null;
            $model->tipoDocumento = null;
            $model->detalle = 'No Existe Conector Dinamico SIESA';
            $model->save();
            $error = 1;
        }

        if ($error == 0) {

            $numRegistrosBorrados = Transferencialogws::deleteAll(['idTransferenciaerp' => $id]);
            $numRegistrosBorrados = Transferenciaerperror::deleteAll(['idTransferenciaerp' => $id]);

            $tiposdocumentos = Transferenciatransitoexcel::find()
                ->select([
                    'centroOperacionDocumento',
                    'tipoDocumento',
                    'fechaDocumento',
                    'bodegaSalidaDocumento',
                    'bodegaEntradaDocumento'
                ])
                ->distinct()
                ->where(['idTransferenciaerp' => $id])
                ->orderBy([
                    'fechaDocumento' => SORT_ASC,
                    'centroOperacionDocumento' => SORT_ASC,
                    'tipoDocumento' => SORT_ASC
                ])->all();

            $error = Transferenciaerp::transferenciasalidaxtipodocumento(
                $id,
                $tiposdocumentos,
                $modelconector
            );



            self::obtenerConsecutivoSIESA($id);
        }

        return $error;
    }

    public static function transferenciasalidaxtipodocumento($id, $tiposdocumentos, $modelconector)
    {

        $error = 0;
        $conta = 1;
        foreach ($tiposdocumentos as $registro) {

            $numeroRegistros = Transferenciatransitoexcel::find()
                ->where(
                    [
                        'idTransferenciaerp' => $id,
                        'centroOperacionDocumento' => $registro->centroOperacionDocumento,
                        'tipoDocumento' => $registro->tipoDocumento,
                        'fechaDocumento' => $registro->fechaDocumento,
                        'bodegaSalidaDocumento' => $registro->bodegaSalidaDocumento,
                        'bodegaEntradaDocumento' => $registro->bodegaEntradaDocumento
                    ]
                )->count();

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

            $json = Transferenciaerp::transferenciaTransitoERP(
                $id,
                $registro->centroOperacionDocumento,
                $registro->tipoDocumento,
                $registro->fechaDocumento,
                $registro->bodegaSalidaDocumento,
                $registro->bodegaEntradaDocumento
            );

            /*if ($conta == 3){
                         var_dump($json); die("hola");
                     }
                     
                     $conta = $conta + 1;
                     continue;*/
            // var_dump($json);
            // die("hola JSON 2");

            if ($json == null) {
                $model = new Transferenciaerperror();
                $model->idTransferenciaerp = $id;
                $model->centroOperacionDocumento = $registro->centroOperacionDocumento;
                $model->tipoDocumento = $registro->tipoDocumento;
                $model->fechaDocumento = $registro->fechaDocumento;
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

            $respuesta = Transferenciaerp::ejecutartransferenciaWS($url, $headers, $json);

            $codigo = Transferenciaerp::errortransferenciaWS(
                $id,
                $registro->centroOperacionDocumento,
                $registro->tipoDocumento,
                $registro->fechaDocumento,
                null,
                $respuesta
            );

            $modellog->endDate = date('Y-m-d H:i:s');
            $modellog->mensaje = $codigo;
            $modellog->save();

            /*if(!$modellog->save()){
                var_dump($modellog->getErrors());die("hola");
            };*/

            if ($codigo != 0) {
                $error = 1;
            }
        }

        return $error;
    }


    public static function ejecutartransferenciaWS($url, $headers, $json)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setUrl($url)
            ->setMethod('POST')
            ->setHeaders($headers)
            ->setContent($json)
            ->send();
        return $response->content;
    }

    public static function errortransferenciaWS_Back($id, $CO, $tipoDocumento, $fechaDocumento = null, $consecutivo = null, $respuesta = null)
    {
        $data = json_decode($respuesta, true); // Convertir JSON a array asociativo

        if (isset($data['errors']['conniKey']) || isset($data['errors']['conniToken'])) {
            $model = new Transferenciaerperror();
            $model->idTransferenciaerp = $id;
            $model->centroOperacionDocumento = $CO;
            $model->tipoDocumento = $tipoDocumento;
            $model->fechaDocumento = $fechaDocumento;
            $model->consecutivo = $consecutivo;
            $model->detalle = 'The conniKey field is required / The conniToken field is required';

            $model->save();

            return 1;
        }

        if (!isset($data['codigo'])) {
            $model = new Transferenciaerperror();
            $model->idTransferenciaerp = $id;
            $model->centroOperacionDocumento = $CO;
            $model->tipoDocumento = $tipoDocumento;
            $model->fechaDocumento = $fechaDocumento;
            $model->consecutivo = $consecutivo;
            $model->detalle = 'No Existe Comunicación Con WS SIESA';

            $model->save();

            return 1;
        }

        //var_dump($data); die(' ' . $CO . ' - ' . $tipoDocumento);

        $codigo = $data['codigo'];
        //$mensaje = $data['mensaje'];
        //$f_detalle = '';

        if ($codigo != 0) {
            $error = 1;

            if (isset($data['detalle']) && is_array($data['detalle'])) {
                // Recorrer y extraer datos si 'detalle' es un array
                foreach ($data['detalle'] as $detalle) {

                    $model = new Transferenciaerperror();
                    $model->idTransferenciaerp = $id;
                    $model->centroOperacionDocumento = $CO;
                    $model->tipoDocumento = $tipoDocumento;
                    $model->fechaDocumento = $fechaDocumento;
                    $model->consecutivo = $consecutivo;
                    $model->numeroLinea = $detalle['f_nro_linea'];
                    $model->tipoRegistro = $detalle['f_tipo_reg'];
                    $model->subTipoRegistro = $detalle['f_subtipo_reg'];
                    $model->version = $detalle['f_version'];
                    $model->nivel = $detalle['f_nivel'];
                    $model->valor = $detalle['f_valor'];
                    $model->detalle = $detalle['f_detalle'];

                    $model->save();
                }
            } elseif (isset($data['detalle']) && is_string($data['detalle'])) {
                // Imprimir el detalle si 'detalle' es una cadena de texto
                $model = new Transferenciaerperror();
                $model->idTransferenciaerp = $id;
                $model->centroOperacionDocumento = $CO;
                $model->tipoDocumento = $tipoDocumento;
                $model->fechaDocumento = $fechaDocumento;
                $model->consecutivo = $consecutivo;
                $model->detalle = $data['detalle'];

                $model->save();
                //var_dump($data);echo("-------");var_dump($model->getErrors());die("hola");
            }

            if (isset($data['mensaje']) && (!isset($data['detalle']))) {
                $model = new Transferenciaerperror();
                $model->idTransferenciaerp = $id;
                $model->centroOperacionDocumento = $CO;
                $model->tipoDocumento = $tipoDocumento;
                $model->fechaDocumento = $fechaDocumento;
                $model->consecutivo = $consecutivo;
                $model->detalle = $data['mensaje'];

                $model->save();
                //var_dump($data);echo("2.-------");var_dump($model->getErrors());die("hola");
            }
        }

        return $codigo;
    }

    public static function transferenciaTransitoERP($id, $co, $tipodocumento, $fechadocumento, $bodegasalida, $bodegaentrada)
    {

        $registros = Transferenciatransitoexcel::find()
            ->where(
                [
                    'idTransferenciaerp' => $id,
                    'centroOperacionDocumento' => $co,
                    'tipoDocumento' => $tipodocumento,
                    'fechaDocumento' => $fechadocumento,
                    'bodegaSalidaDocumento' => $bodegasalida,
                    'bodegaEntradaDocumento' => $bodegaentrada
                ]
            )
            ->orderBy([
                'fechaDocumento' => SORT_ASC,
                'centroOperacionDocumento' => SORT_ASC,
                'tipoDocumento' => SORT_ASC,
                'bodegaSalidaDocumento' => SORT_ASC,
                'bodegaEntradaDocumento' => SORT_ASC,
            ])->all();

        // Arreglo para almacenar los datos
        $jsonArray = [];
        $nroregistro = 0;

        // Recorrer los registros y construir el JSON
        foreach ($registros as $registro) {

            $documentoKey = $registro->centroOperacion . '-' .
                $registro->tipoDocumentoMovimiento . '-' .
                $registro->transferenciaerp->documento . '-' .
                $registro->fechaDocumento . '-' .
                $registro->bodegaSalidaDocumento . '-' .
                $registro->bodegaEntradaDocumento;

            $nroregistro = $nroregistro + 1;

            $notas = $registro->notas;
            if ($registro->codigoBarras) {
                $notas .= ' - ' . $registro->codigoBarras;
            }

            $movimiento = [
                'f470_id_co' => $registro->centroOperacion,
                'f470_id_tipo_docto' => $registro->tipoDocumentoMovimiento,
                'f470_consec_docto' => $registro->transferenciaerp->documento,
                'f470_nro_registro' => $nroregistro,
                'f470_id_bodega' => trim($registro->bodegaSalidaMovimiento),
                'f470_id_motivo' => '01',
                'f470_id_co_movto' => $registro->centroOperacionMovimiento,
                'f470_id_unidad_medida' => $registro->unidadSalida,
                'f470_cant_base' => $registro->cantidadBase,
                'f470_costo_prom_uni' => $registro->costoPromedioUnitario,
                //'f470_notas' => $registro->transferenciaerp->notas,
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
                        //'f350_notas' => $registro->transferenciaerp->descripcion,
                        'f350_notas' => $registro->notas,
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
                //'f350_notas' => $registro->transferenciaerp->descripcion,
                'f350_notas' => $registro->notas,
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

    public static function errortransferenciaWS($id, $CO, $tipoDocumento, $respuesta, $fechaDocumento = null, $consecutivo = null, $numero = null)
    {
        $data = json_decode($respuesta, true); // Convertir JSON a array asociativo

        if (isset($data['errors']['conniKey']) || isset($data['errors']['conniToken'])) {
            $model = new Transferenciaerperror();
            $model->idTransferenciaerp = $id;
            $model->centroOperacionDocumento = $CO;
            $model->tipoDocumento = $tipoDocumento;
            $model->fechaDocumento = $fechaDocumento;
            $model->consecutivo = $consecutivo;
            $model->numero = $numero;
            $model->detalle = 'The conniKey field is required / The conniToken field is required';

            $model->save();

            return 1;
        }

        if (!isset($data['codigo'])) {
            $model = new Transferenciaerperror();
            $model->idTransferenciaerp = $id;
            $model->centroOperacionDocumento = $CO;
            $model->tipoDocumento = $tipoDocumento;
            $model->fechaDocumento = $fechaDocumento;
            $model->consecutivo = $consecutivo;
            $model->numero = $numero;
            $model->detalle = 'No Existe Comunicación Con WS SIESA';

            $model->save();

            return 1;
        }

        //var_dump($data); die(' ' . $CO . ' - ' . $tipoDocumento);

        $codigo = $data['codigo'];
        //$mensaje = $data['mensaje'];
        //$f_detalle = '';

        if ($codigo != 0) {
            $error = 1;

            if (isset($data['detalle']) && is_array($data['detalle'])) {
                // Recorrer y extraer datos si 'detalle' es un array
                foreach ($data['detalle'] as $detalle) {

                    $model = new Transferenciaerperror();
                    $model->idTransferenciaerp = $id;
                    $model->centroOperacionDocumento = $CO;
                    $model->tipoDocumento = $tipoDocumento;
                    $model->fechaDocumento = $fechaDocumento;
                    $model->consecutivo = $consecutivo;
                    $model->numeroLinea = $detalle['f_nro_linea'];
                    $model->tipoRegistro = $detalle['f_tipo_reg'];
                    $model->subTipoRegistro = $detalle['f_subtipo_reg'];
                    $model->version = $detalle['f_version'];
                    $model->nivel = $detalle['f_nivel'];
                    $model->valor = $detalle['f_valor'];
                    $model->detalle = $detalle['f_detalle'];
                    $model->numero = $numero;

                    $model->save();
                    //echo("consecutivo: " . $consecutivo);
                    //var_dump($model->getErrors()); die("stop");
                }
            } elseif (isset($data['detalle']) && is_string($data['detalle'])) {
                // Imprimir el detalle si 'detalle' es una cadena de texto
                $model = new Transferenciaerperror();
                $model->idTransferenciaerp = $id;
                $model->centroOperacionDocumento = $CO;
                $model->tipoDocumento = $tipoDocumento;
                $model->fechaDocumento = $fechaDocumento;
                $model->consecutivo = $consecutivo;
                $model->numero = $numero;
                $model->detalle = $data['detalle'];

                $model->save();
                //var_dump($data);echo("-------");var_dump($model->getErrors());die("hola");
            }

            if (isset($data['mensaje']) && (!isset($data['detalle']))) {
                $model = new Transferenciaerperror();
                $model->idTransferenciaerp = $id;
                $model->centroOperacionDocumento = $CO;
                $model->tipoDocumento = $tipoDocumento;
                $model->fechaDocumento = $fechaDocumento;
                $model->consecutivo = $consecutivo;
                $model->numero = $numero;
                $model->detalle = $data['mensaje'];

                $model->save();
                //var_dump($data);echo("2.-------");var_dump($model->getErrors());die("hola");
            }
        }

        return $codigo;
    }

    public static function entradaAlmacenInteWS($id)
    {

        $error = 0;

        $modeltransferencia = Transferenciaerp::findOne(['id' => $id]);
        if ($modeltransferencia == null) {
            $model = new Transferenciaerperror();
            $model->idTransferenciaerp = $id;
            $model->centroOperacionDocumento = null;
            $model->tipoDocumento = null;
            $model->detalle = 'No Existe Modelo Transferencia ERP';
            $model->save();
            $error = 1;
        }

        $modelconector = Conectoresdinamicos::findOne(['id' => $modeltransferencia->idConectorDinamico]);

        if ($modelconector == null) {
            $model = new Transferenciaerperror();
            $model->idTransferenciaerp = $id;
            $model->centroOperacionDocumento = null;
            $model->tipoDocumento = null;
            $model->detalle = 'No Existe Conector Dinamico SIESA';
            $model->save();
            $error = 1;
        }

        if ($error == 0) {

            $numRegistrosBorrados = Transferenciaerperror::deleteAll(['idTransferenciaerp' => $id]);

            $ordenescompra = Transferenciaordencompraexcel::find()
                ->select([
                    'centroOperacionOrdenCompra',
                    'tipoDocumentoOrdenCompra',
                    'consecutivoOrdenCompra'
                ])
                ->distinct()
                ->where(['idTransferenciaerp' => $id])
                ->orderBy([
                    'centroOperacionOrdenCompra' => SORT_ASC,
                    'tipoDocumentoOrdenCompra' => SORT_ASC,
                    'consecutivoOrdenCompra' => SORT_ASC
                ])->all();

            $error = Transferenciaerp::entradaalmacenintxoc(
                $id,
                $ordenescompra,
                $modelconector
            );
        }

        return $error;
    }

    public static function entradaalmacenintxoc($id, $ordenescompra, $modelconector)
    {

        $error = 0;
        foreach ($ordenescompra as $registro) {

            $numeroRegistros = Transferenciaordencompraexcel::find()
                ->where(
                    [
                        'idTransferenciaerp' => $id,
                        'centroOperacionOrdenCompra' => $registro->centroOperacionOrdenCompra,
                        'tipoDocumentoOrdenCompra' => $registro->tipoDocumentoOrdenCompra,
                        'consecutivoOrdenCompra' => $registro->consecutivoOrdenCompra
                    ]
                )->count();

            $modellog = new Transferencialogws();
            $modellog->idTransferenciaerp = $id;
            $modellog->centroOperacionDocumento = $registro->centroOperacionOrdenCompra;
            $modellog->tipoDocumento = $registro->tipoDocumentoOrdenCompra;
            $modellog->consecutivoOrdenCompra = $registro->consecutivoOrdenCompra;

            $modellog->startDate = date('Y-m-d H:i:s');
            $modellog->idConectorDinamico = $modelconector->id;
            $modellog->numeroRegistros = $numeroRegistros;

            $json = Transferenciaerp::entradaalmacenintERP(
                $id,
                $registro->centroOperacionOrdenCompra,
                $registro->tipoDocumentoOrdenCompra,
                $registro->consecutivoOrdenCompra
            );

            // var_dump($json);die("Hola JSON 1");
            /*if ($registro->consecutivoOrdenCompra == 1101){			
                         var_dump($json); die("hola");
                     }*/




            if ($json == null) {
                $model = new Transferenciaerperror();
                $model->idTransferenciaerp = $id;
                $model->centroOperacionDocumento = $registro->centroOperacionOrdenCompra;
                $model->tipoDocumento = $registro->tipoDocumentoOrdenCompra;
                $model->detalle = 'No Existen Datos Para Importar (JSON): ' . $registro->centroOperacionOrdenCompra . '-' . $registro->tipoDocumentoOrdenCompra;
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

            if (Yii::$app->user->id == '17') {
                var_dump($url);
                var_dump('  <----- ' . '  -------> ');
                var_dump($headers);
                var_dump('  <----- ' . '  -------> ');

                var_dump($json);
                die("hola->entradas");

            }

            $respuesta = Transferenciaerp::ejecutartransferenciaWS($url, $headers, $json);

            $codigo = Transferenciaerp::errortransferenciaWS(
                $id,
                $registro->centroOperacionOrdenCompra,
                $registro->tipoDocumentoOrdenCompra,
                $respuesta,
                $registro->consecutivoOrdenCompra,
                $respuesta
            );

            $modellog->endDate = date('Y-m-d H:i:s');
            $modellog->mensaje = $codigo;
            $modellog->save();

            if ($codigo != 0) {
                $error = 1;
            }
        }

        return $error;
    }

    public static function entradaalmacenintERP($id, $co, $tipodocumento, $consecutivo)
    {

        $registros = Transferenciaordencompraexcel::find()
            ->where([
                'idTransferenciaerp' => $id,
                'centroOperacionOrdenCompra' => $co,
                'tipoDocumentoOrdenCompra' => $tipodocumento,
                'consecutivoOrdenCompra' => $consecutivo
            ])
            ->andWhere(['>', 'cantidadBase', 0])
            ->orderBy([
                'centroOperacionDocumento' => SORT_ASC,
                'tipoDocumento' => SORT_ASC,
                'consecutivoDocumento' => SORT_ASC,
                'tercero' => SORT_ASC,
                'numeroFactura' => SORT_ASC,
            ])->all();

        // Arreglo para almacenar los datos
        $jsonArray = [];
        $nroregistro = 0;

        // Recorrer los registros y construir el JSON
        foreach ($registros as $registro) {

            $documentoKey = $registro->centroOperacionDocumento . '-' .
                $registro->tipoDocumento . '-' .
                $registro->consecutivoDocumento . '-' .
                $registro->tercero . '-' .
                $registro->numeroFactura;

            $nroregistro = $nroregistro + 1;

            $numero_formateado = str_pad($registro->rowid, 7, "0", STR_PAD_LEFT);
            //$numero_formateado = "0000000";

            $cantidad_formateado = str_pad($registro->cantidadBase, 15, "0", STR_PAD_LEFT) . '.' . '0000';

            $movimiento = [
                'f470_id_co' => trim($registro->centroOperacionMovimiento),
                'f470_id_tipo_docto' => trim($registro->tipoDocumentoMovimiento),
                'f470_consec_docto' => trim($registro->consecutivoMovimiento),
                'f470_nro_registro' => trim($registro->numeroRegistroMovimiento),
                // 'f470_id_bodega' => trim($registro->bodegaMovimiento,
                'f470_id_bodega' => trim($registro->bodegaMovimiento),
                'f470_id_unidad_medida' => trim($registro->unidadMovimiento),
                'f421_fecha_entrega' => trim($registro->fechaEntregaMovimiento),
                'f470_cant_base' => trim($cantidad_formateado),
                'f470_notas' => trim($registro->transferenciaerp->notas),
                'f470_id_item' => trim($registro->item),
                'f470_id_ext1_detalle' => trim($registro->color),
                'f470_id_ext2_detalle' => trim($registro->talla),
                'f470_rowid' => trim($numero_formateado),
            ];

            if (!isset($jsonArray[$documentoKey])) {
                $registros = 0;
                $jsonArray[$documentoKey] = [
                    'Documentos' => [
                        'f350_id_co' => $registro->centroOperacionDocumento,
                        'f350_id_tipo_docto' => $registro->tipoDocumento,
                        'f350_consec_docto' => $registro->consecutivoDocumento,
                        'f350_fecha' => $registro->fechaDocumento,
                        'f350_id_tercero' => $registro->tercero,
                        'f350_notas' => $registro->transferenciaerp->descripcion,
                        'f451_id_sucursal_prov' => $registro->sucursal,
                        'f451_id_tercero_comprador' => $registro->idTerceroComprador,
                        'f451_num_docto_referencia' => $registro->numeroFactura,
                        'f451_ind_consignacion' => $registro->consignacion,
                        'f420_id_co_docto' => $registro->centroOperacionOrdenCompra,
                        'f420_id_tipo_docto' => $registro->tipoDocumentoOrdenCompra,
                        'f420_consec_docto' => $registro->consecutivoOrdenCompra,
                    ],

                    'Movimientos' => [],
                ];
            }

            $documento = [
                'f350_id_co' => $registro->centroOperacionDocumento,
                'f350_id_tipo_docto' => $registro->tipoDocumento,
                'f350_consec_docto' => $registro->consecutivoDocumento,
                'f350_fecha' => $registro->fechaDocumento,
                'f350_id_tercero' => $registro->tercero,
                'f350_notas' => $registro->transferenciaerp->descripcion,
                'f451_id_sucursal_prov' => $registro->sucursal,
                'f451_id_tercero_comprador' => $registro->idTerceroComprador,
                'f451_num_docto_referencia' => $registro->numeroFactura,
                'f451_ind_consignacion' => $registro->consignacion,
                'f420_id_co_docto' => $registro->centroOperacionOrdenCompra,
                'f420_id_tipo_docto' => $registro->tipoDocumentoOrdenCompra,
                'f420_consec_docto' => $registro->consecutivoOrdenCompra,
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


    public static function generarExcelGenericTransfer($id)
    {

        $registros = Transferenciaordencompraexcel::find()
            ->where(['idTransferenciaerp' => $id])
            ->orderBy([
                'centroOperacionDocumento' => SORT_ASC,
                'tipoDocumento' => SORT_ASC,
                'consecutivoDocumento' => SORT_ASC,
                'tercero' => SORT_ASC,
                'numeroFactura' => SORT_ASC,
            ])->all();

        // Arreglo para almacenar los datos
        $jsonArray = [];
        $nroregistro = 0;

        // Recorrer los registros y construir el JSON
        foreach ($registros as $registro) {

            $documentoKey = $registro->centroOperacionDocumento . '-' .
                $registro->tipoDocumento . '-' .
                $registro->consecutivoDocumento . '-' .
                $registro->tercero . '-' .
                $registro->numeroFactura;

            $nroregistro = $nroregistro + 1;
            $numero_formateado = str_pad($nroregistro, 7, "0", STR_PAD_LEFT);
            $numero_formateado = "0000000";

            $cantidad_formateado = str_pad($registro->cantidadBase, 15, "0", STR_PAD_LEFT) . '.' . '0000';

            $movimiento = [
                'f470_id_co' => $registro->centroOperacionMovimiento,
                'f470_id_tipo_docto' => $registro->tipoDocumentoMovimiento,
                'f470_consec_docto' => $registro->consecutivoMovimiento,
                'f470_nro_registro' => $registro->numeroRegistroMovimiento,
                'f470_id_bodega' => $registro->bodegaMovimiento,
                'f470_id_unidad_medida' => $registro->unidadMovimiento,
                'f421_fecha_entrega' => $registro->fechaEntregaMovimiento,
                'f470_cant_base' => $cantidad_formateado,
                'f470_notas' => $registro->transferenciaerp->notas,
                'f470_id_item' => $registro->item,
                'f470_id_ext1_detalle' => $registro->color,
                'f470_id_ext2_detalle' => $registro->talla,
                'f470_rowid' => $numero_formateado,
            ];

            if (!isset($jsonArray[$documentoKey])) {
                $registros = 0;
                $jsonArray[$documentoKey] = [
                    'Documentos' => [
                        'f350_id_co' => $registro->centroOperacionDocumento,
                        'f350_id_tipo_docto' => $registro->tipoDocumento,
                        'f350_consec_docto' => $registro->consecutivoDocumento,
                        'f350_fecha' => $registro->fechaDocumento,
                        'f350_id_tercero' => $registro->tercero,
                        'f350_notas' => $registro->transferenciaerp->descripcion,
                        'f451_id_sucursal_prov' => $registro->sucursal,
                        'f451_id_tercero_comprador' => $registro->idTerceroComprador,
                        'f451_num_docto_referencia' => $registro->numeroFactura,
                        'f451_ind_consignacion' => $registro->consignacion,
                        'f420_id_co_docto' => $registro->centroOperacionOrdenCompra,
                        'f420_id_tipo_docto' => $registro->tipoDocumentoOrdenCompra,
                        'f420_consec_docto' => $registro->consecutivoOrdenCompra,
                    ],

                    'Movimientos' => [],
                ];
            }

            $documento = [
                'f350_id_co' => $registro->centroOperacionDocumento,
                'f350_id_tipo_docto' => $registro->tipoDocumento,
                'f350_consec_docto' => $registro->consecutivoDocumento,
                'f350_fecha' => $registro->fechaDocumento,
                'f350_id_tercero' => $registro->tercero,
                'f350_notas' => $registro->transferenciaerp->descripcion,
                'f451_id_sucursal_prov' => $registro->sucursal,
                'f451_id_tercero_comprador' => $registro->idTerceroComprador,
                'f451_num_docto_referencia' => $registro->numeroFactura,
                'f451_ind_consignacion' => $registro->consignacion,
                'f420_id_co_docto' => $registro->centroOperacionOrdenCompra,
                'f420_id_tipo_docto' => $registro->tipoDocumentoOrdenCompra,
                'f420_consec_docto' => $registro->consecutivoOrdenCompra,
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

    public static function obtenerConsecutivoSIESA($id)
    {
        $traspasos = Transferenciatransitoexcel::find()
            ->select([
                'tipoDocumento',
                'numero',
                'notas'
            ])
            ->distinct()
            ->where(['idTransferenciaerp' => $id])
            ->orderBy([
                'tipoDocumento' => SORT_ASC,
                'numero' => SORT_ASC,
            ])->all();

        foreach ($traspasos as $registro) {

            $tipodocumento = $registro['tipoDocumento'];
            $idgruma = str_ireplace("3TB", "", $registro['numero']);

            $traspasoSiesa = OrdendecompraSIESA::obtenerDatosDocumento($tipodocumento, $idgruma);
            Yii::trace('Buscar traspaso en siesa', __METHOD__);

            $guardoDatos = Documentosiesa::grabarDatos($traspasoSiesa, $idgruma);
        }
    }
}
