<?php

namespace frontend\models;

use Exception;
use Yii;
use yii\base\Model;

class FileFormInput extends Model
{
    public $archivo;

    public function rules()
    {
        return [
            [['archivo',], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],

            [['archivo'], 'safe'],
            [['archivo'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx, xls'],
            [['archivo'], 'file', 'maxSize' => 15000000, 'tooBig' => 'El archivo es demasiado grande. El tamaño máximo permitido es 15 MiB.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'archivo' => 'Nombre Archivo',
        ];
    }

}

?>