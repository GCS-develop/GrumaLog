<?php

namespace frontend\models\search;

use yii\base\Model;

class DevolucionmercanciaSearch extends Model
{
    public $fecha_inicio;
    public $fecha_fin;
    public $tipo_documento;
    public $consecutivo;
    public $bodega;

    public function rules()
    {
        return [
            [['fecha_inicio', 'fecha_fin'], 'safe'],
            [['tipo_documento', 'consecutivo', 'bodega'], 'string'],
        ];
    }
}
