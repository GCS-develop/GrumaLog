<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "grupo_concepto".
 *
 * @property int $id
 * @property string $nombre
 *
 * @property GrupoConceptoCuenta[] $grupoConceptoCuentas
 */
class GrupoConcepto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'grupo_concepto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['nombre'], 'string', 'max' => 100],
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
        ];
    }

    /**
     * Gets query for [[GrupoConceptoCuentas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGrupoConceptoCuentas()
    {
        return $this->hasMany(GrupoConceptoCuenta::class, ['grupo_id' => 'id']);
    }
}
