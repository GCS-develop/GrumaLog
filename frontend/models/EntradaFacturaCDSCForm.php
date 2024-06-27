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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idTipoDocumento'], 'required',
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
            'idTipoDocumento' => 'Serie',
            'numeroEntrada' => 'Número',
        ];
    }
}

?>