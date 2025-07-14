<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
/**
 * This is the model class for table "siesa_conector_documento".
 *
 * @property int $id
 * @property int $conector_id
 * @property string $nombre
 * @property string|null $descripcion
 * @property int|null $id_traspaso
 * @property int|null $created_by
 * @property string|null $created_at
 * @property int|null $updated_by
 * @property string|null $updated_at
 *
 * @property SiesaConector $conector
 * @property SiesaEnvioDocumentoValor[] $siesaEnvioDocumentoValors
 * @property SiesaEnvioMovimiento[] $siesaEnvioMovimientos
 * @property Traspaso $traspaso
 */
class SiesaConectorDocumento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'siesa_conector_documento';
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
            [['conector_id', 'nombre'], 'required'],
            [['conector_id', 'id_traspaso', 'created_by', 'updated_by'], 'integer'],
            [['descripcion'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['nombre'], 'string', 'max' => 255],
            [['conector_id'], 'exist', 'skipOnError' => true, 'targetClass' => SiesaConector::class, 'targetAttribute' => ['conector_id' => 'id']],
            [['id_traspaso'], 'exist', 'skipOnError' => true, 'targetClass' => Traspaso::class, 'targetAttribute' => ['id_traspaso' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'conector_id' => 'Conector ID',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripcion',
            'id_traspaso' => 'Id Traspaso',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Conector]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConector()
    {
        return $this->hasOne(SiesaConector::class, ['id' => 'conector_id']);
    }

    /**
     * Gets query for [[SiesaEnvioDocumentoValors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiesaEnvioDocumentoValors()
    {
        return $this->hasMany(SiesaEnvioDocumentoValor::class, ['documento_id' => 'id']);
    }

    /**
     * Gets query for [[SiesaEnvioMovimientos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiesaEnvioMovimientos()
    {
        return $this->hasMany(SiesaEnvioMovimiento::class, ['documento_id' => 'id']);
    }

    /**
     * Gets query for [[Traspaso]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraspaso()
    {
        return $this->hasOne(Traspaso::class, ['id' => 'id_traspaso']);
    }

    public function getValoresDocumento()
    {
        return $this->hasMany(SiesaEnvioDocumentoValor::class, ['documento_id' => 'id']);
    }

    public function getMovimientos()
    {
        return $this->hasMany(SiesaEnvioMovimiento::class, ['documento_id' => 'id']);
    }
    public function getCamposDocumento()
    {
        return $this->hasMany(SiesaConectorDocumentoCampo::class, ['conector_id' => 'conector_id']);
    }
    public function getCamposMovimiento()
    {
        return $this->hasMany(\frontend\models\SiesaConectorMovimientoCampo::class, ['conector_id' => 'conector_id']);
    }


}
