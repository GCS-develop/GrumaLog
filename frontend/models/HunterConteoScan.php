<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $id_conteo
 * @property string $ean
 * @property int $cantidad
 * @property string $created_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $updated_at
 * @property string|null $dispositivo
 *
 * @property HunterConteo $conteo
 */
class HunterConteoScan extends ActiveRecord
{
    public static function tableName()
    {
        return 'hunter_conteo_scan';
    }

    public function rules()
    {
        return [
            [['id_conteo', 'ean'], 'required'],
            [['id_conteo', 'cantidad', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['ean', 'dispositivo'], 'string', 'max' => 50],
            [
                ['id_conteo'],
                'exist',
                'skipOnError' => true,
                'targetClass' => HunterConteo::class,
                'targetAttribute' => ['id_conteo' => 'id']
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'id_conteo'   => 'Conteo',
            'ean'         => 'EAN',
            'cantidad'    => 'Cantidad',
            'dispositivo' => 'Dispositivo',
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
}
