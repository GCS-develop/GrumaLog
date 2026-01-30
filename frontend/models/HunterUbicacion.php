<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
class HunterUbicacion extends ActiveRecord
{
    public static function tableName()
    {
        return 'hunter_ubicacion';
    }

    public function rules()
    {
        return [
            [['id_bodega', 'codigo'], 'required'],
            [['id_bodega', 'id_seccion', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['codigo'], 'string', 'max' => 30],
            [['descripcion'], 'string', 'max' => 100],
        ];
    }
}