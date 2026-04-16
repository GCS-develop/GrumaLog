<?php

namespace frontend\models;

use Yii;

/**
 * Auditoría de eliminaciones de transferenciaerp.
 *
 * Problema que resuelve: cuando se borra un registro de transferenciaerp
 * (manual o automáticamente al regenerar), las filas en transferencialogws
 * quedan huérfanas referenciando un id inexistente. Esta tabla preserva
 * la cabecera del registro eliminado para poder reconstruir la trazabilidad.
 *
 * Consulta útil para investigaciones:
 *   SELECT l.*, w.mensaje
 *   FROM logtransferenciaerp l
 *   JOIN transferencialogws w ON w.idTransferenciaerp = l.idTransferenciaerpBorrada
 *   WHERE l.idOrdenCompra = ?
 *
 * @property int    $id
 * @property int    $idTransferenciaerpBorrada
 * @property int    $idTransferenciaerpNueva
 * @property string $accion
 * @property string $descripcion
 * @property string $origen
 * @property string $documento
 * @property int    $idOrdenCompra
 * @property int    $idConectorDinamico
 * @property int    $numeroRegistros
 * @property int    $enviadoWS
 * @property string $notas
 * @property string $creadoOrigEn
 * @property int    $creadoOrigPor
 * @property string $created_at
 * @property int    $created_by
 * @property string $erroresJson  JSON con los registros de transferenciaerperror al momento del borrado
 */
class Logtransferenciaerp extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'logtransferenciaerp';
    }

    public function getUserBorrado()
    {
        return $this->hasOne(\common\models\User::class, ['id' => 'created_by']);
    }

    public function rules()
    {
        return [
            [['idTransferenciaerpBorrada', 'accion'], 'required'],
            [['idTransferenciaerpBorrada', 'idTransferenciaerpNueva', 'idOrdenCompra',
              'idConectorDinamico', 'numeroRegistros', 'enviadoWS',
              'creadoOrigPor', 'created_by'], 'integer'],
            [['accion', 'origen'], 'string', 'max' => 30],
            [['descripcion', 'notas'], 'string', 'max' => 500],
            [['documento'], 'string', 'max' => 50],
            [['created_at', 'creadoOrigEn', 'erroresJson'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'                        => 'ID',
            'idTransferenciaerpBorrada' => 'Transfer. Borrada',
            'idTransferenciaerpNueva'   => 'Transfer. Nueva',
            'accion'                    => 'Acción',
            'descripcion'               => 'Descripción',
            'origen'                    => 'Origen',
            'documento'                 => 'Documento',
            'idOrdenCompra'             => 'OC',
            'numeroRegistros'           => 'Registros',
            'enviadoWS'                 => 'Enviado SIESA',
            'notas'                     => 'Notas',
            'creadoOrigEn'              => 'Creado (original)',
            'creadoOrigPor'             => 'Creado por (original)',
            'created_at'                => 'Fecha borrado',
            'created_by'                => 'Borrado por',
        ];
    }

    /**
     * Registra la cabecera de una transferenciaerp que está a punto de ser eliminada.
     * Devuelve el log para que el llamador pueda actualizar idTransferenciaerpNueva luego.
     *
     * Uso en borrado manual:
     *   Logtransferenciaerp::registrar($transfer, 'MANUAL_USUARIO');
     *
     * Uso en regeneración automática:
     *   $log = Logtransferenciaerp::registrar($transfer, 'AUTO_REGENERACION');
     *   // ... crear nueva transferencia ...
     *   $log->idTransferenciaerpNueva = $idNuevo;
     *   $log->save(false);
     *
     * @param  Transferenciaerp $transfer Registro que será eliminado
     * @param  string           $accion   MANUAL_USUARIO | AUTO_REGENERACION
     * @return self
     */
    public static function registrar(Transferenciaerp $transfer, string $accion): self
    {
        $log = new self();
        $log->idTransferenciaerpBorrada = $transfer->id;
        $log->accion             = $accion;
        $log->descripcion        = $transfer->descripcion;
        $log->origen             = $transfer->origen;
        $log->documento          = $transfer->documento;
        $log->idOrdenCompra      = $transfer->idOrdenCompra;
        $log->idConectorDinamico = $transfer->idConectorDinamico;
        $log->numeroRegistros    = $transfer->numeroRegistros;
        $log->enviadoWS          = $transfer->enviadoWS;
        $log->notas              = $transfer->notas;
        // Convertir a formato sin guiones (yyyymmdd) para evitar desbordamiento
        // con DATEFORMAT dmy de SQL Server en locale colombiano.
        $ts = $transfer->created_at ? @strtotime($transfer->created_at) : false;
        $log->creadoOrigEn       = ($ts !== false) ? date('Ymd H:i:s', $ts) : null;
        $log->creadoOrigPor      = $transfer->created_by;
        $log->created_at         = new \yii\db\Expression('GETDATE()');
        $log->created_by         = Yii::$app->user->isGuest ? 0 : Yii::$app->user->id;

        // Captura snapshot de errores SIESA antes de que sean eliminados
        $errores = Transferenciaerperror::find()
            ->where(['idTransferenciaerp' => $transfer->id])
            ->asArray()
            ->all();
        if (!empty($errores)) {
            $log->erroresJson = json_encode($errores, JSON_UNESCAPED_UNICODE);
        }

        $log->save(false);
        return $log;
    }
}
