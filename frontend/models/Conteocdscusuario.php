<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "conteocdscusuario".
 *
 * @property int $id
 * @property int $idConteocdscdestinofactura
 * @property int $idUserConteo
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Conteocdscdestinofactura $conteocdscdestinofactura
 * @property Userconteocdsc $userconteo
 */
class Conteocdscusuario extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conteocdscusuario';
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
            [['idConteocdscdestinofactura', 'idUserConteo', 'idEstado',], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idConteocdscdestinofactura', 'idUserConteo', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idUserConteo'], 'exist', 'skipOnError' => true, 'targetClass' => Userconteocdsc::class, 'targetAttribute' => ['idUserConteo' => 'id']],
            [['idConteocdscdestinofactura'], 'exist', 'skipOnError' => true, 'targetClass' => Conteocdscdestinofactura::class, 'targetAttribute' => ['idConteocdscdestinofactura' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idConteocdscdestinofactura' => 'Id Conteocdscdestinofactura',
            'idUserConteo' => 'Usuario Conteo',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'idEstado' => 'Estado',
        ];
    }

    /**
     * Gets query for [[Conteocdscdestinofactura]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConteocdscdestinofactura()
    {
        return $this->hasOne(Conteocdscdestinofactura::class, ['id' => 'idConteocdscdestinofactura']);
    }

    /**
     * Gets query for [[UserConteo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserconteo()
    {
        return $this->hasOne(Userconteocdsc::class, ['id' => 'idUserConteo']);
    }
}
