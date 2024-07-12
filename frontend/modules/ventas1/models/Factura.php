<?php

namespace frontend\modules\ventas\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "factura".
 *
 * @property int $id
 * @property string $centroOperacion
 * @property string $tipoDocumento
 * @property int $consecutivoDocumento
 * @property string $fechaDocumento
 * @property string $idProveedor
 * @property float $documentoProveedor
 * @property string $codigoSucursal
 * @property string $prefijoDocumentoProveedor
 * @property int $consecutivoDocumentoProveedor
 * @property string $fechaDocumentoProveedor
 * @property string $condicionPago
 * @property string $tipoProveedor
 * @property float $valorDocumento
 * @property float $porcentajeCuota
 * @property string $fechaVencimientoCuota
 * @property string $fechaProntoPago
 * @property string $fechaDesde
 * @property string $fechaHasta
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Facturadetalle[] $facturadetalles
 */
class Factura extends \yii\db\ActiveRecord
{
    public $tieneNotaCredito;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'factura';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
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
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbVentasPOS');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipoDocumento', 'fechaDocumento', 'idProveedor', 'codigoSucursal', 
            'prefijoDocumentoProveedor', 'consecutivoDocumentoProveedor', 
            'fechaDocumentoProveedor', 'valorDocumento', 'fechaVencimientoCuota', 
            'fechaProntoPago', 'fechaDesde', 'fechaHasta'], 'required',
            'message' => 'El campo {attribute} es un valor obligatorio'
            ],
            [['consecutivoDocumento', 'consecutivoDocumentoProveedor', 'created_by', 
            'updated_by', 'idProveedor', 'idEstado', 'tieneNotaCredito'], 'integer'],
            [['fechaDocumento', 'fechaDocumentoProveedor', 'fechaVencimientoCuota', 'fechaProntoPago', 'fechaDesde', 'fechaHasta', 'created_at', 'updated_at'], 'safe'],
            [['valorDocumento', 'porcentajeCuota'], 'number'],
            [['centroOperacion', 'condicionPago'], 'string', 'max' => 3],
            [['tipoDocumento', 'codigoSucursal', 'prefijoDocumentoProveedor', 'tipoProveedor'], 'string', 'max' => 5],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'centroOperacion' => 'Centro Operación',
            'tipoDocumento' => 'Tipo Documento',
            'consecutivoDocumento' => 'Consecutivo Documento',
            'fechaDocumento' => 'Fecha Documento',
            'idProveedor' => 'Proveedor',
            'codigoSucursal' => 'Sucursal',
            'prefijoDocumentoProveedor' => 'Prefijo Documento Proveedor',
            'consecutivoDocumentoProveedor' => 'Documento Proveedor',
            'fechaDocumentoProveedor' => 'Fecha Documento Proveedor',
            'condicionPago' => 'Condición Pago',
            'tipoProveedor' => 'Tipo Proveedor',
            'valorDocumento' => 'Valor Documento',
            'porcentajeCuota' => 'Porcentaje Cuota',
            'fechaVencimientoCuota' => 'Fecha Vencimiento Cuota',
            'fechaProntoPago' => 'Fecha Pronto Pago',
            'fechaDesde' => 'Fecha Desde',
            'fechaHasta' => 'Fecha Hasta',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'idEstado' => 'Estado',
            'tieneNotaCredito' => 'Tiene NC',
        ];
    }

    /**
     * Gets query for [[Facturadetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFacturadetalles()
    {
        return $this->hasMany(Facturadetalle::class, ['idFactura' => 'id']);
    }

    public function getProveedor()
    {
        return $this->hasOne(Proveedor::class, ['id' => 'idProveedor']);
    }

}
