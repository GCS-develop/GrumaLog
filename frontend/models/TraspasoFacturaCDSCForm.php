<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class TraspasoFacturaCDSCForm extends Model
{

    public $idImpresora;
    public $numeroEntrada;
    public $idBodegaMovimiento;
    public $idCentroOperacionMovimiento;
    public $idBodega;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['numeroEntrada', 'idImpresora', 'idBodegaMovimiento', 'idCentroOperacionMovimiento'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idImpresora' => 'Impresora',
            'numeroEntrada' => 'Número',
            'idBodegaMovimiento' => 'Bodega Movimiento',
            'idCentroOperacionMovimiento' => 'CO Movimiento'
        ];
    }
}

?>