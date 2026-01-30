<?php
namespace frontend\models;

use yii\db\ActiveRecord;

class LogFactVmi extends ActiveRecord
{
    public static function tableName()
    {
        return 'LogFactVmi';
    }

    public function rules()
    {
        return [
            // Requeridos básicos
            [['consulta_id','usuario','proveedor_id','fecha_inicio','fecha_fin','estado'], 'required'],

            // Tipos
            [['consulta_id','total_items_unicos','consec_doc_prov','version'], 'integer'],
            [['fecha_inicio','fecha_fin','fecha_realizado','fecha_doc'], 'safe'],
            [['total_unidades','total_costo'], 'number'],

            // Longitudes de texto
            [['usuario'], 'string', 'max' => 100],
            [['proveedor_id'], 'string', 'max' => 50],
            [['estado'], 'in', 'range' => ['enviado','error','anulado']],
            [['siesa_tx_id'], 'string', 'max' => 100],
            [['prefijo_doc_prov'], 'string', 'max' => 10],

            // Mensajes / JSON
            [['error_msg','json_enviado'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'consulta_id' => 'Consulta',
            'usuario' => 'Usuario',
            'proveedor_id' => 'Proveedor',
            'fecha_inicio' => 'Fecha Inicio',
            'fecha_fin' => 'Fecha Fin',
            'total_unidades' => 'Total Unidades',
            'total_items_unicos' => 'Items Únicos',
            'total_costo' => 'Costo Total',
            'estado' => 'Estado',
            'fecha_realizado' => 'Fecha Realizado',
            'siesa_tx_id' => 'Siesa Tx ID',
            'error_msg' => 'Respuesta/Error',
            'json_enviado' => 'JSON Enviado',
            'prefijo_doc_prov' => 'Prefijo Proveedor',
            'consec_doc_prov' => 'Consecutivo Proveedor',
            'fecha_doc' => 'Fecha Documento',
            'version' => 'Versión',
        ];
    }

    // 🔹 Relación con detalle
    public function getItems()
    {
        return $this->hasMany(LogFactVmiItem::class, ['log_id' => 'id']);
    }
}
