<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Auditoría de creación, actualización y eliminación de usuarios.
 *
 * @property int    $id
 * @property string $accion               CREAR | ACTUALIZAR | ELIMINAR
 * @property int    $id_usuario_afectado
 * @property string $username_afectado
 * @property string $detalles             JSON con diferencias (solo ACTUALIZAR)
 * @property int    $created_at
 * @property int    $created_by
 */
class Logusuarios extends ActiveRecord
{
    const ACCION_CREAR        = 'CREAR';
    const ACCION_ACTUALIZAR   = 'ACTUALIZAR';
    const ACCION_ELIMINAR     = 'ELIMINAR';
    const ACCION_ASIGNAR_ROL  = 'ASIGNAR_ROL';
    const ACCION_REVOCAR_ROL  = 'REVOCAR_ROL';

    public static function tableName()
    {
        return 'logusuarios';
    }

    public function behaviors()
    {
        return [
            [
                'class'            => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
            [
                'class'             => BlameableBehavior::class,
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * Registra un evento de auditoría de usuario.
     *
     * @param string     $accion      CREAR | ACTUALIZAR | ELIMINAR
     * @param int        $idUsuario   ID del usuario afectado
     * @param string     $username    Username del usuario afectado
     * @param array|null $detalles    Diferencias clave→['antes'=>X,'despues'=>Y]
     */
    public static function registrar(string $accion, int $idUsuario, string $username, ?array $detalles = null): void
    {
        $log = new self();
        $log->accion               = $accion;
        $log->id_usuario_afectado  = $idUsuario;
        $log->username_afectado    = $username;
        $log->detalles             = $detalles ? json_encode($detalles, JSON_UNESCAPED_UNICODE) : null;
        $log->save(false);
    }

    /**
     * Calcula las diferencias entre atributos viejos y nuevos de un modelo User.
     * Excluye campos técnicos que no son relevantes para el log.
     *
     * @param array $oldAttributes  Resultado de $model->getOldAttributes() antes del load
     * @param array $newAttributes  Resultado de $model->attributes después del save
     * @return array                Solo los campos que cambiaron
     */
    public static function calcularDiff(array $oldAttributes, array $newAttributes): array
    {
        $ignorar = ['password_hash', 'auth_key', 'password_reset_token',
                    'verification_token', 'updated_at', 'created_at'];

        $diff = [];
        foreach ($newAttributes as $campo => $nuevo) {
            if (in_array($campo, $ignorar, true)) {
                continue;
            }
            $viejo = $oldAttributes[$campo] ?? null;
            if ((string)$viejo !== (string)$nuevo) {
                $diff[$campo] = ['antes' => $viejo, 'despues' => $nuevo];
            }
        }
        return $diff;
    }

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function getCreador()
    {
        return $this->hasOne(\mdm\admin\models\User::class, ['id' => 'created_by']);
    }
}
