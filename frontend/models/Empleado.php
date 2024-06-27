<?php

namespace frontend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "empleado".
 *
 * @property int $id
 * @property float $identificacion
 * @property string $nombreEmpleado
 * @property int|null $ndc
 * @property int|null $idEstado
 * @property int|null $idCargo
 * @property int|null $idCO
 * @property int|null $idCC
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Horasextras[] $horasextras
 * @property Centrocostos $cC
 * @property Centrooperacion $cO
 * @property Cargo $cargo
 */
class Empleado extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'empleado';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['identificacion', 'nombreEmpleado'], 'required'],
            [['identificacion'], 'number'],
            [['ndc', 'idEstado', 'idCargo', 'idCO', 'idCC', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['nombreEmpleado'], 'string', 'max' => 150],
            [['idCO'], 'exist', 'skipOnError' => true, 'targetClass' => Centrooperacion::class, 'targetAttribute' => ['idCO' => 'id']],
            [['idCC'], 'exist', 'skipOnError' => true, 'targetClass' => Centrocostos::class, 'targetAttribute' => ['idCC' => 'id']],
            [['idCargo'], 'exist', 'skipOnError' => true, 'targetClass' => Cargo::class, 'targetAttribute' => ['idCargo' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'identificacion' => 'Identificacion',
            'nombreEmpleado' => 'Nombre Empleado',
            'ndc' => 'Ndc',
            'idEstado' => 'Id Estado',
            'idCargo' => 'Id Cargo',
            'idCO' => 'Id Co',
            'idCC' => 'Id Cc',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Horasextras]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHorasextras()
    {
        return $this->hasMany(Horasextras::class, ['idEmpleado' => 'id']);
    }

    /**
     * Gets query for [[CC]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCC()
    {
        return $this->hasOne(Centrocostos::class, ['id' => 'idCC']);
    }

    /**
     * Gets query for [[CO]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCO()
    {
        return $this->hasOne(Centrooperacion::class, ['id' => 'idCO']);
    }

    /**
     * Gets query for [[Cargo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCargo()
    {
        return $this->hasOne(Cargo::class, ['id' => 'idCargo']);
    }

    public static  function  getListaData(){
        $data = Empleado::find()
                        ->select(['em.id', "nombreEmpleado + ' - ' + CAST(identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
                        ->alias('em')
                        ->join('INNER JOIN', 'centrooperacion co', 'em.idCO = co.id')
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

    public static  function  getListaDataNoLogistica(){
        $data = Empleado::find()
                        ->select(['em.id', "nombreEmpleado + ' - ' + CAST(identificacion AS NVARCHAR(50)) + ' - ' + co.nombre AS nombre"])
                        ->alias('em')
                        ->join('INNER JOIN', 'centrooperacion co', 'em.idCO = co.id')
                        ->join('LEFT JOIN', 'empleadologistica el', 'em.id = el.idEmpleado')
                        ->where('el.id IS NULL')
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }
}
