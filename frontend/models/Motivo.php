<?php

namespace frontend\models;

use yii\helpers\ArrayHelper;

use Yii;

/**
 * This is the model class for table "motivo".
 *
 * @property string $id
 * @property string|null $descripcion
 */
class Motivo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'motivo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'string', 'max' => 2],
            [['descripcion'], 'string', 'max' => 255],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    /* public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'descripcion' => 'Descripcion',
        ];
    }*/


    public static function getListaMotivos()
    {
        $data = Motivo::find()
            ->select(['id', new \yii\db\Expression("CAST(id AS VARCHAR) + ' - ' + descripcion AS nombre")])
            ->orderBy('nombre')
            ->asArray()
            ->all();

        return ArrayHelper::map($data, 'id', 'nombre');
    }
}
