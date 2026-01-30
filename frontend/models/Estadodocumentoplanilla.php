<?php

namespace frontend\models;
use yii\helpers\ArrayHelper;

use Yii;

/**
 * This is the model class for table "estadodocumentoplanilla".
 *
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string $created_at
 * @property int $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property TipodocumentodespachoRegla[] $tipodocumentodespachoReglas
 * @property TipodocumentodespachoRegla[] $tipodocumentodespachoReglas0
 */
class Estadodocumentoplanilla extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'estadodocumentoplanilla';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigo', 'nombre', 'created_at', 'created_by'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['codigo'], 'string', 'max' => 3],
            [['nombre'], 'string', 'max' => 60],
            [['codigo'], 'unique'],
            [['nombre'], 'unique'],
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

    /**
     * Gets query for [[TipodocumentodespachoReglas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTipodocumentodespachoReglas()
    {
        return $this->hasMany(TipodocumentodespachoRegla::class, ['idEstadoRequerido' => 'id']);
    }

    /**
     * Gets query for [[TipodocumentodespachoReglas0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTipodocumentodespachoReglas0()
    {
        return $this->hasMany(TipodocumentodespachoRegla::class, ['idEstadoNuevo' => 'id']);
    }

    public static function getListaData()
    {
        $data = Estadodocumentoplanilla::find()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }
}
