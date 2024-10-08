<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "conductor".
 *
 * @property int $id
 * @property int $idEmpleado
 * @property int $idEstado
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Empleado $empleado
 */
class Conductor extends \yii\db\ActiveRecord
{
    public $identificacion;
    public $nombreEmpleado;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conductor';
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
            [['idEmpleado', 'idEstado'], 'required', 'message' => '{attribute} Es Un Valor Obliagtorio'],
            [['idEmpleado', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            ['idEmpleado', 'unique', 'message' => 'Empleado ya está registrado.'],
            [['idEmpleado'], 'exist', 'skipOnError' => true, 'targetClass' => Empleado::class, 'targetAttribute' => ['idEmpleado' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idEmpleado' => 'Empleado',
            'idEstado' => 'Estado',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Empleado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmpleado()
    {
        return $this->hasOne(Empleado::class, ['id' => 'idEmpleado']);
    }

    public static  function  getListaData(){
        $data = Conductor::find()
                        ->select(['con.id', "em.nombreEmpleado + ' - ' + CAST(em.identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
                        ->alias('con')
                        ->join('INNER JOIN', 'empleado em', 'con.idEmpleado = em.id')
                        ->join('INNER JOIN', 'centrooperacion co', 'em.idCO = co.id')
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

    public static  function  getListaDataEmpleado(){
        $data = Conductor::find()
                        ->select(['con.idEmpleado AS id', "em.nombreEmpleado + ' - ' + CAST(em.identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
                        ->alias('con')
                        ->join('INNER JOIN', 'empleado em', 'con.idEmpleado = em.id')
                        ->join('INNER JOIN', 'centrooperacion co', 'em.idCO = co.id')
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

    public static  function  getListaDataNoConductor(){
        $data = Empleado::find()
                        ->select(['em.id', "nombreEmpleado + ' - ' + CAST(identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
                        ->alias('em')
                        ->join('INNER JOIN', 'centrooperacion co', 'em.idCO = co.id')
                        ->join('LEFT JOIN', 'conductor el', 'em.id = el.idEmpleado')
                        ->where('el.id IS NULL')
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }
}
