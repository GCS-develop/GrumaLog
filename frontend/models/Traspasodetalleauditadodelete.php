<?php

namespace frontend\models;

use Yii;

use yii\web\NotFoundHttpException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
/**
 * This is the model class for table "traspasodetalleauditadodelete".
 *
 * @property int $id
 * @property int|null $idTraspaso
 * @property int|null $idItem
 * @property int|null $cantidad
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Item $idItem0
 * @property Traspaso $idTraspaso0
 */
class Traspasodetalleauditadodelete extends \yii\db\ActiveRecord
{
    public $item;
    public $talla;
    public $color;
    public $nombreActualizo;
    public $unidades;
    public $nombreCreo;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'traspasodetalleauditadodelete';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idTraspaso', 'idItem', 'cantidad', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idTraspaso'], 'exist', 'skipOnError' => true, 'targetClass' => Traspaso::class, 'targetAttribute' => ['idTraspaso' => 'id']],
            [['idItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['idItem' => 'id']],
        ];
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
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idTraspaso' => 'Id Traspaso',
            'idItem' => 'Id Item',
            'cantidad' => 'Cantidad',
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
    public function getIdItem0()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
    }

    /**
     * Gets query for [[IdTraspaso0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdTraspaso0()
    {
        return $this->hasOne(Traspaso::class, ['id' => 'idTraspaso']);
    }
}
