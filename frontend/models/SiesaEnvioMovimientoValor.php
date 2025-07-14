<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
/**
 * This is the model class for table "siesa_envio_movimiento_valor".
 *
 * @property int $id
 * @property int $movimiento_id
 * @property int $campo_id
 * @property string|null $valor
 * @property int|null $created_by
 * @property string|null $created_at
 * @property int|null $updated_by
 * @property string|null $updated_at
 *
 * @property SiesaConectorMovimientoCampo $campo
 * @property SiesaEnvioMovimiento $movimiento
 */
class SiesaEnvioMovimientoValor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'siesa_envio_movimiento_valor';
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
            [['movimiento_id', 'campo_id'], 'required'],
            [['movimiento_id', 'campo_id', 'created_by', 'updated_by'], 'integer'],
            [['valor'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['campo_id'], 'exist', 'skipOnError' => true, 'targetClass' => SiesaConectorMovimientoCampo::class, 'targetAttribute' => ['campo_id' => 'id']],
            [['movimiento_id'], 'exist', 'skipOnError' => true, 'targetClass' => SiesaEnvioMovimiento::class, 'targetAttribute' => ['movimiento_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'movimiento_id' => 'Movimiento ID',
            'campo_id' => 'Campo ID',
            'valor' => 'Valor',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Campo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCampo()
    {
        return $this->hasOne(SiesaConectorMovimientoCampo::class, ['id' => 'campo_id']);
    }

    /**
     * Gets query for [[Movimiento]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMovimiento()
    {
        return $this->hasOne(SiesaEnvioMovimiento::class, ['id' => 'movimiento_id']);
    }
}
