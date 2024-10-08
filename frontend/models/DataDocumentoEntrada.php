<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class DataDocumentoEntrada extends Model
{
    public $idTipoDocumento;
    public $fechaDocumento;
    public $consecutivo;
    public $consignacion;
    public $idCO;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idCO', 'idTipoDocumento', 'fechaDocumento', 'consecutivo'], 'required',
            'message' => '{attribute} Es Un Valor Obligatorio'],

            [['idTipoDocumento', 'consecutivo', 'consignacion'], 'integer'],

            [['fechaDocumento'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'fechaDocumento' => 'Fecha Documento',
            'idTipoDocumento' => 'Tipo Documento',
            'idCO' => 'Centro Operación',
            'consecutivo' => 'Consecutivo Documento',
        ];
    }
}

?>