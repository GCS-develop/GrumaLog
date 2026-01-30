<?php

namespace app\models;

use frontend\models\Item;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $id_conteo
 * @property int|null $id_item
 * @property int $cantidad_contada
 * @property float|null $cantidad_sistemaSiesa
 * @property float|null $diferencia
 * @property int $id_bodega
 * @property int|null $id_seccion
 * @property int|null $id_ubicacion
 * @property int|null $created_by
 * @property string $created_at
 * @property int|null $updated_by
 * @property string $updated_at
 *
 * @property HunterConteo $conteo
 * @property Item $item
 */
class HunterConteoDetalle extends ActiveRecord
{
    public static function tableName()
    {
        return 'hunter_conteo_detalle';
    }

    public function rules()
    {
        return [
            [['id_conteo', 'cantidad_contada', 'id_bodega'], 'required'],
            [['id_conteo', 'id_item', 'cantidad_contada', 'id_bodega', 'id_seccion', 'id_ubicacion', 'created_by', 'updated_by'], 'integer'],
            [['cantidad_sistemaSiesa', 'diferencia'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [
                ['id_conteo'],
                'exist',
                'skipOnError' => true,
                'targetClass' => HunterConteo::class,
                'targetAttribute' => ['id_conteo' => 'id']
            ],
            // Asumiendo modelo Item:
            // [['id_item'], 'exist', 'skipOnError' => true,
            //    'targetClass' => Item::class,
            //    'targetAttribute' => ['id_item' => 'id']
            // ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'                 => 'ID',
            'id_conteo'          => 'Conteo',
            'id_item'            => 'Item',
            'cantidad_contada'   => 'Cantidad contada',
            'cantidad_sistemaSiesa' => 'Cantidad sistema Siesa',
            'diferencia'         => 'Diferencia',
            'id_bodega'          => 'Bodega',
            'id_seccion'         => 'Sección',
            'id_ubicacion'       => 'Ubicación',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $userId = !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        $now    = date('Y-m-d H:i:s');

        if ($insert) {
            if ($this->created_at === null) {
                $this->created_at = $now;
            }
            if ($this->created_by === null) {
                $this->created_by = $userId;
            }
        }

        $this->updated_at = $now;
        $this->updated_by = $userId;

        return true;
    }

    public function getConteo()
    {
        return $this->hasOne(HunterConteo::class, ['id' => 'id_conteo']);
    }

    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'id_item']);
    }
}
