<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
/**
 * This is the model class for table "siesa_envio_movimiento".
 *
 * @property int $id
 * @property int $documento_id
 * @property int|null $created_by
 * @property string|null $created_at
 * @property int|null $updated_by
 * @property string|null $updated_at
 *
 * @property SiesaConectorDocumento $documento
 * @property SiesaEnvioMovimientoValor[] $siesaEnvioMovimientoValors
 */
class SiesaEnvioMovimiento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'siesa_envio_movimiento';
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
            [['documento_id'], 'required'],
            [['documento_id', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['documento_id'], 'exist', 'skipOnError' => true, 'targetClass' => SiesaConectorDocumento::class, 'targetAttribute' => ['documento_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'documento_id' => 'Documento ID',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Documento]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocumento()
    {
        return $this->hasOne(SiesaConectorDocumento::class, ['id' => 'documento_id']);
    }

    /**
     * Gets query for [[SiesaEnvioMovimientoValors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiesaEnvioMovimientoValors()
    {
        return $this->hasMany(SiesaEnvioMovimientoValor::class, ['movimiento_id' => 'id']);
    }
    public function getValores()
    {
        return $this->hasMany(SiesaEnvioMovimientoValor::class, ['movimiento_id' => 'id']);
    }

}
