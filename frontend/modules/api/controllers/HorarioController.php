<?php

namespace frontend\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use frontend\modules\api\components\ApiAuth;
use frontend\modules\api\models\HorarioBloqueado;
use frontend\models\Parametroscontrol;

/**
 * Gestión de horarios bloqueados.
 * Bloquear/desbloquear requiere el header X-Admin-Key con el valor del parámetro APK.
 */
class HorarioController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'apiAuth' => [
                'class'  => ApiAuth::class,
                'except' => [],
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
            ],
        ]);
    }

    // GET /api/horario/bloqueados?fecha=YYYY-MM-DD&idAgenda=X
    public function actionBloqueados()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $fecha    = Yii::$app->request->get('fecha');
        $idAgenda = Yii::$app->request->get('idAgenda');

        $query = HorarioBloqueado::find()
            ->where(['activo' => 1]);

        if ($fecha) {
            $query->andWhere(['fecha' => $fecha]);
        }
        if ($idAgenda) {
            $query->andWhere(['OR',
                ['idAgendaPresupuesto' => null],
                ['idAgendaPresupuesto' => (int)$idAgenda],
            ]);
        }

        $data = $query->orderBy(['fecha' => SORT_ASC, 'horaInicio' => SORT_ASC])
            ->asArray()
            ->all();

        return ['success' => true, 'data' => $data];
    }

    // POST /api/horario/bloquear
    // Header: X-Admin-Key: {APK value}
    // Body: { fecha, horaInicio, horaFin, motivo, todoElDia, idAgendaPresupuesto }
    public function actionBloquear()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->validarAdminKey()) {
            return $this->error('Acceso denegado. Se requiere X-Admin-Key válida.', 403);
        }

        $body = Yii::$app->request->bodyParams;

        $bloqueo = new HorarioBloqueado();
        $bloqueo->fecha               = $body['fecha'] ?? null;
        $bloqueo->horaInicio          = $body['horaInicio'] ?? null;
        $bloqueo->horaFin             = $body['horaFin'] ?? null;
        $bloqueo->motivo              = $body['motivo'] ?? null;
        $bloqueo->todoElDia           = (int)($body['todoElDia'] ?? 0);
        $bloqueo->idAgendaPresupuesto = isset($body['idAgendaPresupuesto']) ? (int)$body['idAgendaPresupuesto'] : null;
        $bloqueo->activo              = 1;

        if (!$bloqueo->save()) {
            return $this->error('Error guardando bloqueo: ' . json_encode($bloqueo->errors));
        }

        return ['success' => true, 'message' => 'Horario bloqueado exitosamente.', 'data' => ['id' => $bloqueo->id]];
    }

    // DELETE /api/horario/desbloquear/{id}
    public function actionDesbloquear(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->validarAdminKey()) {
            return $this->error('Acceso denegado.', 403);
        }

        $bloqueo = HorarioBloqueado::findOne(['id' => $id, 'activo' => 1]);
        if (!$bloqueo) {
            return $this->error('Bloqueo no encontrado.', 404);
        }

        $bloqueo->activo = 0;
        $bloqueo->save(false);

        return ['success' => true, 'message' => 'Bloqueo eliminado exitosamente.'];
    }

    private function validarAdminKey(): bool
    {
        $adminKey = Yii::$app->request->headers->get('X-Admin-Key', '');
        $apk      = Parametroscontrol::getValorparametro('APK');
        return $adminKey === $apk;
    }

    private function error(string $msg, int $status = 400): array
    {
        Yii::$app->response->statusCode = $status;
        return ['success' => false, 'message' => $msg];
    }
}
