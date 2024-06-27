<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "conteobylecturacodigo".
 *
 * @property int $id
 * @property int $modulo
 * @property int $idConteoDestino
 * @property int $idConteoDetalle
 * @property string $codigoBarras
 * @property int $unidades
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 */
class Conteobylecturacodigo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conteobylecturacodigo';
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
            [['modulo', 'idConteoFactura', 'idConteoDestino', 'idConteoDetalle', 'codigoBarras'], 'required'],
            [['modulo', 'idConteoDestino', 'idConteoDetalle', 'unidades', 'created_by', 'updated_by', 
            'isMobile'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['codigoBarras'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'modulo' => 'Módulo',
            'idConteoFactura' => 'ID Conteo Factura',
            'idConteoDestino' => 'ID Conteo Destino',
            'idConteoDetalle' => 'ID Conteo Detalle',
            'codigoBarras' => 'Código Barras',
            'unidades' => 'Unidades',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'isMobile' => 'Dispositivo',
        ];
    }

    public function getItem()
    {
        return $this->hasOne(Item::class, ['codigoBarras' => 'codigoBarras']);
    }

    public function getDestino()
    {
        return $this->hasOne(Conteocdscdestino::class, ['id' => 'idConteoDestino']);
    }

    public function getFactura()
    {
        return $this->hasOne(Conteocdscdestinofactura::class, ['id' => 'idConteoFactura']);
    }

    public function getDetalle()
    {
        return $this->hasOne(Conteocdscdestinodetalle::class, ['id' => 'idConteoDetalle']);
    }
}
