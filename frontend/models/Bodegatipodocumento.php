<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "bodegatipodocumento".
 *
 * @property int $id
 * @property int $idBodega
 * @property int $idTipoDocumento
 */
class Bodegatipodocumento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bodegatipodocumento';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idBodega', 'idTipoDocumento'], 'required'],
            [['idBodega', 'idTipoDocumento'], 'integer'],
        ];
    }

    public function getTipodocumento()
    {
        return $this->hasOne(Tipodocumento::class, ['id' => 'idTipoDocumento']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idBodega' => 'Id Bodega',
            'idTipoDocumento' => 'Id Tipo Documento',
        ];
    }
}
