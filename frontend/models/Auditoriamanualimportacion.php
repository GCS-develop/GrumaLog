<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use common\models\User;

/**
 * This is the model class for table "auditoriamanualimportacion".
 *
 * @property int $id
 * @property int|null $numeroRegistros
 * @property float|null $totalCantidad
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Auditoriamanualimportaciondetalle[] $auditoriamanualimportaciondetalles
 */
class Auditoriamanualimportacion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auditoriamanualimportacion';
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
            [['numeroRegistros', 'created_by', 'updated_by'], 'integer'],
            [['totalCantidad'], 'number'],
            [['numeroRegistros', 'totalCantidad'], 'required'],
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
            'numeroRegistros' => 'Numero Registros',
            'totalCantidad' => 'Total Cantidad',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Auditoriamanualimportaciondetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuditoriamanualimportaciondetalles()
    {
        return $this->hasMany(Auditoriamanualimportaciondetalle::class, ['idInterfase' => 'id']);
    }

    public function getUsuariocrea()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
}
