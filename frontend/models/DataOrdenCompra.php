<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class DataOrdenCompra extends Model
{

    public $idCentroOperacion;
    public $idTipoDocumento;
    public $numeroOrdenCompra;

    public $fechaOrden;
    public $totalCantidadPedida;
    public $totalCantidadEntrada;
    public $totalCantidadPendiente;
    public $dataProveedor;
    public $idOrdenCompra;
    public $idAgenda;
    public $desde;
    public $hasta;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idCentroOperacion', 'idTipoDocumento', 'numeroOrdenCompra'], 'required',
            'message' => '{attribute} Es Un Valor Obligatorio'],

            [['idOrdenCompra'], 'integer'],

            [['fechaOrden'], 'safe'],

            //[['totalCantidadPendiente'], 'integer', 'min' => 1, 'message' => 'El valor debe ser mayor a cero'],

            //['password', 'validatePassword'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idCentroOperacion' => 'Almacén',
            'idTipoDocumento' => 'Serie',
            'numeroOrdenCompra' => 'Número Documento',
        ];
    }
}

?>