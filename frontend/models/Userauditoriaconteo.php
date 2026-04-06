<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\models\User;

/**
 * This is the model class for table "userauditoriaconteo".
 *
 * @property int $id
 * @property int $idUser
 * @property int $idEmpleadoLogistica
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Empleadologistica $empleadoLogistica
 * @property User $user
 */
class Userauditoriaconteo extends \yii\db\ActiveRecord
{
    public $identificacion;
    public $nombreEmpleado;
    public $email;
    public $status;
    public $username;
    public $password;
    public $retypePassword;

    public static function tableName()
    {
        return 'userauditoriaconteo';
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

    public function rules()
    {
        return [
            [['idUser', 'idEmpleadoLogistica'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idUser', 'idEmpleadoLogistica', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idUser'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['idUser' => 'id']],
            [['idEmpleadoLogistica'], 'exist', 'skipOnError' => true, 'targetClass' => Empleadologistica::class, 'targetAttribute' => ['idEmpleadoLogistica' => 'id']],
            ['idUser', 'unique', 'message' => 'Usuario ya está registrado.'],
            ['idEmpleadoLogistica', 'unique', 'message' => 'Empleado ya está registrado.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'                  => 'ID',
            'idUser'              => 'Usuario',
            'idEmpleadoLogistica' => 'Empleado',
            'created_at'          => 'Creado',
            'created_by'          => 'Creado Por',
            'updated_at'          => 'Actualizado',
            'updated_by'          => 'Actualizado Por',
        ];
    }

    public function getEmpleadoLogistica()
    {
        return $this->hasOne(Empleadologistica::class, ['id' => 'idEmpleadoLogistica']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'idUser']);
    }

    public static function getListaDataNoUsuario()
    {
        $data = Empleadologistica::find()
            ->select(['eml.id', "em.nombreEmpleado + ' - ' + CAST(em.identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
            ->alias('eml')
            ->join('INNER JOIN', 'empleado em', 'eml.idEmpleado = em.id')
            ->join('INNER JOIN', 'centrooperacion co', 'em.idCO = co.id')
            ->join('LEFT JOIN', 'userauditoriaconteo uac', 'eml.id = uac.idEmpleadoLogistica')
            ->where('uac.id IS NULL')
            ->orderBy('em.nombreEmpleado')
            ->asArray()->all();
        return ArrayHelper::map($data, 'id', 'nombre');
    }

    public static function actualizarUsuario($status, $iduser, $idempleadologistica)
    {
        if ($status == 1) {
            $record = static::find()->where(['idUser' => $iduser])->one();
            if ($record === null) {
                $record = new static();
                $record->idUser = $iduser;
            }
            $record->idEmpleadoLogistica = $idempleadologistica;
            return $record->save();
        } else {
            $record = static::find()->where(['idUser' => $iduser])->one();
            if ($record !== null) {
                return $record->delete();
            }
        }
        return false;
    }
}
