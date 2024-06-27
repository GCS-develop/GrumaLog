<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use frontend\models\Empleado;
use frontend\models\Centrooperacion;

/**
 * This is the model class for table "horasextras".
 *
 * @property int $id
 * @property string $fecha
 * @property int $idEmpleado
 * @property int $idCO
 * @property float $numeroHoras
 * @property string $observacion
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Centrooperacion $idCO0
 * @property Empleado $idEmpleado0
 */
class Horasextras extends \yii\db\ActiveRecord
{
    public $identificacion;
    public $nombreEmpleado;
    public $codigoCO;
    public $nombreCO;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'horasextras';
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
            [['fecha', 'idEmpleado', 'idCO', 'numeroHoras'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['fecha', 'created_at', 'updated_at'], 'safe'],
            [['idEmpleado', 'idCO', 'created_by', 'updated_by', 'idEstado'], 'integer'],
            [['numeroHoras'], 'number'],
            [['numeroHoras'], 'compare', 'compareValue' => 2, 'operator' => '<=', 'type' => 'number', 'message' => 'El número de horas extras no puede ser mayor a 2.'],
            [['observacion'], 'string', 'max' => 500],
            [['fecha'], 'compare', 'compareValue' => date('Y-m-d'), 'operator' => '<=', 'type' => 'date', 'message' => 'La fecha debe ser menor o igual a la fecha actual.'],
            [['idCO'], 'exist', 'skipOnError' => true, 'targetClass' => Centrooperacion::class, 'targetAttribute' => ['idCO' => 'id']],
            [['idEmpleado'], 'exist', 'skipOnError' => true, 'targetClass' => Empleado::class, 'targetAttribute' => ['idEmpleado' => 'id']],
            [['idEmpleado', 'fecha', 'idCO'], 'unique', 'targetAttribute' => ['idEmpleado', 'fecha', 'idCO'], 'message' => 'Este registro ya existe para el empleado en esta fecha en el CO.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fecha' => 'Fecha',
            'idEmpleado' => 'Empleado',
            'idCO' => 'Centro Operación',
            'numeroHoras' => 'Número Horas',
            'observacion' => 'Observación',
            'created_at' => 'Fecha Creación',
            'created_by' => 'Creado Por',
            'updated_at' => 'Fecha Actualización',
            'updated_by' => 'Actualizado Por',
            'codigoCO' => 'Código CO',
            'nombreCO' => 'Nombre CO',
            'identificacion' => 'Identificación',
            'idEstado' => 'Estado'
        ];
    }

    /**
     * Gets query for [[IdCO0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdCO0()
    {
        return $this->hasOne(Centrooperacion::class, ['id' => 'idCO']);
    }

    /**
     * Gets query for [[IdEmpleado0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdEmpleado0()
    {
        return $this->hasOne(Empleado::class, ['id' => 'idEmpleado']);
    }
}
