<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "Conteocdscusuariodestino".
 *
 * @property int $id
 * @property int $idConteocdscdestino
 * @property int $idUser
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Conteocdscdestino $idConteocdscdestino0
 */
class Conteocdscusuariodestino extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Conteocdscusuariodestino';
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
            [['idConteocdscdestino', 'idUser'], 'required'],
            [['idConteocdscdestino', 'idUser', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idConteocdscdestino'], 'exist', 'skipOnError' => true, 'targetClass' => Conteocdscdestino::class, 'targetAttribute' => ['idConteocdscdestino' => 'id']],
            [['idConteocdscdestino', 'idUser'], 'unique', 'targetAttribute' => ['idConteocdscdestino', 'idUser'], 'message' => 'Almacén YA Esta Relacionado Con el Usuario'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idConteocdscdestino' => 'Id Conteocdscdestino',
            'idUser' => 'Id User',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[IdConteocdscdestino0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdConteocdscdestino0()
    {
        return $this->hasOne(Conteocdscdestino::class, ['id' => 'idConteocdscdestino']);
    }
}
