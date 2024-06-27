<?php

namespace frontend\models;

use Yii;

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
            [['idTransferenciaerp', 'cantidadBase', 'item', 'numero'], 'integer'],
            [['costoPromedioUnitario'], 'number'],
            /*[['centroOperacionDocumento', 'tipoDocumento', 'bodegaSalidaDocumento', 
            'bodegaEntradaDocumento', 'centroOperacion', 'tipoDocumentoMovimiento', 
            'bodegaSalidaMovimiento', 'centroOperacionMovimiento'], 'string', 'max' => 5],*/
            //[['fechaDocumento', 'unidadSalida'], 'string', 'max' => 10],
            [['color'], 'string', 'max' => 50],
            //[['talla'], 'string', 'max' => 20],
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
}
