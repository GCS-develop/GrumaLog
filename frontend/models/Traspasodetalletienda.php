<?php

namespace frontend\models;

use Yii;
use yii\web\NotFoundHttpException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "traspasodetalletienda".
 *
 * @property int $id
 * @property int $idTraspaso
 * @property int $idItem
 * @property int $cantidad
 * @property int|null $idDocumentosiesa
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Item $idItem0
 * @property Traspaso $idTraspaso0
 */
class Traspasodetalletienda extends \yii\db\ActiveRecord
{

    public $item;
    public $talla;
    public $color;
    public $nombreActualizo;
    public $unidades;
    public $nombreCreo;
    public $fechaDesde;
    public $fechaHasta;
    public $cantidadTraspasoRegistros;
    public $cantidadTraspasounidades;
    public $diferencia;
    public $consecutivoSiesa;
    public $serie;
    public $Origen;
    public $Destino;
    public $userTraspaso;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'traspasodetalletienda';
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
            [['idTraspaso', 'idItem', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['idTraspaso', 'idItem', 'cantidad', 'idDocumentosiesa', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idTraspaso'], 'exist', 'skipOnError' => true, 'targetClass' => Traspaso::class, 'targetAttribute' => ['idTraspaso' => 'id']],
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
            'idTraspaso' => 'Id Traspaso',
            'idItem' => 'Id Item',
            'cantidad' => 'Cantidad',
            'idDocumentosiesa' => 'Id Documentosiesa',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[IdItem0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdItem()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
    }

    /**
     * Gets query for [[IdTraspaso0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdTraspaso()
    {
        return $this->hasOne(Traspaso::class, ['id' => 'idTraspaso']);
    }
}
