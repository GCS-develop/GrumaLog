<?php

namespace frontend\modules\ventas\models;

use Yii;

/**
 * This is the model class for table "estadofactura".
 *
 * @property int $id
 * @property string $nombre
 * @property int $codigo
 */
class Estadofactura extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'estadofactura';
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
            [['nombre', 'codigo'], 'required'],
            [['codigo'], 'integer'],
            [['nombre'], 'string', 'max' => 45],
            [['nombre'], 'unique'],
            [['codigo'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'codigo' => 'Codigo',
        ];
    }
}
