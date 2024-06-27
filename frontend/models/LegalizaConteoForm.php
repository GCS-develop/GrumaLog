<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LegalizaConteoForm extends Model
{

    public $numeroFactura;
    public $observacion;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['numeroFactura', 'observacion'], 'required',
            'message' => '{attribute} Es Un Valor Obligatorio'],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'numeroFactura' => 'Número Factura',
            'observacion' => 'Observación',
        ];
    }
}

?>