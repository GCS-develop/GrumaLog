<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "ordendecompra".
 *
 * @property int $id
 * @property int|null $idCO
 * @property int|null $idTipoDocumento
 * @property float|null $consecutivo
 * @property string|null $fecha
 * @property int|null $idProveedor
 * @property int|null $idEstado
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Centrooperacion $CO
 * @property Proveedor $Proveedor
 * @property Tipodocumento $TipoDocumento
 */
class Ordendecompra extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ordendecompra';
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
            [['idCO', 'idTipoDocumento', 'idProveedor', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [['consecutivo'], 'number'],
            [['fecha', 'created_at', 'updated_at', 'fechaEntrega'], 'safe'],
            [['idTipoDocumento'], 'exist', 'skipOnError' => true, 'targetClass' => Tipodocumento::class, 'targetAttribute' => ['idTipoDocumento' => 'id']],
            [['idCO'], 'exist', 'skipOnError' => true, 'targetClass' => Centrooperacion::class, 'targetAttribute' => ['idCO' => 'id']],
            [['idProveedor'], 'exist', 'skipOnError' => true, 'targetClass' => Proveedor::class, 'targetAttribute' => ['idProveedor' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idCO' => 'Id Co',
            'idTipoDocumento' => 'Id Tipo Documento',
            'consecutivo' => 'Consecutivo',
            'fecha' => 'Fecha',
            'idProveedor' => 'Id Proveedor',
            'idEstado' => 'Id Estado',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'fechaEntrega' => 'Fecha Entrega',
        ];
    }

    /**
     * Gets query for [[CO]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCO()
    {
        return $this->hasOne(Centrooperacion::class, ['id' => 'idCO']);
    }

    /**
     * Gets query for [[Proveedor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProveedor()
    {
        return $this->hasOne(Proveedor::class, ['id' => 'idProveedor']);
    }

    /**
     * Gets query for [[TipoDocumento]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTipoDocumento()
    {
        return $this->hasOne(Tipodocumento::class, ['id' => 'idTipoDocumento']);
    }

    public function getPurchaseOrderItems()
{
    return $this->hasMany(Ordendecompradetalle::className(), ['idOrdenCompra' => 'id']);
}
}
