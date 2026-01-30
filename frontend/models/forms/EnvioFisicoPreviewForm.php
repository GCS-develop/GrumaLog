<?php

namespace frontend\models\forms;

use yii\base\Model;

class EnvioFisicoPreviewForm extends Model
{
    public $idbodega; // int
    public $fecha;    // YYYY-MM-DD

    public function rules(): array
    {
        return [
            [['idbodega', 'fecha'], 'required'],
            ['idbodega', 'integer', 'min' => 1],
            ['fecha', 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'idbodega' => 'Bodega',
            'fecha' => 'Fecha del conteo (día)',
        ];
    }

    public function getDateRange(): array
    {
        $desde = $this->fecha . ' 00:00:00';
        $hasta = date('Y-m-d 00:00:00', strtotime($this->fecha . ' +1 day'));
        return [$desde, $hasta];
    }
}
