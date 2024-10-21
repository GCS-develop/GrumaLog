<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "parametroscontrol".
 *
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string $valor
 * @property string|null $tipoDato
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $update_by
 */
class Parametroscontrol extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'parametroscontrol';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigo', 'nombre', 'created_at', 'created_by', 'updated_at', 'update_by'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'update_by'], 'integer'],
            [['codigo'], 'string', 'max' => 3],
            [['nombre'], 'string', 'max' => 80],
            [['valor'], 'string', 'max' => 250],
            [['tipoDato'], 'string', 'max' => 20],
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
            'valor' => 'Valor',
            'tipoDato' => 'Tipo Dato',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'update_by' => 'Update By',
        ];
    }

    public static function getValorparametro ($codigo){
        $parametroscontrol = Parametroscontrol::find()->where(['codigo' => $codigo])->one();
        
        if ($parametroscontrol){
            return $parametroscontrol->valor;
        }

        return null;
    }
}
