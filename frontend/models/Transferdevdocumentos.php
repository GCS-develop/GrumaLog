<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "transferdevdocumentos".
 *
 * @property int $id
 * @property string|null $centro_operacion
 * @property string|null $tipo_documento
 * @property string|null $consecutivo_documento
 * @property string|null $fecha_documento
 * @property string|null $tercero_proveedor
 * @property string|null $nombreProveedor
 * @property string|null $notas
 * @property string|null $sucursal_proveedor
 * @property string|null $comprador
 * @property string|null $consignacion
 * @property int $id_devoluciondocumento
 * @property int|null $usuario_envio
 * @property string|null $fecha_envio
 *
 * @property Transferdevmovimientos[] $transferdevmovimientos
 */
   
class Transferdevdocumentos extends \yii\db\ActiveRecord
{
    public $bodegadestino;

    public $valor_unitario;

    public static function tableName()
    {
        return 'transferdevdocumentos';
    }

    public function rules()
    {
        return [
            [['notas'], 'string'],
            [['centro_operacion'], 'string', 'max' => 100],
            [['tipo_documento', 'consecutivo_documento'], 'string', 'max' => 50],
            [['fecha_documento'], 'string', 'max' => 10],
            [['id_devoluciondocumento'], 'integer', 'min' => 0],
            [['tercero_proveedor', 'sucursal_proveedor', 'comprador', 'consignacion'], 'string', 'max' => 255],
            [['usuario_anula'], 'integer'],
            [['fecha_anulacion'], 'safe'],
            [['usuario_envio'], 'integer'],
            [['fecha_envio'], 'safe'],
            [['observacion_anulacion'], 'string', 'max' => 300], [['motivo'], 'string', 'max' => 2],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'centro_operacion' => 'Centro Operacion',
            'tipo_documento' => 'Tipo Documento',
            'consecutivo_documento' => 'Consecutivo Documento',
            'fecha_documento' => 'Fecha Documento',
            'tercero_proveedor' => 'Tercero Proveedor',
            'notas' => 'Notas',
            'sucursal_proveedor' => 'Sucursal Proveedor',
            'comprador' => 'Comprador',
            'consignacion' => 'Consignacion',
            'motivo' => 'Motivo',
        ];
    }


            public function beforeSave($insert)
{
    if (parent::beforeSave($insert)) {
        // ✅ Formatear fecha_documento a YYYYMMDD solo si viene en formato YYYY-MM-DD
        if (!empty($this->fecha_documento) && strpos($this->fecha_documento, '-') !== false) {
            $this->fecha_documento = str_replace('-', '', $this->fecha_documento);
        }

        // Si es un nuevo registro, asignar el usuario y la fecha de envío
        if ($insert) {
            $this->usuario_envio = Yii::$app->user->id; // Asignar el usuario que está realizando el envío
            $this->fecha_envio = date('Y-m-d H:i:s'); // Asignar la fecha y hora actual
        }

        return true;
    }
    return false;
}



    public function getTransferdevmovimientos()
    {
        return $this->hasMany(Transferdevmovimientos::class, ['documento_id' => 'id']);
    }

    public function getDevoluciondocumento()
    {
        return $this->hasOne(Devoluciondocumento::class, ['id' => 'id_devoluciondocumento']);
    }

    /*public function getDevolucionDetalles()
    {
        return $this->hasOne(Devoluciondocumentodetalle::class, ['id' => 'id_devoluciondocumento']);
    }*/


public function getDevolucionDetalles()
{
    return $this->hasMany(Devoluciondocumentodetalle::class, ['id_devoluciondocumento' => 'id_devoluciondocumento']);
}



public function getUsuarioanula()
{
    return $this->hasOne(Userbodega::class, ['id' => 'usuario_anula']);
}

public function getProveedor()
{
    return $this->hasOne(Proveedor::class, ['nit' => 'tercero_proveedor']);
}


public function getNombreProveedor()
{
    return $this->proveedor ? $this->proveedor->nombre : '(Sin nombre)';
}



}
