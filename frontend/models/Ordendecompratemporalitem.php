<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "ordendecompratemporalitem".
 *
 * @property int $id
 * @property int $idOrdenCompra
 * @property int $numeroRegistro
 * @property int $idBodega
 * @property string $codigoMotivo
 * @property int $idCOMovimiento
 * @property int $cantidadPedida
 * @property string $fechaEntrega
 * @property float $precioUnitario
 * @property int $idItem
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Bodegas $bodega
 * @property Centrooperacion $comovimiento
 * @property Item $item
 * @property Ordendecompratemporal $ordencompra
 */
class Ordendecompratemporalitem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ordendecompratemporalitem';
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
            [['idOrdenCompra', 'numeroRegistro', 'idBodega', 'idCOMovimiento', 'cantidadPedida', 
            'fechaEntrega', 'precioUnitario', 'idColor', 'idTalla', 'item'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idOrdenCompra', 'numeroRegistro', 'idBodega', 'idCOMovimiento', 'cantidadPedida', 'idItem', 'created_by', 'updated_by'], 'integer'],
            [['fechaEntrega', 'created_at', 'updated_at', 'precioUnitario', 'idItem', ], 'safe'],
            //[['precioUnitario'], 'number'],
            [['codigoMotivo'], 'string', 'max' => 2],
            [['idOrdenCompra'], 'exist', 'skipOnError' => true, 'targetClass' => Ordendecompratemporal::class, 'targetAttribute' => ['idOrdenCompra' => 'id']],
            [['idBodega'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodega' => 'id']],
            [['idCOMovimiento'], 'exist', 'skipOnError' => true, 'targetClass' => Centrooperacion::class, 'targetAttribute' => ['idCOMovimiento' => 'id']],
            [['idItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['idItem' => 'id']],
            [['idOrdenCompra', 'item', 'idColor', 'idTalla'], 'unique', 'targetAttribute' => ['idOrdenCompra', 'item', 'idColor', 'idTalla'], 'message' => 'Item YA Existe En Esta Orden'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idOrdenCompra' => 'Orden Compra',
            'numeroRegistro' => 'No. Registro',
            'idBodega' => 'Bodega',
            'codigoMotivo' => 'Código Motivo',
            'idCOMovimiento' => 'CO Movto',
            'cantidadPedida' => 'Cantidad Pedida',
            'fechaEntrega' => 'Fecha Entrega',
            'precioUnitario' => 'Precio Unitario',
            'idItem' => 'Item',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'idColor' => 'Color',
            'idTalla' => 'Talla',
            'item' => 'Item'
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
     * Gets query for [[Comovimiento]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComovimiento()
    {
        return $this->hasOne(Centrooperacion::class, ['id' => 'idCOMovimiento']);
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
        return $this->hasOne(Ordendecompratemporal::class, ['id' => 'idOrdenCompra']);
    }

    public function getColor()
    {
        return $this->hasOne(Color::class, ['id' => 'idColor']);
    }

    public function getTalla()
    {
        return $this->hasOne(Talla::class, ['id' => 'idTalla']);
    }

    public static function actualizarRegistro($idordencompra){
        $items = Ordendecompratemporalitem::find()->where(['idOrdenCompra' => $idordencompra])->all();

        $numero = 0;
        foreach($items as $item){
            $model = Ordendecompratemporalitem::findOne(['id' => $item->id]);
            $model->numeroRegistro = $numero + 1;
            $numero += 1;
            $model->save();
        }
    }
}
