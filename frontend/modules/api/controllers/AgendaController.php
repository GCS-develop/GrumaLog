<?php

namespace frontend\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use frontend\modules\api\components\ApiAuth;
use frontend\modules\api\models\HorarioBloqueado;
use frontend\models\Agendaentregamercancia;
use frontend\models\Agendapresupuesto;
use frontend\models\Agendapresupuestosubcategoria;
use frontend\models\Ordendecompra;
use frontend\models\Estadoagenda;
use frontend\models\Transportadora;
use frontend\models\Parametroscontrol;

/**
 * Endpoints de agendamiento para proveedores externos (GRUMAProv)
 */
class AgendaController extends Controller
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

    // ─────────────────────────────────────────────────────────────
    // GET /api/agenda/periodos
    // Lista periodos de agenda activos (agendapresupuesto con estado=1)
    // ─────────────────────────────────────────────────────────────
    public function actionPeriodos()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $periodos = Agendapresupuesto::find()
            ->select(['id', 'periodoAnio', 'periodoMes', 'desde', 'hasta', 'observacion'])
            ->where(['idEstado' => 1])
            ->orderBy(['periodoAnio' => SORT_DESC, 'periodoMes' => SORT_DESC])
            ->asArray()
            ->all();

        // Rellenar desde/hasta si están vacíos
        foreach ($periodos as &$p) {
            if (!$p['desde'] || !$p['hasta']) {
                $año = (int)$p['periodoAnio'];
                $mes = (int)$p['periodoMes'];
                $p['desde'] = date('Y-m-d', mktime(0, 0, 0, $mes, 1, $año));
                $p['hasta'] = date('Y-m-d', mktime(0, 0, 0, $mes + 1, 0, $año));
            }
            static $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                             'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
            $p['nombreMes'] = $meses[(int)$p['periodoMes']] ?? '';
        }

        return ['success' => true, 'data' => $periodos];
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/agenda/disponible?idAgenda=X&idCategoria=Y
    // Fechas con cupo disponible para agendar
    // ─────────────────────────────────────────────────────────────
    public function actionDisponible()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $idAgenda    = (int) Yii::$app->request->get('idAgenda', 0);
        $idCategoria = Yii::$app->request->get('idCategoria', null);

        if (!$idAgenda) {
            return $this->error('idAgenda es requerido.');
        }

        // Fechas disponibles con capacidad
        $rows = Agendapresupuestosubcategoria::find()
            ->alias('aps')
            ->select([
                'fechaLlegada AS fecha',
                'CONVERT(NVARCHAR(10), fechaLlegada, 120) AS fechaStr',
                'SUM(cantidad) AS totalCupo',
                'SUM(ISNULL(cantidadAgendada,0)) AS totalAgendado',
                '(SUM(cantidad) - SUM(ISNULL(cantidadAgendada,0))) AS disponible',
            ])
            ->where(['idAgendaPresupuesto' => $idAgenda])
            ->andWhere(['>', 'cantidad', 0])
            ->andFilterWhere(['categoria' => $idCategoria])
            ->groupBy(['fechaLlegada'])
            ->having(['>', '(SUM(cantidad) - SUM(ISNULL(cantidadAgendada,0)))', 0])
            ->orderBy(['fechaLlegada' => SORT_ASC])
            ->asArray()
            ->all();

        // Marcar qué fechas tienen bloqueos de día completo
        $result = [];
        foreach ($rows as $row) {
            $bloquesTotales = HorarioBloqueado::getBloquesPorFecha($row['fecha'], $idAgenda);
            $row['bloques'] = $bloquesTotales;
            $result[] = $row;
        }

        return ['success' => true, 'data' => $result];
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/agenda/mis-citas
    // Citas del proveedor autenticado
    // ─────────────────────────────────────────────────────────────
    public function actionMisCitas()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $nit = Yii::$app->params['api_nit'];

        $citas = Agendaentregamercancia::find()
            ->alias('aem')
            ->select([
                'aem.id',
                'aem.idAgenda',
                'aem.fechaCita',
                'aem.horaCita',
                'aem.unidades',
                'aem.numeroCajas',
                'aem.observacion',
                'aem.contacto',
                'aem.idEstado',
                'es.nombre AS estadoNombre',
                'es.codigo AS estadoCodigo',
                'oc.consecutivo AS numeroOC',
                'oc.fecha AS fechaOC',
                'td.nombre AS tipoDocumento',
                'co.nombre AS centroOperacion',
                'tr.nombre AS transportadora',
                'cat.nombre AS categoria',
            ])
            ->innerJoin('ordendecompra oc', 'aem.idOrdenCompra = oc.id')
            ->innerJoin('proveedor pr', 'oc.idProveedor = pr.id')
            ->innerJoin('estadoagenda es', 'aem.idEstado = es.id')
            ->leftJoin('tipodocumento td', 'oc.idTipoDocumento = td.id')
            ->leftJoin('centrooperacion co', 'oc.idCO = co.id')
            ->leftJoin('transportadora tr', 'aem.idTransportadora = tr.id')
            ->leftJoin('categoria cat', 'aem.idCategoria = cat.id')
            ->where(['pr.nit' => $nit])
            ->andWhere(['NOT IN', 'es.codigo', [5, 6]]) // excluir "No Cumplio-Nueva Cita" y Cancelado (ya no aplican)
            ->orderBy(new \yii\db\Expression('CASE WHEN es.codigo = 1 THEN 0 ELSE 1 END ASC, aem.fechaCita ASC, aem.id ASC'))
            ->asArray()
            ->all();

        return ['success' => true, 'data' => $citas];
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/agenda/mis-citas/{id}
    // ─────────────────────────────────────────────────────────────
    public function actionDetalleCita(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $nit  = Yii::$app->params['api_nit'];
        $cita = $this->findCitaProveedor($id, $nit);
        if (!$cita) {
            return $this->error('Cita no encontrada.', 404);
        }

        return ['success' => true, 'data' => $cita];
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/agenda/mis-ordenes
    // OC pendientes de agendamiento del proveedor
    // ─────────────────────────────────────────────────────────────
    public function actionMisOrdenes()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $nit = Yii::$app->params['api_nit'];

        $ordenes = Ordendecompra::find()
            ->alias('oc')
            ->select([
                'oc.id',
                'oc.consecutivo',
                'oc.fecha',
                'oc.totalCantidadPedida',
                'oc.totalCantidadEntrada',
                'oc.totalCantidadPendiente',
                'td.nombre AS tipoDocumento',
                'co.nombre AS centroOperacion',
                'eo.nombre AS estado',
            ])
            ->innerJoin('proveedor pr', 'oc.idProveedor = pr.id')
            ->leftJoin('tipodocumento td', 'oc.idTipoDocumento = td.id')
            ->leftJoin('centrooperacion co', 'oc.idCO = co.id')
            ->leftJoin('estadoordencompra eo', 'oc.idEstado = eo.id')
            ->where(['pr.nit' => $nit])
            ->andWhere(['>', 'oc.totalCantidadPendiente', 0])
            ->orderBy(['oc.fecha' => SORT_DESC])
            ->asArray()
            ->all();

        return ['success' => true, 'data' => $ordenes];
    }

    // ─────────────────────────────────────────────────────────────
    // POST /api/agenda/crear
    // Crear cita de entrega
    // Body: { idOrdenCompra, idAgenda, fechaCita, horaCita,
    //         unidades, numeroCajas, idTransportadora,
    //         contacto, observacion, idBodega }
    // ─────────────────────────────────────────────────────────────
    public function actionCrear()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return $this->error('Método no permitido.', 405);
        }

        $nit  = Yii::$app->params['api_nit'];
        $body = Yii::$app->request->bodyParams;

        $idOrdenCompra   = (int)($body['idOrdenCompra']   ?? 0);
        $idAgenda        = (int)($body['idAgenda']        ?? 0);
        $fechaCita       = trim($body['fechaCita']        ?? '');
        $horaCita        = trim($body['horaCita']         ?? '');
        $unidades        = (float)($body['unidades']      ?? 0);
        $numeroCajas     = (int)($body['numeroCajas']     ?? 0);
        $idTransportadora= (int)($body['idTransportadora'] ?? 0);
        $contacto        = trim($body['contacto']         ?? '');
        $observacion     = trim($body['observacion']      ?? '');
        $idBodega        = isset($body['idBodega']) ? (int)$body['idBodega'] : null;
        $idCategoria     = isset($body['idCategoria']) ? (int)$body['idCategoria'] : null;

        // Validaciones básicas
        if (!$idOrdenCompra || !$idAgenda || !$fechaCita || !$horaCita) {
            return $this->error('Faltan campos requeridos: idOrdenCompra, idAgenda, fechaCita, horaCita.');
        }

        // Verificar que la OC pertenece al proveedor
        $oc = Ordendecompra::find()
            ->alias('oc')
            ->innerJoin('proveedor pr', 'oc.idProveedor = pr.id')
            ->where(['oc.id' => $idOrdenCompra, 'pr.nit' => $nit])
            ->one();

        if (!$oc) {
            return $this->error('La Orden de Compra no pertenece a su empresa o no existe.', 403);
        }

        // Verificar que la fecha esté disponible (no bloqueada)
        if (HorarioBloqueado::estaBloquedo($fechaCita, $horaCita, $idAgenda)) {
            return $this->error('El horario seleccionado está bloqueado por el equipo logístico.');
        }

        // Verificar cupo en presupuesto
        $cupo = $this->getCupoDisponible($idAgenda, $fechaCita, $idCategoria);
        if ($cupo <= 0) {
            return $this->error('No hay cupo disponible para la fecha seleccionada.');
        }

        // Obtener estado "Agendamiento" (código 1)
        $estadoAgendamiento = Estadoagenda::findOne(['codigo' => 1]);
        if (!$estadoAgendamiento) {
            return $this->error('Configuración de estados incompleta en el servidor.');
        }

        // Crear registro de cita
        $cita = new Agendaentregamercancia();
        $cita->idOrdenCompra    = $idOrdenCompra;
        $cita->idAgenda         = $idAgenda;
        $cita->fechaCita        = $fechaCita;
        $cita->horaCita         = $horaCita;
        $cita->unidades         = $unidades;
        $cita->numeroCajas      = $numeroCajas;
        $cita->idTransportadora = $idTransportadora ?: null;
        $cita->contacto         = $contacto;
        $cita->observacion      = $observacion;
        $cita->idBodega         = $idBodega;
        $cita->idCategoria      = $idCategoria;
        $cita->idEstado         = $estadoAgendamiento->id;

        if (!$cita->save()) {
            return $this->error('Error guardando la cita: ' . json_encode($cita->errors));
        }

        // Actualizar cupos presupuestados
        Agendaentregamercancia::actualizarUnidadesAgendamiento(
            $idAgenda, $idOrdenCompra, $fechaCita
        );

        return [
            'success' => true,
            'message' => 'Cita agendada exitosamente.',
            'data'    => ['id' => $cita->id],
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // PUT /api/agenda/cancelar/{id}
    // ─────────────────────────────────────────────────────────────
    public function actionCancelar(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPut && !Yii::$app->request->isPost) {
            return $this->error('Método no permitido.', 405);
        }

        $nit  = Yii::$app->params['api_nit'];
        $cita = $this->findCitaModeloProveedor($id, $nit);
        if (!$cita) {
            return $this->error('Cita no encontrada o no pertenece a su empresa.', 404);
        }

        // Verificar que el estado sea "Agendamiento" (código 1)
        if ($cita->estado->codigo != 1) {
            return $this->error('Solo se pueden cancelar citas en estado Agendado.');
        }

        // Verificar tiempo mínimo de anticipación
        $check = $this->verificarAnticipacion($cita);
        if ($check !== true) {
            return $this->error($check);
        }

        // Cambiar estado a Cancelado (código 6)
        $estadoCancelado = Estadoagenda::findOne(['codigo' => 6]);
        if (!$estadoCancelado) {
            return $this->error('Estado Cancelado no configurado en el servidor.');
        }

        $cita->idEstado = $estadoCancelado->id;
        if (!$cita->save(false)) {
            return $this->error('Error cancelando la cita.');
        }

        // Liberar cupo presupuestado
        Agendaentregamercancia::actualizarUnidadesAgendamiento(
            $cita->idAgenda, $cita->idOrdenCompra, $cita->fechaCita, 'restar'
        );

        return ['success' => true, 'message' => 'Cita cancelada exitosamente.'];
    }

    // ─────────────────────────────────────────────────────────────
    // PUT /api/agenda/reprogramar/{id}
    // Body: { fechaCita, horaCita }
    // ─────────────────────────────────────────────────────────────
    public function actionReprogramar(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPut && !Yii::$app->request->isPost) {
            return $this->error('Método no permitido.', 405);
        }

        $nit   = Yii::$app->params['api_nit'];
        $body  = Yii::$app->request->bodyParams;
        $nuevaFecha = trim($body['fechaCita'] ?? '');
        $nuevaHora  = trim($body['horaCita']  ?? '');

        if (!$nuevaFecha || !$nuevaHora) {
            return $this->error('Nuevas fechaCita y horaCita son requeridas.');
        }

        $cita = $this->findCitaModeloProveedor($id, $nit);
        if (!$cita) {
            return $this->error('Cita no encontrada o no pertenece a su empresa.', 404);
        }

        if ($cita->estado->codigo != 1) {
            return $this->error('Solo se pueden reprogramar citas en estado Agendado.');
        }

        $check = $this->verificarAnticipacion($cita);
        if ($check !== true) {
            return $this->error($check);
        }

        // Verificar nueva fecha/hora no bloqueada
        if (HorarioBloqueado::estaBloquedo($nuevaFecha, $nuevaHora, $cita->idAgenda)) {
            return $this->error('El nuevo horario está bloqueado por el equipo logístico.');
        }

        // Verificar cupo en nueva fecha
        $cupo = $this->getCupoDisponible($cita->idAgenda, $nuevaFecha, $cita->idCategoria);
        if ($cupo <= 0) {
            return $this->error('No hay cupo disponible en la nueva fecha.');
        }

        // Obtener estados
        $estadoAgendamiento  = Estadoagenda::findOne(['codigo' => 1]);
        $estadoReagendado    = Estadoagenda::findOne(['codigo' => 5]);
        if (!$estadoAgendamiento || !$estadoReagendado) {
            return $this->error('Estados no configurados correctamente.');
        }

        // Marcar cita anterior como reagendada (código 5)
        $cita->idEstado = $estadoReagendado->id;
        $cita->save(false);

        // Liberar cupo de fecha anterior
        Agendaentregamercancia::actualizarUnidadesAgendamiento(
            $cita->idAgenda, $cita->idOrdenCompra, $cita->fechaCita, 'restar'
        );

        // Crear nueva cita
        $nueva = new Agendaentregamercancia();
        $nueva->idOrdenCompra           = $cita->idOrdenCompra;
        $nueva->idAgenda                = $cita->idAgenda;
        $nueva->fechaCita               = $nuevaFecha;
        $nueva->horaCita                = $nuevaHora;
        $nueva->unidades                = $cita->unidades;
        $nueva->numeroCajas             = $cita->numeroCajas;
        $nueva->idTransportadora        = $cita->idTransportadora;
        $nueva->contacto                = $cita->contacto;
        $nueva->observacion             = $cita->observacion;
        $nueva->idBodega                = $cita->idBodega;
        $nueva->idCategoria             = $cita->idCategoria;
        $nueva->idEstado                = $estadoAgendamiento->id;
        $nueva->idAgendaEntregaMercancia = $cita->id; // referencia cita anterior

        if (!$nueva->save()) {
            // Revertir estado anterior
            $cita->idEstado = $estadoAgendamiento->id;
            $cita->save(false);
            return $this->error('Error creando nueva cita.');
        }

        // Actualizar cupo nueva fecha
        Agendaentregamercancia::actualizarUnidadesAgendamiento(
            $nueva->idAgenda, $nueva->idOrdenCompra, $nuevaFecha
        );

        return [
            'success' => true,
            'message' => 'Cita reprogramada exitosamente.',
            'data'    => ['id' => $nueva->id],
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/agenda/calendario?idAgenda=X
    // Retorna todos los días del período con su estado:
    //   - libre: hay cupo disponible
    //   - ocupado: sin cupo
    //   - bloqueado: bloqueado por logística
    //   - mis-citas: días donde el proveedor autenticado tiene cita
    // ─────────────────────────────────────────────────────────────
    public function actionCalendario()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $idAgenda = (int) Yii::$app->request->get('idAgenda', 0);
        if (!$idAgenda) {
            return $this->error('idAgenda es requerido.');
        }

        $nit = Yii::$app->params['api_nit'];

        // Cupos por fecha
        $cupos = Agendapresupuestosubcategoria::find()
            ->select([
                'CONVERT(NVARCHAR(10), fechaLlegada, 120) AS fecha',
                'SUM(cantidad) AS totalCupo',
                'SUM(ISNULL(cantidadAgendada,0)) AS totalAgendado',
            ])
            ->where(['idAgendaPresupuesto' => $idAgenda])
            ->andWhere(['>', 'cantidad', 0])
            ->groupBy(['fechaLlegada'])
            ->orderBy(['fechaLlegada' => SORT_ASC])
            ->asArray()
            ->all();

        // Citas del proveedor autenticado en este período
        $misCitas = Agendaentregamercancia::find()
            ->alias('aem')
            ->select(['CONVERT(NVARCHAR(10), aem.fechaCita, 120) AS fecha', 'aem.horaCita', 'aem.id', 'es.codigo AS estadoCodigo'])
            ->innerJoin('ordendecompra oc', 'aem.idOrdenCompra = oc.id')
            ->innerJoin('proveedor pr', 'oc.idProveedor = pr.id')
            ->innerJoin('estadoagenda es', 'aem.idEstado = es.id')
            ->where(['aem.idAgenda' => $idAgenda, 'pr.nit' => $nit])
            ->andWhere(['NOT IN', 'es.codigo', [5, 6]])
            ->asArray()
            ->all();

        $misCitasPorFecha = [];
        foreach ($misCitas as $c) {
            $misCitasPorFecha[$c['fecha']][] = ['id' => $c['id'], 'hora' => $c['horaCita']];
        }

        // Bloqueados
        $bloqueados = \frontend\modules\api\models\HorarioBloqueado::find()
            ->select(['CONVERT(NVARCHAR(10), fecha, 120) AS fecha', 'todoElDia', 'horaInicio', 'horaFin', 'motivo'])
            ->where(['activo' => 1])
            ->andWhere(['OR', ['idAgendaPresupuesto' => null], ['idAgendaPresupuesto' => $idAgenda]])
            ->asArray()
            ->all();

        $bloqueadosPorFecha = [];
        foreach ($bloqueados as $b) {
            $bloqueadosPorFecha[$b['fecha']][] = $b;
        }

        // Construir resultado del calendario
        $resultado = [];
        foreach ($cupos as $row) {
            $fecha       = $row['fecha'];
            $disponible  = (float)$row['totalCupo'] - (float)$row['totalAgendado'];
            $bloqueos    = $bloqueadosPorFecha[$fecha] ?? [];
            $bloqueoDia  = array_filter($bloqueos, fn($b) => $b['todoElDia'] == 1);
            $misCitasHoy = $misCitasPorFecha[$fecha] ?? [];

            if (!empty($bloqueoDia)) {
                $estado = 'bloqueado';
            } elseif ($disponible <= 0) {
                $estado = 'lleno';
            } else {
                $estado = 'disponible';
            }

            $resultado[] = [
                'fecha'      => $fecha,
                'estado'     => $estado,
                'totalCupo'  => (float)$row['totalCupo'],
                'disponible' => max(0, $disponible),
                'agendado'   => (float)$row['totalAgendado'],
                'bloques'    => array_values($bloqueos),
                'misCitas'   => $misCitasHoy,
            ];
        }

        return ['success' => true, 'data' => $resultado];
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/agenda/detalle-dia?idAgenda=X&fecha=YYYY-MM-DD
    // Detalle horario de un día: cupos ocupados por hora (sin exponer
    // qué proveedor ocupa cada hora, solo conteos + las mías)
    // ─────────────────────────────────────────────────────────────
    public function actionDetalleDia()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $idAgenda = (int) Yii::$app->request->get('idAgenda', 0);
        $fecha    = Yii::$app->request->get('fecha', '');

        if (!$idAgenda || !$fecha) {
            return $this->error('idAgenda y fecha son requeridos.');
        }

        $nit = Yii::$app->params['api_nit'];

        // Cupo total del día
        $cupo = Agendapresupuestosubcategoria::find()
            ->select(['SUM(cantidad) AS totalCupo', 'SUM(ISNULL(cantidadAgendada,0)) AS totalAgendado'])
            ->where(['idAgendaPresupuesto' => $idAgenda, 'fechaLlegada' => $fecha])
            ->asArray()->one();

        $totalCupo    = (float)($cupo['totalCupo']    ?? 0);
        $totalAgendado = (float)($cupo['totalAgendado'] ?? 0);

        // Todas las citas activas del día (solo hora + count, SIN proveedor)
        $todasCitas = Agendaentregamercancia::find()
            ->alias('aem')
            ->select(['aem.horaCita AS hora', 'aem.id', 'oc.idProveedor'])
            ->innerJoin('ordendecompra oc', 'aem.idOrdenCompra = oc.id')
            ->innerJoin('estadoagenda es', 'aem.idEstado = es.id')
            ->where(['aem.idAgenda' => $idAgenda])
            ->andWhere(['CONVERT(NVARCHAR(10), aem.fechaCita, 120)' => $fecha])
            ->andWhere(['NOT IN', 'es.codigo', [5, 6]])
            ->asArray()->all();

        // Citas propias del proveedor
        $misCitas = Agendaentregamercancia::find()
            ->alias('aem')
            ->select(['aem.id', 'aem.horaCita AS hora', 'oc.consecutivo AS numeroOC', 'es.nombre AS estadoNombre'])
            ->innerJoin('ordendecompra oc', 'aem.idOrdenCompra = oc.id')
            ->innerJoin('proveedor pr', 'oc.idProveedor = pr.id')
            ->innerJoin('estadoagenda es', 'aem.idEstado = es.id')
            ->where(['aem.idAgenda' => $idAgenda, 'pr.nit' => $nit])
            ->andWhere(['CONVERT(NVARCHAR(10), aem.fechaCita, 120)' => $fecha])
            ->andWhere(['NOT IN', 'es.codigo', [5, 6]])
            ->asArray()->all();

        $misCitasIds = array_column($misCitas, 'id');

        // Agrupar por hora: solo contar, marcar si es mía
        $horaMap = [];
        foreach ($todasCitas as $c) {
            $h = substr((string)$c['hora'], 0, 5);
            if (!isset($horaMap[$h])) {
                $horaMap[$h] = ['hora' => $h, 'ocupadas' => 0, 'esMia' => false];
            }
            $horaMap[$h]['ocupadas']++;
            if (in_array($c['id'], $misCitasIds, true)) {
                $horaMap[$h]['esMia'] = true;
            }
        }
        ksort($horaMap);

        // Bloques horarios del día
        $bloques = HorarioBloqueado::find()
            ->select(['horaInicio', 'horaFin', 'todoElDia', 'motivo'])
            ->where(['activo' => 1])
            ->andWhere(['CONVERT(NVARCHAR(10), fecha, 120)' => $fecha])
            ->andWhere(['OR', ['idAgendaPresupuesto' => null], ['idAgendaPresupuesto' => $idAgenda]])
            ->asArray()->all();

        return ['success' => true, 'data' => [
            'fecha'         => $fecha,
            'totalCupo'     => $totalCupo,
            'disponible'    => max(0, $totalCupo - $totalAgendado),
            'agendado'      => $totalAgendado,
            'horas'         => array_values($horaMap),
            'misCitas'      => $misCitas,
            'bloques'       => $bloques,
        ]];
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/agenda/transportadoras
    // Lista de transportadoras disponibles
    // ─────────────────────────────────────────────────────────────
    public function actionTransportadoras()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $data = Transportadora::find()
            ->select(['id', 'nombre'])
            ->where(['idEstado' => 1])
            ->orderBy('nombre')
            ->asArray()
            ->all();

        return ['success' => true, 'data' => $data];
    }

    // ──────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────

    private function findCitaProveedor(int $id, string $nit): ?array
    {
        return Agendaentregamercancia::find()
            ->alias('aem')
            ->select([
                'aem.*',
                'es.nombre AS estadoNombre',
                'es.codigo AS estadoCodigo',
                'oc.consecutivo AS numeroOC',
                'oc.fecha AS fechaOC',
                'pr.razonSocial',
                'tr.nombre AS transportadora',
            ])
            ->innerJoin('ordendecompra oc', 'aem.idOrdenCompra = oc.id')
            ->innerJoin('proveedor pr', 'oc.idProveedor = pr.id')
            ->innerJoin('estadoagenda es', 'aem.idEstado = es.id')
            ->leftJoin('transportadora tr', 'aem.idTransportadora = tr.id')
            ->where(['aem.id' => $id, 'pr.nit' => $nit])
            ->asArray()
            ->one();
    }

    private function findCitaModeloProveedor(int $id, string $nit): ?Agendaentregamercancia
    {
        return Agendaentregamercancia::find()
            ->alias('aem')
            ->innerJoin('ordendecompra oc', 'aem.idOrdenCompra = oc.id')
            ->innerJoin('proveedor pr', 'oc.idProveedor = pr.id')
            ->where(['aem.id' => $id, 'pr.nit' => $nit])
            ->one();
    }

    private function getCupoDisponible(int $idAgenda, string $fecha, ?int $idCategoria): float
    {
        $row = Agendapresupuestosubcategoria::find()
            ->select([
                '(SUM(cantidad) - SUM(ISNULL(cantidadAgendada,0))) AS disponible',
            ])
            ->where(['idAgendaPresupuesto' => $idAgenda, 'fechaLlegada' => $fecha])
            ->andFilterWhere(['categoria' => $idCategoria])
            ->asArray()
            ->one();

        return $row ? (float)$row['disponible'] : 0;
    }

    /**
     * Verifica que la cita pueda cancelarse/reprogramarse con la anticipación mínima.
     * Retorna true si OK, o un mensaje de error si no.
     */
    private function verificarAnticipacion(Agendaentregamercancia $cita)
    {
        $horasMinimas = (int)(Parametroscontrol::getValorparametro('HAC') ?? 1);

        if (!$cita->fechaCita) {
            return true;
        }

        $hora = $cita->horaCita ?: '00:00';
        $fechaHoraCita = strtotime($cita->fechaCita . ' ' . $hora);
        $limite        = time() + ($horasMinimas * 3600);

        if ($limite >= $fechaHoraCita) {
            return "La cita no puede modificarse con menos de {$horasMinimas} hora(s) de anticipación.";
        }

        return true;
    }

    private function error(string $msg, int $status = 400): array
    {
        Yii::$app->response->statusCode = $status;
        return ['success' => false, 'message' => $msg];
    }
}
