<?php

namespace frontend\models\forms;

use yii\base\Model;

class GrumascanMarcacionBulkUseForm extends Model
{
    public $desde;
    public $hasta;
    public $idbodega;
    public $ubicacion;
    public $seccion;
    public $sobrescribir = 0;

    public function rules()
    {
        return [
            [['desde', 'hasta', 'idbodega'], 'required'],
            [['desde', 'hasta', 'idbodega'], 'integer', 'min' => 1],
            [['sobrescribir'], 'boolean'],
            [['ubicacion', 'seccion'], 'string', 'max' => 100],
            [
                'hasta',
                'compare',
                'compareAttribute' => 'desde',
                'operator' => '>=',
                'type' => 'number',
                'message' => 'El ID final debe ser mayor o igual al inicial.'
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'desde' => 'Desde (ID)',
            'hasta' => 'Hasta (ID)',
            'idbodega' => 'Bodega',
            'ubicacion' => 'Ubicación',
            'seccion' => 'Sección',
            'sobrescribir' => 'Sobrescribir registros ya asignados',
        ];
    }
}
