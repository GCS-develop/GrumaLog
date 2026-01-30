<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "pedidoordendecompra".
 *
 * @property int $id
 * @property int $idPedido
 * @property int $idOrdenCompra
 * @property string|null $nombreArchivo
 * @property int|null $nroItems
 * @property int|null $totalUnidades
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Ordendecompra $ordencompra
 * @property Pedido $pedido
 * @property Pedidoordendecompraitem[] $pedidoordendecompraitems
 */
class Pedidoordendecompra extends \yii\db\ActiveRecord
{
    public $idCentroOperacion;
    public $idTipoDocumento;
    public $consecutivo;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pedidoordendecompra';
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
            [['idPedido', 'idOrdenCompra'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idPedido', 'idOrdenCompra', 'nroItems', 'totalUnidades', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at', 'idCentroOperacion', 'idTipoDocumento', 'consecutivo'], 'safe'],
            [['nombreArchivo'], 'string', 'max' => 150],
            [['idOrdenCompra'], 'exist', 'skipOnError' => true, 'targetClass' => Ordendecompra::class, 'targetAttribute' => ['idOrdenCompra' => 'id']],
            [['idPedido'], 'exist', 'skipOnError' => true, 'targetClass' => Pedido::class, 'targetAttribute' => ['idPedido' => 'id']],
            [['idPedido', 'idOrdenCompra'], 'unique', 'targetAttribute' => ['idPedido', 'idOrdenCompra'], 'message' => 'Orden de Compra ya está registrada En ESte Pedido'],
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
            'nombreArchivo' => 'Nombre Archivo',
            'nroItems' => 'Nro Items',
            'totalUnidades' => 'Total Unidades',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',

            'idCentroOperacion' => 'CO',
            'idTipoDocumento' => 'Serie',
            'consecutivo' => 'Consecutivo',
            'archivo' => 'Nombre Archivo',
        ];
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

    /**
     * Gets query for [[Pedidoordendecompraitems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPedidoordendecompraitems()
    {
        return $this->hasMany(Pedidoordendecompraitem::class, ['idPedidoOrdenCompra' => 'id']);
    }
}
