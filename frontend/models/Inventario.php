<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "inventario".
 *
 * @property int $id
 * @property string $codigoBarras
 * @property int $idItem
 * @property string $codigoBodega
 * @property float $existencia
 * @property string $fechaUltimaActualizacion
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Bodegas $codigoBodega0
 * @property Item $idItem0
 */
class Inventario extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inventario';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigoBarras', 'idItem', 'codigoBodega', 'existencia', 'fechaUltimaActualizacion', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['idItem', 'created_by', 'updated_by'], 'integer'],
            [['existencia'], 'number'],
            [['fechaUltimaActualizacion', 'created_at', 'updated_at'], 'safe'],
            [['codigoBarras'], 'string', 'max' => 50],
            [['codigoBodega'], 'string', 'max' => 5],
            [['idItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['idItem' => 'id']],
            [['codigoBodega'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['codigoBodega' => 'codigo']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigoBarras' => 'Codigo Barras',
            'idItem' => 'Id Item',
            'codigoBodega' => 'Codigo Bodega',
            'existencia' => 'Existencia',
            'fechaUltimaActualizacion' => 'Fecha Ultima Actualizacion',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[CodigoBodega0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoBodega0()
    {
        return $this->hasOne(Bodegas::class, ['codigo' => 'codigoBodega']);
    }

    /**
     * Gets query for [[IdItem0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdItem0()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
    }
}
