<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "transferenciaordencompraexcel".
 *
 * @property int $id
 * @property int $idTransferenciaerp
 * @property string $centroOperacionDocumento
 * @property string $tipoDocumento
 * @property int $consecutivoDocumento
 * @property string $fechaDocumento
 * @property string $tercero
 * @property string $numeroFactura
 * @property string $sucursal
 * @property string $idTerceroComprador
 * @property int $consignacion
 * @property string $centroOperacionOrdenCompra
 * @property string $tipoDocumentoOrdenCompra
 * @property int $consecutivoOrdenCompra
 * @property string $centroOperacionMovimiento
 * @property string $tipoDocumentoMovimiento
 * @property int $consecutivoMovimiento
 * @property int $numeroRegistroMovimiento
 * @property string $bodegaMovimiento
 * @property string $unidadMovimiento
 * @property string $fechaEntregaMovimiento
 * @property int $cantidadBase
 * @property int $item
 * @property string $color
 * @property string $talla
 * @property string $rowid
 */
class Transferenciaordencompraexcel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transferenciaordencompraexcel';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idTransferenciaerp', 'centroOperacionDocumento', 'tipoDocumento', 
            'consecutivoDocumento', 'fechaDocumento', 'tercero', 'numeroFactura', 
            'sucursal', 'idTerceroComprador', 'consignacion', 'centroOperacionOrdenCompra', 
            'tipoDocumentoOrdenCompra', 'consecutivoOrdenCompra', 'centroOperacionMovimiento', 
            'tipoDocumentoMovimiento', 'consecutivoMovimiento', 'numeroRegistroMovimiento', 
            'bodegaMovimiento', 'unidadMovimiento', 'fechaEntregaMovimiento', 'cantidadBase', 
            'item', 'color', 'talla', 'rowid'], 'required'],
            [['idTransferenciaerp', 'consecutivoDocumento', 'consignacion', 
            'consecutivoOrdenCompra', 'consecutivoMovimiento', 
            'numeroRegistroMovimiento', 'cantidadBase', 'item'], 'integer'],
            /*[['centroOperacionDocumento', 'tipoDocumento', 'sucursal', 
            'centroOperacionOrdenCompra', 'tipoDocumentoOrdenCompra', 
            'centroOperacionMovimiento', 'tipoDocumentoMovimiento', 'bodegaMovimiento', 
            'unidadMovimiento'], 'string', 'max' => 5],*/
            //[['fechaDocumento', 'fechaEntregaMovimiento'], 'string', 'max' => 10],
            //[['tercero', 'numeroFactura', 'idTerceroComprador'], 'string', 'max' => 15],
            [['color'], 'string', 'max' => 20],
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
            'consecutivoDocumento' => 'Consecutivo Documento',
            'fechaDocumento' => 'Fecha Documento',
            'tercero' => 'Tercero',
            'numeroFactura' => 'Numero Factura',
            'sucursal' => 'Sucursal',
            'idTerceroComprador' => 'Id Tercero Comprador',
            'consignacion' => 'Consignacion',
            'centroOperacionOrdenCompra' => 'Centro Operacion Orden Compra',
            'tipoDocumentoOrdenCompra' => 'Tipo Documento Orden Compra',
            'consecutivoOrdenCompra' => 'Consecutivo Orden Compra',
            'centroOperacionMovimiento' => 'Centro Operacion Movimiento',
            'tipoDocumentoMovimiento' => 'Tipo Documento Movimiento',
            'consecutivoMovimiento' => 'Consecutivo Movimiento',
            'numeroRegistroMovimiento' => 'Numero Registro Movimiento',
            'bodegaMovimiento' => 'Bodega Movimiento',
            'unidadMovimiento' => 'Unidad Movimiento',
            'fechaEntregaMovimiento' => 'Fecha Entrega Movimiento',
            'cantidadBase' => 'Cantidad Base',
            'item' => 'Item',
            'color' => 'Color',
            'talla' => 'Talla',
            'rowid' => 'ROWID'
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
}
