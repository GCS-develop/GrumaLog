<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;

use common\models\User;

/**
 * This is the model class for table "usertraspaso".
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
class Usertraspaso extends \yii\db\ActiveRecord
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
        return 'usertraspaso';
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
            [
                ['idUser', 'idEmpleadoLogistica'],
                'required',
                'message' => '{attribute} Es Un Valor Obligatorio'
            ],
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
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function getEmpleadoLogistica()
    {
        return $this->hasOne(Empleadologistica::class, ['id' => 'idEmpleadoLogistica']);
    }

    /**
     * Gets query for [[IdUser0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'idUser']);
    }

    public static function getListaData()
    {
        $data = Usertraspaso::find()
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

    public static function getListaDataUsertraspaso()
    {
        $data = Usertraspaso::find()
            ->select([
                'us.id', // ID del registro Usertraspaso
                "CONCAT(em.nombreEmpleado, ' - ', CAST(em.identificacion AS NVARCHAR(50)), ' - ', co.nombre, ' - User ID: ', us.id) AS nombre"
            ])
            ->alias('usc')
            ->innerJoin('empleadologistica eml', 'usc.idEmpleadoLogistica = eml.id')
            ->innerJoin('empleado em', 'eml.idEmpleado = em.id')
            ->innerJoin('centrooperacion co', 'em.idCO = co.id')
            ->innerJoin('user us', 'usc.idUser = us.id')
            ->where(['eml.idEstado' => 1, 'us.status' => 10])
            ->orderBy('em.nombreEmpleado')
            ->asArray()
            ->all();

        // Mapear los resultados para crear un array usable en formularios
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }


    public static function actualizarUsuario($status, $iduser, $idempleadologistica)
    {

        // die($status . ' - ' . $iduser . ' - ' . $idempleadologistica);

        $resultado = FALSE;
        if ($status == 1) {
            $conteo = Usertraspaso::find()->where(['idUser' => $iduser])->one();
            if ($conteo == null) {
                $conteo = new Usertraspaso();
                $conteo->idUser = $iduser;
            }
            $conteo->idEmpleadoLogistica = $idempleadologistica;
            $conteo->save();
            $resultado = TRUE;
        } else {
            $conteo = Usertraspaso::find()->where(['idUser' => $iduser])->one();
            if ($conteo != null) {
                $conteo->delete();
                $resultado = TRUE;
            }
        }

        return $resultado;
    }
}
