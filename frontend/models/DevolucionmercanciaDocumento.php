<?php

namespace frontend\modules\contabilidad\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "devolucionmercancia_documento".
 *
 * @property int $id
 * @property string $tipo_documento
 * @property string $fecha_documento
 * @property string $tercero_proveedor
 * @property string $sucursal_proveedor
 * @property string $prefijo_documento_proveedor
 * @property string $consecutivo_documento_proveedor
 * @property string $fecha_documento_proveedor
 * @property string $condicion_pago
 * @property string $valor_documento
 * @property string $tipo_proveedor
 * @property string $estado
 * @property string|null $respuesta_siesa
 * @property string $created_at
 * @property string $updated_at
 *
 * @property DevolucionmercanciaDetalle[] $detalles
 */
class DevolucionmercanciaDocumento extends ActiveRecord
{
    public static function tableName()
    {
        return 'devolucionmercancia_documento';
    }

    public function rules()
    {
        return [
            [['tipo_documento', 'fecha_documento', 'tercero_proveedor', 'sucursal_proveedor', 'prefijo_documento_proveedor', 'consecutivo_documento_proveedor', 'fecha_documento_proveedor', 'condicion_pago', 'valor_documento', 'tipo_proveedor'], 'required'],
            [['fecha_documento', 'fecha_documento_proveedor', 'created_at', 'updated_at'], 'safe'],
            [['valor_documento'], 'number'],
            [['respuesta_siesa'], 'string'],
            [['tipo_documento', 'tercero_proveedor', 'sucursal_proveedor', 'prefijo_documento_proveedor', 'consecutivo_documento_proveedor', 'condicion_pago', 'tipo_proveedor', 'estado'], 'string', 'max' => 50],
        ];
    }

    public function getDetalles()
    {
        return $this->hasMany(DevolucionmercanciaDetalle::class, ['documento_id' => 'id']);
    }
}
