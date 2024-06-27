<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use common\models\ProcedimientosGenerales;

/**
 * This is the model class for table "agendapresupuesto".
 *
 * @property int $id
 * @property int|null $periodoAnio
 * @property int|null $periodoMes
 * @property string|null $observacion
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Agendapresupuesto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agendapresupuesto';
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
            [['periodoAnio', 'periodoMes', 'observacion'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['periodoAnio', 'periodoMes', 'created_by', 'updated_by', 'tienePresupuesto', 'idEstado'], 'integer'],
            [['created_at', 'updated_at', 'desde', 'hasta'], 'safe'],
            [['observacion'], 'string', 'max' => 150],
            [['periodoAnio', 'periodoMes'], 'unique', 'targetAttribute' => ['periodoAnio', 'periodoMes'], 'message' => 'Período de Fecha YA Existe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'periodoAnio' => 'Año',
            'periodoMes' => 'Mes',
            'observacion' => 'Observación',
            'idEstado' => 'Estado',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function getNombremes (){
        return ProcedimientosGenerales::nombreMes ($this->periodoMes);
    }

    public function getPrimerUltimoDiaDelMes (){
        $anio = $this->periodoAnio;
        $mes = $this->periodoMes;
        $resultado = ProcedimientosGenerales::obtenerPrimerUltimoDiaDelMes ($anio, $mes);

        $this->desde = $resultado['primerDia'];
        $this->hasta = $resultado['ultimoDia'];
    }
}
