<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "pedidodetalle".
 *
 * @property int $id
 * @property int $idPedido
 * @property int $idOrdenCompra
 * @property int $idItem
 * @property int $idBodega
 * @property int|null $unidades
 * @property int|null $unidadesRecibidas
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Bodegas $bodega
 * @property Item $item
 * @property Ordendecompra $ordencompra
 * @property Pedido $pedido
 */
class Pedidodetalle extends \yii\db\ActiveRecord
{

    // --- propiedades para resultados agregados / alias de SELECT ---
    public $total_unidades;
    public $total_recibidas;
    public $completo;
    public $bodega_codigo;
    public $bodega_nombre;
    public $oc_consecutivo;
    public $item_item;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pedidodetalle';
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
            [
                ['idPedido', 'idOrdenCompra', 'idItem', 'idBodega'],
                'required',
                'message' => '{attribute} Es Un Valor Obligatorio'
            ],
            [['idPedido', 'idOrdenCompra', 'idItem', 'idBodega', 'unidades', 'unidadesRecibidas', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idOrdenCompra'], 'exist', 'skipOnError' => true, 'targetClass' => Ordendecompra::class, 'targetAttribute' => ['idOrdenCompra' => 'id']],
            [['idPedido'], 'exist', 'skipOnError' => true, 'targetClass' => Pedido::class, 'targetAttribute' => ['idPedido' => 'id']],
            [['idBodega'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodega' => 'id']],
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
            'unidades' => 'Unidades',
            'unidadesRecibidas' => 'Unidades Recibidas',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
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
     * Gets query for [[Pedido]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPedido()
    {
        return $this->hasOne(Pedido::class, ['id' => 'idPedido']);
    }

    public function getPedidoordencompraitem()
    {
        return $this->hasOne(Pedidoordendecompraitem::class, [
            'idPedido'      => 'idPedido',
            'idOrdenCompra' => 'idOrdenCompra',
            'idItem'        => 'idItem',
        ]);
    }

    public function getOrdencompradetalle()
    {
        return $this->hasOne(Ordendecompradetalle::class, [
            'idOrdenCompra' => 'idOrdenCompra',
            'idItem'        => 'idItem',
        ]);
    }
}
