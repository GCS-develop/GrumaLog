<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "siesa_conector_movimiento_campo".
 *
 * @property int $id
 * @property int $conector_id
 * @property string $nombre_campo
 * @property string|null $alias
 * @property string|null $tipo_dato
 * @property int|null $obligatorio
 * @property int|null $created_by
 * @property string|null $created_at
 * @property int|null $updated_by
 * @property string|null $updated_at
 *
 * @property SiesaConector $conector
 * @property SiesaEnvioMovimientoValor[] $siesaEnvioMovimientoValors
 */
class SiesaConectorMovimientoCampo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'siesa_conector_movimiento_campo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['conector_id', 'nombre_campo'], 'required'],
            [['conector_id', 'obligatorio', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['nombre_campo', 'alias'], 'string', 'max' => 255],
            [['tipo_dato'], 'string', 'max' => 50],
            [['conector_id'], 'exist', 'skipOnError' => true, 'targetClass' => SiesaConector::class, 'targetAttribute' => ['conector_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'conector_id' => 'Conector ID',
            'nombre_campo' => 'Nombre Campo',
            'alias' => 'Alias',
            'tipo_dato' => 'Tipo Dato',
            'obligatorio' => 'Obligatorio',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Conector]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConector()
    {
        return $this->hasOne(SiesaConector::class, ['id' => 'conector_id']);
    }

    /**
     * Gets query for [[SiesaEnvioMovimientoValors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiesaEnvioMovimientoValors()
    {
        return $this->hasMany(SiesaEnvioMovimientoValor::class, ['campo_id' => 'id']);
    }
}
