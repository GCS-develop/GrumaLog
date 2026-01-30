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

    public $cantidad_stickers;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idImpresora'], 'required',
            'message' => '{attribute} Es Un Valor Obligatorio'],

            ['cantidad_stickers', 'integer', 'min' => 1],
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