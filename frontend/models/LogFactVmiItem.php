<?php

namespace frontend\models;

use yii\db\ActiveRecord;

/**
 * Modelo para la tabla "LogFactVmiItem"
 *
 * @property int $id
 * @property int $log_id
 * @property string $item
 * @property string|null $extension1
 * @property string|null $extension2
 * @property string $bodega
 * @property float $cantidad
 * @property float $precio_unitario
 * @property float $costo_total
 *
 * @property LogFactVmi $log
 */
class LogFactVmiItem extends ActiveRecord
{
    public static function tableName()
    {
        return 'LogFactVmiItem';
    }


      public function rules()
    {
        return [
            [['log_id', 'item', 'bodega'], 'required'],
            [['log_id'], 'integer'],
            [['cantidad', 'precio_unitario', 'costo_total'], 'number'],
            [['item', 'extension1', 'extension2', 'codigo_barras'], 'string', 'max' => 50],
            [['bodega'], 'string', 'max' => 20],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'log_id'         => 'Documento VMI',
            'item'           => 'Item',
            'extension1'     => 'Ext1',
            'extension2'     => 'Ext2',
            'bodega'         => 'Bodega',
            'cantidad'       => 'Cantidad',
            'precio_unitario'=> 'Precio Unitario',
            'costo_total'    => 'Costo Total',
            'codigo_barras'  => 'Código de Barras', // 👈 nuevo
        ];
    }

    public function getLog()
    {
        return $this->hasOne(LogFactVmi::class, ['id' => 'log_id']);
    }

      public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (!empty($this->bodega)) {
                $this->bodega = str_pad(trim($this->bodega), 3, '0', STR_PAD_LEFT);
            }
            return true;
        }
        return false;
    }


}
