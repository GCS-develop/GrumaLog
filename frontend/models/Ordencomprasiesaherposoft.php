<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "ordencomprasiesaherposoft".
 *
 * @property int $id
 * @property string $serie
 * @property int $numeroOrdenCompra
 * @property int $numeroOrdenCompraHS
 * @property int $radicadoAgenda
 */
class Ordencomprasiesaherposoft extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ordencomprasiesaherposoft';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['serie', 'numeroOrdenCompra', 'numeroOrdenCompraHS', 'radicadoAgenda'], 'required'],
            [['numeroOrdenCompra', 'numeroOrdenCompraHS', 'radicadoAgenda'], 'integer'],
            [['serie'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'serie' => 'Serie',
            'numeroOrdenCompra' => 'Numero Orden Compra',
            'numeroOrdenCompraHS' => 'Numero Orden Compra Hs',
            'radicadoAgenda' => 'Radicado Agenda',
        ];
    }
}
