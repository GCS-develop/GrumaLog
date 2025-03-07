<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class EntradaFacturaCDSCForm extends Model
{

    public $idTipoDocumento;
    public $numeroEntrada;
    public $consignacion;
    public $fechaEntrada;
    public $numeroFacturaEntrada;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idTipoDocumento'], 'required',
            'message' => '{attribute} Es Un Valor Obligatorio'],
            [['numeroEntrada', 'consignacion', 'fechaEntrada', 'numeroFacturaEntrada'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idTipoDocumento' => 'Serie',
            'numeroEntrada' => 'Número',
            'consignacion' => 'Consignación',
            'fechaEntrada' => 'Fecha Entrada',
            'numeroFacturaEntrada' => 'Número Factura'
        ];
    }
}

?>