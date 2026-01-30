<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "grupo_concepto_cuenta".
 *
 * @property int $id
 * @property int $grupo_id
 * @property string $cuenta
 * @property string|null $descripcion
 * @property string $naturaleza
 *
 * @property GrupoConcepto $grupo
 */
class GrupoConceptocuenta extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'grupo_concepto_cuenta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['grupo_id', 'cuenta', 'naturaleza'], 'required'],
            [['grupo_id'], 'integer'],
            [['cuenta'], 'string', 'max' => 20],
            [['descripcion'], 'string', 'max' => 150],
            [['naturaleza'], 'string', 'max' => 10],
            [['grupo_id'], 'exist', 'skipOnError' => true, 'targetClass' => GrupoConcepto::class, 'targetAttribute' => ['grupo_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'grupo_id' => 'Grupo ID',
            'cuenta' => 'Cuenta',
            'descripcion' => 'Descripcion',
            'naturaleza' => 'Naturaleza',
        ];
    }

    /**
     * Gets query for [[Grupo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGrupo()
    {
        return $this->hasOne(GrupoConcepto::class, ['id' => 'grupo_id']);
    }
}
