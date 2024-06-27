<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;

use common\models\User;

/**
 * This is the model class for table "userbodega".
 *
 * @property int $id
 * @property int $idUser
 * @property int $idEmpleado
 * @property int $idEstado
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Bodegas $bodega
 * @property User $user
 */
class Userbodega extends \yii\db\ActiveRecord
{
    public $nombreEmpleado;
    public $identificacion;
    public $username;
    public $email;
    public $retypePassword;
    public $password;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'userbodega';
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
            [['username', 'idEmpleado', 'email', 'password', 'retypePassword'], 'required', 
            'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idUser', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idUser'], 'unique', 'targetAttribute' => ['idUser'], 'message' => 'Usuario ya está registrado.'],
            [['idEmpleado'], 'unique', 'targetAttribute' => ['idEmpleado'], 'message' => 'Empleado ya está registrado.'],
            [['idUser'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['idUser' => 'id']],
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
            'idEstado' => 'Estado',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'idEmpleado' => 'Empleado',

            'email' => 'Correo Electrónico',
            'password' => 'Contraseña',
            'retypePassword' => 'Repetir Contraseña',
            'nombreEmpleado' => 'Nombre Empleado',
            'identificacion' => 'Identificación',
            'username' => 'Nombre Usuario',

        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'idUser']);
    }

    public function getEmpleado()
    {
        return $this->hasOne(Empleado::class, ['id' => 'idEmpleado']);
    }

    public static  function  getListaData($iduser){
        $data = Userbodega::find()
                        ->alias('usb')
                        ->select(['usb.id', "em.nombreEmpleado + ' - ' + CAST(em.identificacion AS NVARCHAR(50)) AS nombre"])
                        ->join('INNER JOIN', 'empleado em', 'usb.idEmpleado = em.id')
                        ->join('INNER JOIN', 'user us', 'usb.idUser = us.id')
                        ->where(['eml.idEstado' => 1, 'us.status' => 10])
                        ->andFilterWhere(['usb.idUser' => $iduser])
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }
}
