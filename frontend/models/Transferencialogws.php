<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "transferencialogws".
 *
 * @property int $id
 * @property string $centroOperacionDocumento
 * @property string $tipoDocumento
 * @property string $fechaDocumento
 * @property string|null $bodegaSalidaDocumento
 * @property string|null $bodegaEntradaDocumento
 * @property string $startDate
 * @property string $endDate
 * @property int $numeroRegistros
 * @property string $mensaje
 * @property int|null $idConectorDinamico
 */
class Transferencialogws extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transferencialogws';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['centroOperacionDocumento', 'tipoDocumento', 'startDate', 
            'endDate', 'numeroRegistros', 'mensaje', 'idTransferenciaerp'], 'required'],
            [['startDate', 'endDate', 'fechaDocumento', ], 'safe'],
            [['numeroRegistros', 'idConectorDinamico', 'consecutivoOrdenCompra'], 'integer'],
            //[['mensaje'], 'string'],
            //[['centroOperacionDocumento', 'tipoDocumento', 'bodegaSalidaDocumento', 'bodegaEntradaDocumento'], 'string', 'max' => 5],
            //[['fechaDocumento'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'centroOperacionDocumento' => 'CO',
            'tipoDocumento' => 'Tipo Documento',
            'fechaDocumento' => 'Fecha Documento',
            'bodegaSalidaDocumento' => 'Bodega Salida',
            'bodegaEntradaDocumento' => 'Bodega Entrada',
            'startDate' => 'Inicio',
            'endDate' => 'Fin',
            'numeroRegistros' => 'No. Registros',
            'mensaje' => 'Mensaje',
            'idConectorDinamico' => 'Id Conector Dinamico',
            'consecutivoOrdenCompra' => 'Consecutivo'
        ];
    }

    public function getConectordinamico()
    {
        return $this->hasOne(Conectoresdinamicos::class, ['id' => 'idConectorDinamico']);
    }
}
