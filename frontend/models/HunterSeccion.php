<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class HunterSeccion extends ActiveRecord
{
    public static function tableName()
    {
        return 'hunter_seccion';
    }

    public function rules()
    {
        return [
            [['id_bodega', 'codigo', 'nombre'], 'required'],
            [['id_bodega', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['codigo'], 'string', 'max' => 30],
            [['nombre'], 'string', 'max' => 100],
        ];
    }
}


