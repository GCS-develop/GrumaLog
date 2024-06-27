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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idImpresora'], 'required',
            'message' => '{attribute} Es Un Valor Obligatorio'],
            [['numeroEntrada'], 'safe'],
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
        ];
    }
}

?>