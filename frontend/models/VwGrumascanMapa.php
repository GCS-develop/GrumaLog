<?php

namespace frontend\models;

use yii\db\ActiveRecord;

class VwGrumascanMapa extends ActiveRecord
{
    public static function tableName()
    {
        // Nombre real de la VIEW en SQL Server
        return 'dbo.vw_grumascan_mapa_marcacion';
    }

    /**
     * IMPORTANTE:
     * Las VIEWS no siempre tienen PK, pero Yii la necesita para Grid/DataProvider.
     * Usamos una PK compuesta con las columnas que definen una fila del mapa.
     */
    public static function primaryKey()
    {
        return ['idbodega', 'ubicacion', 'seccion', 'desde', 'hasta'];
    }

    public function rules()
    {
        return [
            [['idbodega', 'cantidad'], 'integer'],
            [['ubicacion', 'seccion'], 'string', 'max' => 100],
            [['desde', 'hasta'], 'integer'], // bigint en SQL Server -> integer en PHP (sirve)
        ];
    }

    public function attributeLabels()
    {
        return [
            'idbodega'  => 'Bodega',
            'ubicacion' => 'Ubicación',
            'seccion'   => 'Sección',
            'desde'     => 'Desde',
            'hasta'     => 'Hasta',
            'cantidad'  => 'Cantidad',
        ];
    }
    public function getbodega()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idbodega']);
    }
}
