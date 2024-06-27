<?php

namespace frontend\models;

use Yii;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "empleadologistica".
 *
 * @property int $id
 * @property int $idEmpleado
 * @property int $idEstado
 *
 * @property Empleado $empleado
 */
class Empleadologistica extends \yii\db\ActiveRecord
{

    public $identificacion;
    public $nombreEmpleado;
    public $username;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'empleadologistica';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idEmpleado', 'idEstado'], 'required', 'message' => '{attribute} Es Un Valor Obliagtorio'],
            [['idEmpleado', 'idEstado'], 'integer'],
            [['identificacion'], 'safe'],
            [['nombreEmpleado'], 'string', 'max' => 150],
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
            'nombreEmpleado' => 'Empleado',
            'username' => 'Nombre Usuario'
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
        $data = Empleadologistica::find()
                        ->select(['eml.id', "em.nombreEmpleado + ' - ' + CAST(em.identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
                        ->alias('eml')
                        ->join('INNER JOIN', 'empleado em', 'eml.idEmpleado = em.id')
                        ->join('INNER JOIN', 'centrooperacion co', 'em.idCO = co.id')
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

    public static  function  getListaDataEmpleado(){
        $data = Empleadologistica::find()
                        ->select(['eml.idEmpleado AS id', "em.nombreEmpleado + ' - ' + CAST(em.identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
                        ->alias('eml')
                        ->join('INNER JOIN', 'empleado em', 'eml.idEmpleado = em.id')
                        ->join('INNER JOIN', 'centrooperacion co', 'em.idCO = co.id')
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

    public static  function  getListaDataUsuarioConteo(){
        $data = Empleadologistica::find()
                        ->select(['eml.id', "em.nombreEmpleado + ' - ' + CAST(em.identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
                        ->alias('eml')
                        ->join('INNER JOIN', 'empleado em', 'eml.idEmpleado = em.id')
                        ->join('INNER JOIN', 'centrooperacion co', 'em.idCO = co.id')
                        ->join('INNER JOIN', 'userconteo us', 'eml.id = us.idEmpleadoLogistica')
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

    public static  function  getListaDataNoUsuarioConteo(){
        $data = Empleadologistica::find()
                        ->select(['eml.id', "em.nombreEmpleado + ' - ' + CAST(em.identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
                        ->alias('eml')
                        ->join('LEFT JOIN', 'empleado em', 'eml.idEmpleado = em.id')
                        ->join('LEFT JOIN', 'centrooperacion co', 'em.idCO = co.id')
                        ->join('LEFT JOIN', 'userconteo us', 'eml.id = us.idEmpleadoLogistica')
                        ->where('us.idEmpleadoLogistica IS NULL')
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

}
