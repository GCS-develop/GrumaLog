<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "unidadempaque".
 *
 * @property int $id
 * @property string|null $codigo
 * @property string|null $nombre
 * @property int|null $equivalencia
 */
class Unidadempaque extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'unidadempaque';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['equivalencia'], 'integer'],
            [['codigo'], 'string', 'max' => 10],
            [['nombre'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigo' => 'Codigo',
            'nombre' => 'Nombre',
            'equivalencia' => 'Equivalencia',
        ];
    }
}
