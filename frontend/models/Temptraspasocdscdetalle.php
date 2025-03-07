<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "temptraspasocdscdetalle".
 *
 * @property int $id
 * @property int $idTraspaso
 * @property int $idItem
 * @property int $cantidad
 * @property int|null $cantidadTransferencia
 */
class Temptraspasocdscdetalle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temptraspasocdscdetalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idTraspaso', 'idItem', 'cantidad'], 'required'],
            [['idTraspaso', 'idItem', 'cantidad', 'cantidadTransferencia', 'idConteoFactura', 'idConteoDestino'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idTraspaso' => 'Id Traspaso',
            'idItem' => 'Id Item',
            'cantidad' => 'Cantidad',
            'cantidadTransferencia' => 'Cantidad Transferencia',
        ];
    }

    public static function crearRegistro($iditem, $unidades, $idtraspaso, $transferencia){
        $traspaso = new Temptraspasocdscdetalle();

        $traspaso->idTraspaso = $idtraspaso;
        $traspaso->idItem = $iditem;
        $traspaso->cantidad = $unidades;
        $traspaso->idConteoFactura = $transferencia->idConteoFactura;
        $traspaso->idConteoDestino = $transferencia->idConteoDestino;

        if (!$traspaso->save()){
            var_dump($traspaso->getErrors()); die("hola");
            return null;
        }
        return $traspaso;
    }
}
