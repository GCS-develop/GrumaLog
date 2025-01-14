<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class Selectimpresora extends Model
{

    public $idImpresora;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idImpresora'], 'required',
            'message' => '{attribute} Es Un Valor Obligatorio'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idImpresora' => 'Impresora',
        ];
    }
}

?>