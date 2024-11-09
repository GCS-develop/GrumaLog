<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class DataDocumentoDevolucion extends Model
{

    public $idBodega;
    public $numeroDocumento;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idBodega', 'numeroDocumento'], 'required',
            'message' => '{attribute} Es Un Valor Obligatorio'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idBodega' => 'Bodega',
            'numeroDocumento' => 'Número Documento',
        ];
    }
}

?>