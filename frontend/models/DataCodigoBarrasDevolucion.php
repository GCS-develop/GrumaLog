<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class DataCodigoBarrasDevolucion extends Model
{

    public $codigobarras;
    public $cantidad;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigobarras', 'cantidad'], 'required',
            'message' => '{attribute} Es Un Valor Obligatorio'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'codigobarras' => 'Código Barras',
            'cantidad' => 'Cantidad',
        ];
    }
}

?>