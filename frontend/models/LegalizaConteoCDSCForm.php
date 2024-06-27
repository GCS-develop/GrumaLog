<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LegalizaConteoCDSCForm extends Model
{

    public $idCentroOperacion;
    public $observacion;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idCentroOperacion'], 'required',
            'message' => '{attribute} Es Un Valor Obligatorio'],
            [['observacion'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idCentroOperacion' => 'Almacén',
            'observacion' => 'Observación',
        ];
    }
}

?>