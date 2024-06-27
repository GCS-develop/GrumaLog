<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "conteocdscdestinodetalle".
 *
 * @property int $id
 * @property int $idConteocdscdestino
 * @property int $idItem
 * @property string $codigoBarras
 * @property float $totalUnidades
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Conteocdscdestino $idConteocdscdestino0
 * @property Item $item
 */
class Conteocdscdestinodetalle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conteocdscdestinodetalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idConteocdscdestino', 'idItem', 'codigoBarras', 'totalUnidades', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['idConteocdscdestino', 'idItem', 'created_by', 'updated_by'], 'integer'],
            [['totalUnidades'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['codigoBarras'], 'string', 'max' => 50],
            [['idConteocdscdestino'], 'exist', 'skipOnError' => true, 'targetClass' => Conteocdscdestino::class, 'targetAttribute' => ['idConteocdscdestino' => 'id']],
            [['idItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['idItem' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idConteocdscdestino' => 'Id Conteocdscdestino',
            'idItem' => 'Id Item',
            'codigoBarras' => 'Codigo Barras',
            'totalUnidades' => 'Total Unidades',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[IdConteocdscdestino0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdConteocdscdestino0()
    {
        return $this->hasOne(Conteocdscdestino::class, ['id' => 'idConteocdscdestino']);
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
    }
}
