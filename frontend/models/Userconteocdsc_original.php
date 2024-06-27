<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "userconteocdsc".
 *
 * @property int $id
 * @property int $idUser
 * @property int $idEmpleadoLogistica
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Empleadologistica $EmpleadoLogistica
 * @property User $User
 */
class Userconteocdsc extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'userconteocdsc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idUser', 'idEmpleadoLogistica', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['idUser', 'idEmpleadoLogistica', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idUser'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['idUser' => 'id']],
            [['idEmpleadoLogistica'], 'exist', 'skipOnError' => true, 'targetClass' => Empleadologistica::class, 'targetAttribute' => ['idEmpleadoLogistica' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idUser' => 'Id User',
            'idEmpleadoLogistica' => 'Id Empleado Logistica',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[IdEmpleadoLogistica0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdEmpleadoLogistica0()
    {
        return $this->hasOne(Empleadologistica::class, ['id' => 'idEmpleadoLogistica']);
    }

    /**
     * Gets query for [[IdUser0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdUser0()
    {
        return $this->hasOne(User::class, ['id' => 'idUser']);
    }
}
