<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "transferenciaerperror".
 *
 * @property int $id
 * @property int $idTransferenciaerp
 * @property string|null $centroOperacionDocumento
 * @property string|null $tipoDocumento
 * @property string|null $numeroLinea
 * @property string|null $tipoRegistro
 * @property string|null $subTipoRegistro
 * @property string|null $version
 * @property string|null $nivel
 * @property string|null $valor
 * @property string|null $detalle
 *
 * @property Transferenciaerp $idTransferenciaerp0
 */
class Transferenciaerperror extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transferenciaerperror';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idTransferenciaerp'], 'required'],
            [['idTransferenciaerp', 'consecutivo'], 'integer'],
            [['detalle'], 'string'],
            //[['centroOperacionDocumento', 'tipoDocumento'], 'string', 'max' => 5],
            //[['numeroLinea', 'tipoRegistro', 'subTipoRegistro', 'version', 'nivel', 'valor'], 'string', 'max' => 10],
            [['idTransferenciaerp'], 'exist', 'skipOnError' => true, 'targetClass' => Transferenciaerp::class, 'targetAttribute' => ['idTransferenciaerp' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idTransferenciaerp' => 'Id Transferenciaerp',
            'centroOperacionDocumento' => 'Centro Operacion Documento',
            'tipoDocumento' => 'Tipo Documento',
            'numeroLinea' => 'Numero Linea',
            'tipoRegistro' => 'Tipo Registro',
            'subTipoRegistro' => 'Sub Tipo Registro',
            'version' => 'Version',
            'nivel' => 'Nivel',
            'valor' => 'Valor',
            'detalle' => 'Detalle',
        ];
    }

    /**
     * Gets query for [[IdTransferenciaerp0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdTransferenciaerp0()
    {
        return $this->hasOne(Transferenciaerp::class, ['id' => 'idTransferenciaerp']);
    }
}
