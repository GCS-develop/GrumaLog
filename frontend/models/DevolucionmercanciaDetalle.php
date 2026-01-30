<?php

namespace frontend\modules\contabilidad\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "devolucionmercancia_detalle".
 *
 * @property int $id
 * @property int $documento_id
 * @property string $item
 * @property string $extension1
 * @property string $extension2
 * @property string $unidad
 * @property string $bodega
 * @property string $motivo
 * @property float $cantidad
 * @property float $precio_unitario
 * @property float $costo_total
 *
 * @property DevolucionmercanciaDocumento $documento
 */
class DevolucionmercanciaDetalle extends ActiveRecord
{
    public static function tableName()
    {
        return 'devolucionmercancia_detalle';
    }

    public function rules()
    {
        return [
            [['documento_id', 'item', 'unidad', 'bodega', 'motivo', 'cantidad', 'precio_unitario', 'costo_total'], 'required'],
            [['documento_id'], 'integer'],
            [['cantidad', 'precio_unitario', 'costo_total'], 'number'],
            [['item', 'extension1', 'extension2', 'unidad', 'bodega', 'motivo'], 'string', 'max' => 100],
        ];
    }

    public function getDocumento()
    {
        return $this->hasOne(DevolucionmercanciaDocumento::class, ['id' => 'documento_id']);
    }
}
