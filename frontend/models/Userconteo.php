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
class Userconteo extends \yii\db\ActiveRecord
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
        return 'userconteo';
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
            [['idUser', 'idEmpleadoLogistica',], 'required', 
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
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
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
        $data = Userconteo::find()
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
        $data = Userconteo::find()
                        ->select(['usc.id', "em.nombreEmpleado + ' - ' + CAST(em.identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
                        ->alias('usc')
                        ->join('INNER JOIN', 'empleadologistica eml', 'usc.idEmpleadoLogistica = eml.id')
                        ->join('INNER JOIN', 'empleado em', 'eml.idEmpleado = em.id')
                        ->join('INNER JOIN', 'centrooperacion co', 'em.idCO = co.id')
                        ->join('INNER JOIN', 'user us', 'usc.idUser = us.id')
                        ->join('LEFT JOIN', 'programacionentregamercancia pem', 'usc.id = pem.idUserConteo')
                        ->where(['eml.idEstado' => 1, 'us.status' => 10])
                        ->andwhere(['NOT IN', 'ISNULL(pem.idEstado,0)' , [1]])
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

    public static function getListaDataHabilOC($idagenda, $item){
        $sql = "
            SELECT usc.id,  
            emp.nombreEmpleado + ' - ' + CAST(emp.identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre
            FROM userconteo usc 
            INNER JOIN [user] us ON usc.idUser = us.id
            INNER JOIN empleado emp ON us.idEmpleado = emp.id 
            LEFT JOIN centrooperacion co ON emp.idCO = co.id 
            INNER JOIN 
            (
            SELECT Q1.idUserConteo
            FROM (
            SELECT pem.idUserConteo
                FROM programacionentregamercancia pem
                WHERE ISNULL(pem.idEstado,0) = 1 AND pem.idAgendaEntregaMercancia = " . $idagenda . " AND pem.idUserConteo IS NOT NULL
            ) Q1
            LEFT JOIN (
            SELECT pem.idUserConteo
                FROM programacionentregamercancia pem
                WHERE ISNULL(pem.idEstado,0) = 1 AND pem.idAgendaEntregaMercancia = " . $idagenda . " AND pem.item = " . $item . 
            ") Q2 
            ON Q1.idUserConteo = Q2.idUserConteo 
            WHERE Q2.idUserConteo IS NULL
            ) Q3 ON Q3.idUserConteo = usc.id
            UNION
            SELECT usc.id,  
            emp.nombreEmpleado + ' - ' + CAST(emp.identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre
            FROM userconteo usc 
            INNER JOIN [user] us ON usc.idUser = us.id
            INNER JOIN empleado emp ON us.idEmpleado = emp.id 
            LEFT JOIN centrooperacion co ON emp.idCO = co.id 
            LEFT JOIN 
            (
                SELECT distinct pem.idUserConteo
                FROM programacionentregamercancia pem
                WHERE ISNULL(pem.idEstado,0) = 1 AND pem.idUserConteo IS NOT NULL
            ) Q1 ON usc.id = Q1.idUserConteo
            WHERE Q1.idUserConteo IS NULL
        ";

        // Ejecutar la consulta y devolver los resultados
        $command = Yii::$app->db->createCommand($sql);
        $results = $command->queryAll(); 

        $listadata = ArrayHelper::map($results, 'id', 'nombre');
    	return $listadata;
    }

    public static function actualizarUsuario ($status, $iduser, $idempleadologistica){

        // die($status . ' - ' . $iduser . ' - ' . $idempleadologistica);

        $resultado = FALSE;
        if ($status == 1){
            $conteo = Userconteo::find()->where(['idUser' => $iduser])->one();
            if ($conteo == null){
                $conteo = new Userconteo();
                $conteo->idUser = $iduser;
            }
            $conteo->idEmpleadoLogistica = $idempleadologistica;
            $conteo->save();
            $resultado = TRUE;
        }else{
            $conteo = Userconteo::find()->where(['idUser' => $iduser])->one();
            if ($conteo != null){
                $conteo->delete();
                $resultado = TRUE;
            }
        }

        return $resultado;
    }
}
