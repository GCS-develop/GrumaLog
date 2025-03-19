<?php

namespace frontend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "estadorecepcion".
 *
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 */
class Estadorecepcion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'estadorecepcion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigo', 'nombre', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['codigo'], 'string', 'max' => 2],
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
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function getNombre()
    {
        return 'o';
        // return $this->hasOne(Estadorecepcion::class, ['id' => 'idEstado']);
    }
    public static function getListaData()
    {
        $data = Estadorecepcion::find()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }

}
