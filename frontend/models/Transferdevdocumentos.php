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

        // ✅ Formatear fecha_documento como texto AAAAMMDD
        if (!empty($this->fecha_documento)) {
            if (strpos($this->fecha_documento, '-') !== false) {
                // Convierte de YYYY-MM-DD a YYYYMMDD
                $this->fecha_documento = str_replace('-', '', $this->fecha_documento);
            }
            // Forzar que siempre sea string
            $this->fecha_documento = (string)$this->fecha_documento;
        }

        // ✅ Manejo de fecha_envio (datetime en SQL Server)
        if ($insert) {
            $this->usuario_envio = Yii::$app->user->id;
            // Siempre en formato compatible con SQL Server
            $this->fecha_envio = date('Ymd H:i:s');
        } elseif (!empty($this->fecha_envio)) {
            // Si ya trae valor, forzar formato
            $this->fecha_envio = date('Ymd H:i:s', strtotime($this->fecha_envio));
        }

        // ✅ Manejo de fecha_anulacion (datetime en SQL Server)
        if (!empty($this->fecha_anulacion)) {
            $this->fecha_anulacion = date('Ymd H:i:s', strtotime($this->fecha_anulacion));
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
