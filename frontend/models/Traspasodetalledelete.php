<?php

namespace frontend\models;

use Yii;
use yii\web\NotFoundHttpException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "traspasodetalledelete".
 *
 * @property int $id
 * @property int|null $idTraspaso
 * @property int|null $idItem
 * @property int|null $cantidad
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Traspasodetalledelete extends \yii\db\ActiveRecord
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
        return 'traspasodetalledelete';
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
            [['idTraspaso', 'idItem', 'cantidad', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
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
    public static function findModelByIdTraspaso($idTraspaso)
    {
        if (($model = Traspasodetalledelete::findOne(['idTraspaso' => $idTraspaso])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('El registro solicitado no existe.');
    }


}
