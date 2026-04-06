<?php

namespace frontend\modules\api\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * Horarios bloqueados por administradores logísticos.
 * Tabla: horariobloqueado
 */
class HorarioBloqueado extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'horariobloqueado';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['fecha'], 'date', 'format' => 'php:Y-m-d'],
            [['horaInicio', 'horaFin'], 'match', 'pattern' => '/^\d{2}:\d{2}$/'],
            [['motivo'], 'string', 'max' => 200],
            [['idAgendaPresupuesto', 'todoElDia', 'activo', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
        ];
    }

    /**
     * Verifica si una fecha+hora determinada está bloqueada.
     */
    public static function estaBloquedo(string $fecha, string $hora, ?int $idAgenda = null): bool
    {
        $query = self::find()->where(['activo' => 1]);

        if ($idAgenda) {
            $query->andWhere(['OR',
                ['idAgendaPresupuesto' => null],
                ['idAgendaPresupuesto' => $idAgenda],
            ]);
        }

        // Bloqueo de día completo
        $todoDia = (clone $query)
            ->andWhere(['fecha' => $fecha, 'todoElDia' => 1])
            ->exists();

        if ($todoDia) {
            return true;
        }

        // Bloqueo por rango horario
        return (clone $query)
            ->andWhere(['fecha' => $fecha])
            ->andWhere(['todoElDia' => 0])
            ->andWhere(['<=', 'horaInicio', $hora])
            ->andWhere(['>=', 'horaFin', $hora])
            ->exists();
    }

    /**
     * Retorna todos los bloques activos de una fecha (para mostrar al proveedor).
     */
    public static function getBloquesPorFecha(string $fecha, ?int $idAgenda = null): array
    {
        $query = self::find()
            ->select(['fecha', 'horaInicio', 'horaFin', 'todoElDia', 'motivo'])
            ->where(['activo' => 1, 'fecha' => $fecha]);

        if ($idAgenda) {
            $query->andWhere(['OR',
                ['idAgendaPresupuesto' => null],
                ['idAgendaPresupuesto' => $idAgenda],
            ]);
        }

        return $query->asArray()->all();
    }
}
