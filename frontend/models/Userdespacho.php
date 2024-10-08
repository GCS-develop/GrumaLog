<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;

use common\models\User;

/**
 * This is the model class for table "Userdespacho".
 *
 * @property int $id
 * @property int $idUser
 * @property int $idEmpleadoLogistica
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Empleadologistica $empleadologistica
 * @property User $user
 */
class Userdespacho extends \yii\db\ActiveRecord
{
    public $identificacion;
    public $nombreEmpleado;
    public $email;
    public $status;
    public $username;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Userdespacho';
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
            [['idUser', 'idEmpleadoLogistica'], 'required',
            'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idUser', 'idEmpleadoLogistica', 'created_by', 'updated_by'], 'integer'],
            [['created_at'], 'safe'],
            [['updated_at'], 'string', 'max' => 10],
            [['idUser'], 'unique'],
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
            'idUser' => 'Usuario',
            'idEmpleadoLogistica' => 'Empleado',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Empleadologistica]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmpleadologistica()
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
        $data = Userdespacho::find()
                        ->select(['usc.id', "em.nombreEmpleado + ' - ' + CAST(em.identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
                        ->alias('usc')
                        ->join('INNER JOIN', 'user us', 'usc.idUser = us.id')
                        ->join('INNER JOIN', 'empleado em', 'us.idEmpleado = em.id')
                        ->join('INNER JOIN', 'centrooperacion co', 'em.idCO = co.id')
                        ->where(['us.status' => 10])
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

    public static function actualizarUsuario ($status, $iduser, $idempleadologistica){

        // die($status . ' - ' . $iduser . ' - ' . $idempleadologistica);

        $resultado = FALSE;
        if ($status == 1){
            $conteo = Userdespacho::find()->where(['idUser' => $iduser])->one();
            if ($conteo == null){
                $conteo = new Userdespacho();
                $conteo->idUser = $iduser;
            }
            $conteo->idEmpleadoLogistica = $idempleadologistica;
            $conteo->save();
            $resultado = TRUE;
        }else{
            $conteo = Userdespacho::find()->where(['idUser' => $iduser])->one();
            if ($conteo != null){
                $conteo->delete();
                $resultado = TRUE;
            }
        }

        return $resultado;
    }
}
