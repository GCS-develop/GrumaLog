<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "planillaembarque".
 *
 * @property int $id
 * @property string $fechaDespacho
 * @property string $horaDespacho
 * @property int|null $idTransportadora
 * @property int|null $idVehiculo
 * @property string|null $placa
 * @property int|null $idConductor
 * @property string $nombreConductor
 * @property string|null $sello
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 */
class Planillaembarque extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'planillaembarque';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fechaDespacho', 'horaDespacho', 'nombreConductor', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['fechaDespacho', 'created_at', 'updated_at'], 'safe'],
            [['idTransportadora', 'idVehiculo', 'idConductor', 'created_by', 'updated_by'], 'integer'],
            [['horaDespacho'], 'string', 'max' => 10],
            [['placa'], 'string', 'max' => 20],
            [['nombreConductor'], 'string', 'max' => 150],
            [['sello'], 'string', 'max' => 50],
            [['sello'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fechaDespacho' => 'Fecha Despacho',
            'horaDespacho' => 'Hora Despacho',
            'idTransportadora' => 'Id Transportadora',
            'idVehiculo' => 'Id Vehiculo',
            'placa' => 'Placa',
            'idConductor' => 'Id Conductor',
            'nombreConductor' => 'Nombre Conductor',
            'sello' => 'Sello',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }
}
