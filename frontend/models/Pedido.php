<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use common\models\User;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "pedido".
 *
 * @property int $id
 * @property string $fecha
 * @property string|null $observaciones
 * @property int $nroOrdenesCompra
 * @property int $totalUnidades
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Bodegas $bodega
 * @property Pedidodetalle[] $pedidodetalles
 */
class Pedido extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pedido';
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
            [['observaciones'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['fecha', 'created_at', 'updated_at'], 'safe'],
            [['observaciones'], 'string'],
            [['nroOrdenesCompra', 'totalUnidades', 'created_by', 'updated_by'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'No. Pedido',
            'fecha' => 'Fecha',
            'observaciones' => 'Observaciones',
            'nroOrdenesCompra' => 'No. OC',
            'totalUnidades' => 'Total Unidades',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[created_at]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreadopor()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
    public static function getListaData()
    {
        $data = self::find()
            ->select([
                'id',
                // CONCAT maneja NULL como vacío y convierte tipos automáticamente
                new \yii\db\Expression(
                    "CONCAT(CAST(id AS varchar(20)), ' - ', ISNULL(observaciones, ''), ' - ', CAST(totalUnidades AS varchar(20))) AS nombre"
                ),
            ])
            ->orderBy(['id' => SORT_DESC]) // o ->orderBy('nombre') si prefieres por texto
            ->asArray()
            ->all();

        return \yii\helpers\ArrayHelper::map($data, 'id', 'nombre');
    }
}
