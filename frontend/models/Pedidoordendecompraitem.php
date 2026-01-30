<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "pedidoordendecompraitem".
 *
 * @property int $id
 * @property int $idPedido
 * @property int $idOrdenCompra
 * @property int $idItem
 * @property int $idBodega
 * @property int $totalUnidades
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Bodegas $bodega
 * @property Item $item
 * @property Pedidoordendecompra $ordencompra
 * @property Pedidodetalle[] $pedidodetalles
 */
class Pedidoordendecompraitem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pedidoordendecompraitem';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function ($event) {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idPedido', 'idOrdenCompra', 'idItem', 'idBodega', 'totalUnidades'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idPedido', 'idOrdenCompra', 'idItem', 'idBodega', 'totalUnidades', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idBodega'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodega' => 'id']],
            [['idOrdenCompra'], 'exist', 'skipOnError' => true, 'targetClass' => Ordendecompra::class, 'targetAttribute' => ['idOrdenCompra' => 'id']],
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
            'idPedido' => 'Pedido',
            'idOrdenCompra' => 'Orden Compra',
            'idItem' => 'Item',
            'idBodega' => 'Bodega',
            'totalUnidades' => 'Total Unidades',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Pedido]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPedido()
    {
        return $this->hasOne(Pedido::class, ['id' => 'idPedido']);
    }

    /**
     * Gets query for [[Bodega]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBodega()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idBodega']);
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

    /**
     * Gets query for [[Ordencompra]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrdencompra()
    {
        return $this->hasOne(Ordendecompra::class, ['id' => 'idOrdenCompra']);
    }

    /**
     * Gets query for [[Pedidodetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPedidodetalles()
    {
        return $this->hasMany(Pedidodetalle::class, ['idPedido' => 'id']);
    }

    public function getOrdencompradetalle()
    {
        return $this->hasOne(Ordendecompradetalle::class, [
            'idOrdenCompra' => 'idOrdenCompra',
            'idItem'        => 'idItem',
        ]);
    }
}
