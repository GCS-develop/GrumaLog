<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;

use common\models\User;

/**
 * This is the model class for table "userconteo".
 *
 * @property int $id
 * @property int $idUser
 * @property int $idEmpleadoLogistica
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Empleadologistica $empleadoLogistica
 * @property User $user
 */
class Userconteocdsc extends \yii\db\ActiveRecord
{
    public $nombreEmpleado;
    public $identificacion;
    public $username;
    public $idEstado;
    public $email;
    public $retypePassword;
    public $password;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'userconteocdsc';
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
            [['username', 'idEmpleadoLogistica', 'email', 'password', 'retypePassword'], 'required', 
            'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idUser', 'idEmpleadoLogistica', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idUser'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['idUser' => 'id']],
            [['idEmpleadoLogistica'], 'exist', 'skipOnError' => true, 'targetClass' => Empleadologistica::class, 'targetAttribute' => ['idEmpleadoLogistica' => 'id']],
            ['idUser', 'unique', 'message' => 'Usuario ya está registrado.'],
            ['idEmpleadoLogistica', 'unique', 'message' => 'Empleado ya está registrado.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idUser' => 'Usuario',
            'idEmpleadoLogistica' => 'Empleado',
            'nombreEmpleado' => 'Nombre Empleado',
            'identificacion' => 'Identificación',
            'username' => 'Nombre Usuario',
            'idEstado' => 'Estado',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'email' => 'Correo Electrónico',
            'password' => 'Contraseña',
            'retypePassword' => 'Repetir Contraseña',
        ];
    }

    /**
     * Gets query for [[EmpleadoLogistica]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmpleadoLogistica()
    {
        return $this->hasOne(Empleadologistica::class, ['id' => 'idEmpleadoLogistica']);
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

    public static  function  getListaData(){
        $data = Userconteocdsc::find()
                        ->select(['usc.id', "em.nombreEmpleado + ' - ' + CAST(em.identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
                        ->alias('usc')
                        ->join('INNER JOIN', 'empleadologistica eml', 'usc.idEmpleadoLogistica = eml.id')
                        ->join('INNER JOIN', 'empleado em', 'eml.idEmpleado = em.id')
                        ->join('INNER JOIN', 'centrooperacion co', 'em.idCO = co.id')
                        ->join('INNER JOIN', 'user us', 'usc.idUser = us.id')
                        ->where(['eml.idEstado' => 1, 'us.status' => 10])
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

    public static  function  getListaDataHabil(){
        $data = Userconteocdsc::find()
                        ->select(['usc.id', "em.nombreEmpleado + ' - ' + CAST(em.identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
                        ->alias('usc')
                        ->join('INNER JOIN', 'empleadologistica eml', 'usc.idEmpleadoLogistica = eml.id')
                        ->join('INNER JOIN', 'empleado em', 'eml.idEmpleado = em.id')
                        ->join('INNER JOIN', 'centrooperacion co', 'em.idCO = co.id')
                        ->join('INNER JOIN', 'user us', 'usc.idUser = us.id')
                        ->join('LEFT JOIN', 'conteocdscusuario cu', 'usc.id = cu.idUserConteo')
                        ->where(['eml.idEstado' => 1, 'us.status' => 10])
                        ->andwhere(['NOT IN', 'ISNULL(cu.idEstado,0)' , [1]])
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }
}
