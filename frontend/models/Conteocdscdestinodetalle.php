<?php

namespace frontend\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;

use frontend\models\search\ConteocdscdestinodetalleSearch;

/**
 * This is the model class for table "conteocdscdestinodetalle".
 *
 * @property int $id
 * @property int $idConteocdscdestino
 * @property int $idItem
 * @property string $codigoBarras
 * @property float $totalUnidades
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Conteocdscdestino $idConteocdscdestino0
 * @property Item $item
 */
class Conteocdscdestinodetalle extends \yii\db\ActiveRecord
{
    public $codigoCentroOperacionDocumentoEntrada;
    public $codigoTipoDocumentoEntrada;
    public $consecutivoDocumentoEntrada;
    public $fechaDocumentoEntrada;
    public $codigoCentroOperacionOC;
    public $codigoTipoDoctoOC;
    public $consecutivoOC;
    public $codigointernomovto;
    public $tercero;
    public $numeroFactura;
    public $sucursalProveedor;
    public $consignacion;
    public $nitcomprador;
    public $bodega;
    public $fechaEntrega;
    public $unidadesConteo;
    public $talla;
    public $color;
    public $unidadEmpaque;
    public $unidades;
    public $item;
    public $bodegaSalidaDocumento;
    public $bodegaEntradaDocumento;
    public $centroOperacion;
    public $bodegaSalidaMovimiento;
    public $tipoDocumentoMovimiento;
    public $centroOperacionMovimiento;
    public $costoPromedioUnitario;
    public $equivalencia;
    public $centroOperacionDocumento;
    public $tipoDocumento;
    public $fechaDocumento;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conteocdscdestinodetalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idConteocdscdestino', 'idItem', 'codigoBarras', 'totalUnidades', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['idConteocdscdestino', 'idItem', 'created_by', 'updated_by'], 'integer'],
            [['totalUnidades'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['codigoBarras'], 'string', 'max' => 50],
            [['idConteocdscdestino'], 'exist', 'skipOnError' => true, 'targetClass' => Conteocdscdestino::class, 'targetAttribute' => ['idConteocdscdestino' => 'id']],
            [['idItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['idItem' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idConteocdscdestino' => 'Id Conteocdscdestino',
            'idItem' => 'Id Item',
            'codigoBarras' => 'Codigo Barras',
            'totalUnidades' => 'Total Unidades',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[IdConteocdscdestino0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdConteocdscdestino0()
    {
        return $this->hasOne(Conteocdscdestino::class, ['id' => 'idConteocdscdestino']);
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
    }

    public static function generarTransferenciaTraspasoERP($factura){
        $respuesta = [
            'id' => null,
            'mensaje' => '',
            'codigoError' => '',
        ];

        $logErp = null;
        if ($factura->idTransferenciatraspasoerp){
            $id = $factura->idTransferenciatraspasoerp;
            $transferenciaVieja = Transferenciaerp::findOne(['id' => $id]);
            if ($transferenciaVieja) {
                $logErp = Logtransferenciaerp::registrar($transferenciaVieja, 'AUTO_REGENERACION');
            }
            Transferenciaerperror::deleteAll(['idTransferenciaerp' => $id]);
            Transferenciatransitoexcel::deleteAll(['idTransferenciaerp' => $id]);
            Transferenciaerp::deleteAll(['id' => $id]);
        }

        $iddocumento = 165613;
        $numero = $factura->ordenCompra->tipoDocumento->codigo . '-' . $factura->ordenCompra->consecutivo;
        $descripcion =
                "Traspaso CDSC: " . $factura->id . ' - ' . 
                $factura->proveedor->razonSocial . ' - ' .
                'OC: ' . $numero . ' - ' . 
                'Factura: ' . $factura->numeroFactura . ' - ' .
                'Fecha: ' . $factura->fecha;

        $documento = $factura->numeroFactura;

        $numero = 'Traspaso CDSC: ' . $factura->id;

        //$notas = 'Transferencia Modulo CDSC GRUMALOG';
        $notas = $numero;

        $origen = 'R';
        $transferencia = Transferenciaerp::crearRegistro($iddocumento, $descripcion, $documento, $notas, $origen);

        $query = Conteocdscdestinodetalle::find()->alias('det');
        $query->join('LEFT JOIN', 'conteocdscdestino dest', 'det.idConteocdscdestino = dest.id');
        $query->join('LEFT JOIN', 'conteocdscdestinofactura fac', 'dest.idConteocdscdestinofactura = fac.id');
        $query->join('LEFT JOIN', 'ordendecompra oc', 'fac.idOrdenCompra = oc.id');
        $query->join('LEFT JOIN', 'tipodocumento td', 'oc.idTipoDocumento = td.id');
        $query->join('LEFT JOIN', 'proveedor prv', 'oc.idProveedor = prv.id');
        $query->join('LEFT JOIN', 'bodegas bo', 'dest.idCentroOperacion = bo.id');
        $query->join('LEFT JOIN', 'item it', 'det.idItem = it.id');
        $query->join('LEFT JOIN', 'color col', 'it.idColor = col.id');
        $query->join('LEFT JOIN', 'talla tal', 'it.idTalla = tal.id');
        $query->join('LEFT JOIN', 'bodegas bom', 'fac.idBodegaMovimiento = bom.id');
        $query->join('LEFT JOIN', 'tipodocumento tdm', 'fac.idTipoDocumentoMovimiento = tdm.id');
        $query->join('LEFT JOIN', 'centrooperacion com', 'fac.idCentroOperacionMovimiento = com.id');
        $query->join('LEFT JOIN', 'unidadempaque ue', "ISNULL(it.unidadEmpaque,'UND') = ue.codigo");

        $query->Select([
                "fac.id",
                "com.codigo AS centroOperacionDocumento", 
                "tdm.codigo AS tipoDocumento", 
                "FORMAT(fac.updated_at, 'yyyyMMdd') AS fechaDocumento",
                "bom.codigo AS bodegaSalidaDocumento",
                "bo.codigo AS bodegaEntradaDocumento",
                "com.codigo AS centroOperacion",
                "tdm.codigo AS tipoDocumentoMovimiento",
                "bom.codigo AS bodegaSalidaMovimiento",
                "com.codigo AS centroOperacionMovimiento",
                "ISNULL(it.unidadEmpaque,'UND') AS unidadSalida", 

                "det.totalUnidades AS cantidadBase" ,
                "(det.totalUnidades * ISNULL(ue.equivalencia, 1)) AS unidadesConteo",
                'ISNULL(ue.equivalencia,1) AS equivalencia',

                new Expression('0 AS costoPromedioUnitario'),
                "it.item",
                "col.nombre AS color",
                "tal.nombre AS talla",
                "ue.equivalencia"
        ]);

        $query->where(['fac.id' => $factura->id]);

        $results = Yii::$app->db->createCommand($query->createCommand()->getRawSql())->queryAll();

        // echo $query->createCommand()->getRawSql(); die ("")
        // Yii::debug($query->createCommand()->getRawSql(), 'debug');

        $fila = 0;
        $numeroRegistros = 0;
        foreach ($results as $detalle) {
            $modeltransferencia = new Transferenciatransitoexcel();
            $fila = $fila + 1;
            $unidad = $detalle['unidadSalida'];
            $modeltransferencia->idTransferenciaerp = $transferencia->id;
            $modeltransferencia->centroOperacionDocumento = $detalle['centroOperacionDocumento'];
            $modeltransferencia->tipoDocumento = $detalle['tipoDocumento'];
            $modeltransferencia->fechaDocumento = $detalle['fechaDocumento'];
            $modeltransferencia->bodegaSalidaDocumento = $detalle['bodegaSalidaDocumento'];
            $modeltransferencia->bodegaEntradaDocumento = $detalle['bodegaEntradaDocumento'];
            $modeltransferencia->centroOperacion = $detalle['centroOperacion'];
            $modeltransferencia->tipoDocumentoMovimiento = $detalle['tipoDocumentoMovimiento'];
            $modeltransferencia->bodegaSalidaMovimiento = $detalle['bodegaSalidaMovimiento'];
            $modeltransferencia->centroOperacionMovimiento = $detalle['centroOperacionMovimiento'];
            $modeltransferencia->unidadSalida = $unidad;
            $modeltransferencia->cantidadBase = $detalle['unidadesConteo'];
            $modeltransferencia->costoPromedioUnitario = $detalle['costoPromedioUnitario'];
            $modeltransferencia->item = $detalle['item'];
            $modeltransferencia->color = $detalle['color'];
            $modeltransferencia->talla = $detalle['talla'];
            $modeltransferencia->numero = $numero;
            $modeltransferencia->procesado = 0;
            $modeltransferencia->fila = $fila;
            $numeroRegistros++;

            $modeltransferencia->codigoUnidadEmpaque = $detalle['unidadSalida'];
            $modeltransferencia->unidadesConteoEmpaque = $detalle['cantidadBase'];

            if (!$modeltransferencia->save()){
                var_dump($modeltransferencia->getErrors()); die("stop");
            }
        }

        $respuesta['id'] = $transferencia->id;
        $respuesta['mensaje'] = "Proceso de Transferencia Finalizo Con Éxito";
        $respuesta['codigoError'] = 1;

        if ($logErp !== null) {
            $logErp->idTransferenciaerpNueva = $transferencia->id;
            $logErp->save(false);
        }

        return $respuesta;
    }

    public static function generarTransferenciaWS($factura)
    {

        $respuesta = [
            'mensaje' => '',
            'codigoError' => '',
        ];

        if ($factura->transferenciaerp == 0) {
            $iddocumento = 165613;
            $numero = $factura->ordenCompra->tipoDocumento->codigo . '-' . $factura->ordenCompra->consecutivo;
            $descripcion = $factura->proveedor->razonSocial . ' - ' .
                'OC: ' . $numero . ' - ' . 
                'Factura: ' . $factura->numeroFactura . ' - ' .
                'Fecha: ' . $factura->fecha;
            $documento = $factura->id;

            //$notas = 'Transferencia Modulo CDSC GRUMALOG';
            $numero = $factura->id;
            $notas = 'Crossdocking CDSC => ' . $numero;

            $origen = 'T';
            $transferencia = Transferenciaerp::crearRegistro($iddocumento, $descripcion, $documento, $notas, $origen);

            //$searchModel = new ConteocdscdestinodetalleSearch();
            //$dataProvider = $searchModel->searchSIESA($documento);

            $query = Conteocdscdestinodetalle::find()->alias('det');
            $query->join('LEFT JOIN', 'conteocdscdestino dest', 'det.idConteocdscdestino = dest.id');
            $query->join('LEFT JOIN', 'conteocdscdestinofactura fac', 'dest.idConteocdscdestinofactura = fac.id');
            $query->join('LEFT JOIN', 'ordendecompra oc', 'fac.idOrdenCompra = oc.id');
            $query->join('LEFT JOIN', 'tipodocumento td', 'oc.idTipoDocumento = td.id');
            $query->join('LEFT JOIN', 'proveedor prv', 'oc.idProveedor = prv.id');
            $query->join('LEFT JOIN', 'bodegas bo', 'dest.idCentroOperacion = bo.id');
            $query->join('LEFT JOIN', 'item it', 'det.idItem = it.id');
            $query->join('LEFT JOIN', 'color col', 'it.idColor = col.id');
            $query->join('LEFT JOIN', 'talla tal', 'it.idTalla = tal.id');
            $query->join('LEFT JOIN', 'bodegas bom', 'fac.idBodegaMovimiento = bom.id');
            $query->join('LEFT JOIN', 'tipodocumento tdm', 'fac.idTipoDocumentoMovimiento = tdm.id');
            $query->join('LEFT JOIN', 'centrooperacion com', 'fac.idBodegaMovimiento = com.id');
            $query->join('LEFT JOIN', 'unidadempaque ue', "ISNULL(it.unidadEmpaque,'UND') = ue.codigo");

            $query->Select([
                "fac.id",
                "com.codigo AS centroOperacionDocumento", 
                "tdm.codigo AS tipoDocumento", 
                "FORMAT(fac.updated_at, 'yyyyMMdd') AS fechaDocumento",
                "bom.codigo AS bodegaSalidaDocumento",
                "bo.codigo AS bodegaEntradaDocumento",
                "com.codigo AS centroOperacion",
                "tdm.codigo AS tipoDocumentoMovimiento",
                "bom.codigo AS bodegaSalidaMovimiento",
                "com.codigo AS centroOperacionMovimiento",
                "ISNULL(it.unidadEmpaque,'UND') AS unidadSalida", 
                "det.totalUnidades AS cantidadBase",
                new Expression('0 AS costoPromedioUnitario'),
                "it.item",
                "col.nombre AS color",
                "tal.nombre AS talla",
                "ue.equivalencia"
            ]);

            $query->where(['fac.id' => $factura->id]);

            $results = Yii::$app->db->createCommand($query->createCommand()->getRawSql())->queryAll();

            //Yii::debug($query->createCommand()->getRawSql(), 'debug');

            $fila = 0;
            $numeroRegistros = 0;
            foreach ($results as $detalle) {
                $modeltransferencia = new Transferenciatransitoexcel();

                $fila = $fila + 1;
                $unidad = $detalle['unidadSalida'];

                $modeltransferencia->idTransferenciaerp = $transferencia->id;
                $modeltransferencia->centroOperacionDocumento = $detalle['centroOperacionDocumento'];
                $modeltransferencia->tipoDocumento = $detalle['tipoDocumento'];
                $modeltransferencia->fechaDocumento = $detalle['fechaDocumento'];
                $modeltransferencia->bodegaSalidaDocumento = $detalle['bodegaSalidaDocumento'];
                $modeltransferencia->bodegaEntradaDocumento = $detalle['bodegaEntradaDocumento'];
                $modeltransferencia->centroOperacion = $detalle['centroOperacion'];
                $modeltransferencia->tipoDocumentoMovimiento = $detalle['tipoDocumentoMovimiento'];
                $modeltransferencia->bodegaSalidaMovimiento = $detalle['bodegaSalidaMovimiento'];
                $modeltransferencia->centroOperacionMovimiento = $detalle['centroOperacionMovimiento'];
                $modeltransferencia->unidadSalida = $unidad;
                $modeltransferencia->cantidadBase = $detalle['cantidadBase'];
                $modeltransferencia->costoPromedioUnitario = $detalle['costoPromedioUnitario'];
                $modeltransferencia->item = $detalle['item'];
                $modeltransferencia->color = $detalle['color'];
                $modeltransferencia->talla = $detalle['talla'];
                $modeltransferencia->numero = $numero;
                $modeltransferencia->procesado = 0;
                $modeltransferencia->fila = $fila;
                $numeroRegistros++;

                if (!$modeltransferencia->save()){
                    var_dump($modeltransferencia->getErrors()); die("stop");
                }
            }

            /*if ($fila > 0) {

                $envioWS = 0;
                $model = Transferenciaerp::findOne(['id' => $transferencia->id]);
                $model->numeroRegistros = $numeroRegistros;

                $envioWS = Transferenciatransitoexcel::transferenciaSalidaWS($transferencia->id, Yii::$app->user->identity->username);

                if ($envioWS == 0) {
                    $respuesta['mensaje'] = "Proceso de Transferencia Finalizo Con Éxito";
                    $respuesta['codigoError'] = 1;

                    $model->enviadoWS = 1;
                } else {
                    $respuesta['mensaje'] = "Proceso de Transferencia Presenta Inconsistencia";
                    $respuesta['codigoError'] = 0;
                    //Yii::$app->session->setFlash( 'error', $mensaje);
                    $model->enviadoWS = 0;
                }
                $model->save();
            }else{
                $respuesta['mensaje'] = "Proceso de Transferencia No Tiene Registros";
                $respuesta['codigoError'] = 0;
            }

            */
        }else{
            $respuesta['mensaje'] = "Proceso Transferencia SIESA YA Fue Generada";
            $respuesta['codigoError'] = 0;
        }

        return $respuesta;
    }

    public static function generarTraspasodesdecdsc($idconteofactura){


        $modelfactura = Conteocdscdestinofactura::findOne(['id' => $idconteofactura]);

        $dataProviderDestino = $modelfactura->getConteocdscdestinos()
            ->andFilterWhere([
                'idConteocdscdestinofactura' => $idconteofactura,
            ])
            ->all(); // Obtener los datos como un arreglo

            $puerto = '9100';
    
        try{
    
            foreach ($dataProviderDestino as $destino) {

                Conteocdscdestino::generarTraspasoEncabezado($destino);
    
                $dataProviderDetalle = $destino->getConteocdscdestinodetalles()
                    ->where(['>', 'totalUnidades', 0])
                    ->all();
    
                $totales = Conteocdscdestino::generarTraspasoDetalle($dataProviderDetalle);
    
                Conteocdscdestino::generarTraspasoPiePagina ($destino, $totales);
            }
            
    
            return true;
    
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die("hola ....");
        }

        return false;
    }
}
