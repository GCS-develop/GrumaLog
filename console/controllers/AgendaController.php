<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use frontend\models\Agendaentregamercancia;
use frontend\models\Estadoagenda;

/**
 * Tareas programadas del módulo de agendamiento.
 *
 * Cron sugerido (cada 5 minutos):
 *   *\/5 * * * * php C:\Apache24\htdocs\GRUMALog\yii agenda/marcar-no-cumplido >> C:\Apache24\htdocs\GRUMALog\runtime\logs\agenda-cron.log 2>&1
 */
class AgendaController extends Controller
{
    /**
     * Marca como "No Cumplio" las citas Agendadas que superaron
     * 30 minutos desde su hora pactada sin confirmación de cumplimiento.
     *
     * yii agenda/marcar-no-cumplido
     */
    public function actionMarcarNoCumplido(): int
    {
        // Estado origen: Agendada (codigo=1)
        $estadoAgendada = Estadoagenda::findOne(['codigo' => 1]);
        // Estado destino: No Cumplio (codigo=3)
        $estadoNoCumplio = Estadoagenda::findOne(['codigo' => 3]);

        if (!$estadoAgendada || !$estadoNoCumplio) {
            $this->stderr("Error: estados de agenda no encontrados.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Hora límite: ahora menos 30 minutos
        $limiteTs  = time() - (30 * 60);
        $limiteFecha = date('Y-m-d', $limiteTs);
        $limiteHora  = date('H:i', $limiteTs);

        // Buscar citas Agendadas cuya fecha+hora ya pasó el umbral
        // Se evalúa: fechaCita < hoy  OR  (fechaCita = hoy AND horaCita <= limiteHora)
        $citas = Agendaentregamercancia::find()
            ->where(['idEstado' => $estadoAgendada->id])
            ->andWhere(['IS', 'fechaCumplimiento', null])
            ->andWhere([
                'OR',
                ['<', 'fechaCita', $limiteFecha],
                [
                    'AND',
                    ['fechaCita' => $limiteFecha],
                    new \yii\db\Expression("CONVERT(NVARCHAR(5), horaCita, 108) <= :hora", [':hora' => $limiteHora]),
                ],
            ])
            ->all();

        if (empty($citas)) {
            echo date('Y-m-d H:i:s') . " — Sin citas pendientes de marcar.\n";
            return ExitCode::OK;
        }

        $marcadas = 0;
        foreach ($citas as $cita) {
            $cita->idEstado = $estadoNoCumplio->id;
            if ($cita->save(false)) {
                $marcadas++;
                echo date('Y-m-d H:i:s') . " — Cita #{$cita->id} [{$cita->fechaCita} {$cita->horaCita}] → No Cumplio\n";
            } else {
                $this->stderr("Error guardando cita #{$cita->id}: " . json_encode($cita->errors) . "\n");
            }
        }

        echo date('Y-m-d H:i:s') . " — Total marcadas: {$marcadas}\n";
        return ExitCode::OK;
    }
}
