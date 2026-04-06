<?php

namespace frontend\modules\calificacion\controllers;

use frontend\models\Calificacionproveedor;
use frontend\models\Proveedor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CalificacionController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ]);
    }

    // ---------------------------------------------------------------
    //  Lista de OCs pendientes / todas
    // ---------------------------------------------------------------
    public function actionIndex()
    {
        $request          = Yii::$app->request;
        $soloSinCalificar = $request->get('pendientes', 1);

        $filtros = [
            'q_oc'        => trim($request->get('q_oc', '')),
            'q_proveedor' => trim($request->get('q_proveedor', '')),
            'q_tipo_doc'  => $request->get('q_tipo_doc', ''),
        ];

        $ocs = Calificacionproveedor::listaOcsPendientes((bool)$soloSinCalificar, $filtros);

        // Tipos de documento que existen en órdenes de compra
        $tiposDoc = Yii::$app->db->createCommand("
            SELECT DISTINCT td.id, td.nombre
            FROM ordendecompra oc
            INNER JOIN tipodocumento td ON td.id = oc.idTipoDocumento
            ORDER BY td.nombre
        ")->queryAll();
        $tiposDocMap = \yii\helpers\ArrayHelper::map($tiposDoc, 'id', 'nombre');

        return $this->render('index', [
            'ocs'              => $ocs,
            'soloSinCalificar' => (bool)$soloSinCalificar,
            'filtros'          => $filtros,
            'tiposDocMap'      => $tiposDocMap,
        ]);
    }

    // ---------------------------------------------------------------
    //  Exportar Excel con los filtros actuales
    // ---------------------------------------------------------------
    public function actionExport()
    {
        $request = Yii::$app->request;
        $filtros = [
            'q_oc'        => trim($request->get('q_oc', '')),
            'q_proveedor' => trim($request->get('q_proveedor', '')),
            'q_tipo_doc'  => $request->get('q_tipo_doc', ''),
        ];

        // Construir WHERE igual que listaOcsPendientes pero sin TOP 10 y trayendo el detalle completo
        $where  = [];
        $params = [];

        if (!empty($filtros['q_tipo_doc'])) {
            $where[]               = "oc.idTipoDocumento = :q_tipo_doc";
            $params[':q_tipo_doc'] = (int)$filtros['q_tipo_doc'];
        }
        if (!empty($filtros['q_oc']) && is_numeric($filtros['q_oc'])) {
            $where[]         = "oc.consecutivo = :q_oc";
            $params[':q_oc'] = (int)$filtros['q_oc'];
        }
        if (!empty($filtros['q_proveedor'])) {
            $where[]               = "p.razonSocial LIKE :q_proveedor";
            $params[':q_proveedor'] = '%' . $filtros['q_proveedor'] . '%';
        }

        $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sql = "
            SELECT
                ISNULL(td.nombre,'') + '-' + FORMAT(oc.consecutivo,'00000000') AS numero_oc,
                ISNULL(p.razonSocial,'')      AS proveedor,
                ISNULL(co.nombre,'')          AS centro_operacion,
                CONVERT(VARCHAR(10), oc.fecha, 120) AS fecha_oc,
                cp.id,
                cp.categoria,
                cp.subcategoria,
                cp.tipo_mercancia,
                cp.producto,
                cp.transportadora,
                cp.revisado_por,
                cp.unidades_ordenadas,
                cp.unidades_entregadas,
                CONVERT(VARCHAR(10), cp.fecha_entrega_cita, 120) AS fecha_cita,
                CONVERT(VARCHAR(10), cp.fecha_entrega_oc,   120) AS fecha_entrega_oc,
                cp.caja_bulto, cp.calibre, cp.rotulo, cp.contiene_documentos,
                cp.separa_tallas, cp.separa_color, cp.separa_referencia,
                cp.etiquetado, cp.error_tiqueteo, cp.homologacion,
                cp.precio, cp.novedad, cp.factura, cp.orden_compra_doc,
                cp.gancho, cp.tallero,
                cp.calidad_ponderada,
                cp.calidad_producto,
                cp.oportunidad_formula,
                cp.cantidad_formula,
                cp.puntaje_total,
                cp.observacion,
                CONVERT(VARCHAR(10), cp.created_at, 120) AS fecha_calificacion
            FROM calificacionproveedor cp
            INNER JOIN ordendecompra oc    ON oc.id  = cp.id_ordendecompra
            LEFT  JOIN proveedor      p    ON p.id   = oc.idProveedor
            LEFT  JOIN tipodocumento  td   ON td.id  = oc.idTipoDocumento
            LEFT  JOIN centrooperacion co  ON co.id  = oc.idCO
            {$whereClause}
            ORDER BY oc.fecha DESC, cp.created_at DESC
        ";

        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        // ---- Construir Excel ----
        $spread = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet  = $spread->getActiveSheet();
        $sheet->setTitle('Calificaciones');

        // Cabeceras
        $cols = [
            'N° OC', 'Proveedor', 'CO', 'Fecha OC',
            'Categoría', 'Subcategoría', 'Tipo Mercancía', 'Producto', 'Transportadora',
            'Revisado Por', 'Uds. Ordenadas', 'Uds. Entregadas',
            'Fecha Cita', 'Fecha Entrega OC',
            // Criterios
            'Caja/Bulto', 'Calibre', 'Rótulo', 'Contiene Docs',
            'Separa Tallas', 'Separa Color', 'Separa Referencia',
            'Etiquetado', 'Error Tiqueteo', 'Homologación',
            'Precio', 'Novedad', 'Factura', 'OC Doc',
            'Gancho', 'Tallero',
            // Puntajes
            'Cal. Criterios', 'Cal. Producto (30%)',
            'Oportunidad (10%)', 'Cantidad (30%)',
            'Puntaje Total', 'Letra',
            'Observación', 'Fecha Calificación',
        ];

        $colLetter = 'A';
        foreach ($cols as $header) {
            $sheet->setCellValue($colLetter . '1', $header);
            $colLetter++;
        }

        // Estilo cabecera
        $lastCol = chr(ord('A') + count($cols) - 1);
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'wrapText'   => true],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Datos
        $rowNum = 2;
        foreach ($rows as $r) {
            $letra = \frontend\models\Calificacionproveedor::puntajeALetra($r['puntaje_total']);
            $sheet->fromArray([
                $r['numero_oc'],
                $r['proveedor'],
                $r['centro_operacion'],
                $r['fecha_oc'],
                $r['categoria'],
                $r['subcategoria'],
                $r['tipo_mercancia'],
                $r['producto'],
                $r['transportadora'],
                $r['revisado_por'],
                (int)$r['unidades_ordenadas'],
                (int)$r['unidades_entregadas'],
                $r['fecha_cita'],
                $r['fecha_entrega_oc'],
                $r['caja_bulto'],    $r['calibre'],    $r['rotulo'],     $r['contiene_documentos'],
                $r['separa_tallas'], $r['separa_color'],$r['separa_referencia'],
                $r['etiquetado'],   $r['error_tiqueteo'], $r['homologacion'],
                $r['precio'],       $r['novedad'],     $r['factura'],    $r['orden_compra_doc'],
                $r['gancho'],       $r['tallero'],
                $r['calidad_ponderada'],
                $r['calidad_producto'],
                $r['oportunidad_formula'],
                $r['cantidad_formula'],
                $r['puntaje_total'],
                $letra,
                $r['observacion'],
                $r['fecha_calificacion'],
            ], null, 'A' . $rowNum);

            // Color fila por letra
            $bgColor = $letra === 'A' ? 'D9EAD3' : ($letra === 'B' ? 'FFF2CC' : 'FCE5CD');
            $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")
                  ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setRGB($bgColor);

            $rowNum++;
        }

        // Autosize primeras columnas importantes
        foreach (['A','B','C','D','J','AK','AL'] as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        // Nombre archivo
        $fecha    = date('Ymd_His');
        $filename = "Calificaciones_Proveedores_{$fecha}.xlsx";

        $tempFile = tempnam(sys_get_temp_dir(), 'cal_');
        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spread);
        $writer->save($tempFile);

        return Yii::$app->response->sendFile($tempFile, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tempFile) {
            @unlink($tempFile);
        });
    }

    // ---------------------------------------------------------------
    //  Historial de calificaciones (listado con filtros)
    // ---------------------------------------------------------------
    public function actionHistorial()
    {
        $request = Yii::$app->request;
        $filtros = [
            'q_oc'         => trim($request->get('q_oc', '')),
            'q_proveedor'  => trim($request->get('q_proveedor', '')),
            'q_desde'      => trim($request->get('q_desde', '')),
            'q_hasta'      => trim($request->get('q_hasta', '')),
        ];

        $where  = [];
        $params = [];

        if (!empty($filtros['q_oc'])) {
            $where[]       = "cp.numero_oc LIKE :q_oc";
            $params[':q_oc'] = '%' . $filtros['q_oc'] . '%';
        }
        if (!empty($filtros['q_proveedor'])) {
            $where[]              = "cp.proveedor LIKE :q_proveedor";
            $params[':q_proveedor'] = '%' . $filtros['q_proveedor'] . '%';
        }
        if (!empty($filtros['q_desde'])) {
            $where[]           = "CAST(cp.created_at AS DATE) >= :q_desde";
            $params[':q_desde'] = $filtros['q_desde'];
        }
        if (!empty($filtros['q_hasta'])) {
            $where[]           = "CAST(cp.created_at AS DATE) <= :q_hasta";
            $params[':q_hasta'] = $filtros['q_hasta'];
        }

        $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sql = "
            SELECT TOP 200
                cp.id,
                cp.id_ordendecompra,
                cp.numero_oc,
                cp.proveedor,
                cp.categoria,
                cp.subcategoria,
                cp.transportadora,
                cp.oportunidad_formula,
                cp.cantidad_formula,
                cp.calidad_ponderada,
                cp.calidad_producto,
                cp.puntaje_total,
                cp.created_at,
                cp.created_by
            FROM calificacionproveedor cp
            {$whereClause}
            ORDER BY cp.created_at DESC
        ";

        $calificaciones = Yii::$app->db->createCommand($sql, $params)->queryAll();

        // Traer usernames para los created_by
        $userIds = array_values(array_unique(array_filter(array_column($calificaciones, 'created_by'))));
        $users   = [];
        if (!empty($userIds)) {
            $namedParams  = [];
            $placeholders = [];
            foreach ($userIds as $k => $uid) {
                $key = ':uid' . $k;
                $placeholders[]   = $key;
                $namedParams[$key] = $uid;
            }
            $userRows = Yii::$app->db->createCommand(
                "SELECT id, username FROM [user] WHERE id IN (" . implode(',', $placeholders) . ")",
                $namedParams
            )->queryAll();
            foreach ($userRows as $u) {
                $users[$u['id']] = $u['username'];
            }
        }

        return $this->render('historial', [
            'calificaciones' => $calificaciones,
            'users'          => $users,
            'filtros'        => $filtros,
        ]);
    }

    // ---------------------------------------------------------------
    //  Exportar historial a Excel
    // ---------------------------------------------------------------
    public function actionExportHistorial()
    {
        $request = Yii::$app->request;
        $filtros = [
            'q_oc'        => trim($request->get('q_oc', '')),
            'q_proveedor' => trim($request->get('q_proveedor', '')),
            'q_desde'     => trim($request->get('q_desde', '')),
            'q_hasta'     => trim($request->get('q_hasta', '')),
        ];

        $where  = [];
        $params = [];

        if (!empty($filtros['q_oc'])) {
            $where[]         = "cp.numero_oc LIKE :q_oc";
            $params[':q_oc'] = '%' . $filtros['q_oc'] . '%';
        }
        if (!empty($filtros['q_proveedor'])) {
            $where[]              = "cp.proveedor LIKE :q_proveedor";
            $params[':q_proveedor'] = '%' . $filtros['q_proveedor'] . '%';
        }
        if (!empty($filtros['q_desde'])) {
            $where[]           = "CAST(cp.created_at AS DATE) >= :q_desde";
            $params[':q_desde'] = $filtros['q_desde'];
        }
        if (!empty($filtros['q_hasta'])) {
            $where[]           = "CAST(cp.created_at AS DATE) <= :q_hasta";
            $params[':q_hasta'] = $filtros['q_hasta'];
        }

        $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sql = "
            SELECT
                cp.id,
                cp.numero_oc,
                cp.proveedor,
                cp.categoria,
                cp.subcategoria,
                cp.tipo_mercancia,
                cp.transportadora,
                cp.revisado_por,
                cp.unidades_ordenadas,
                cp.unidades_entregadas,
                CONVERT(VARCHAR(10), cp.fecha_entrega_cita, 120) AS fecha_cita,
                CONVERT(VARCHAR(10), cp.fecha_entrega_oc,   120) AS fecha_entrega_oc,
                cp.caja_bulto, cp.calibre, cp.rotulo, cp.contiene_documentos,
                cp.separa_tallas, cp.separa_color, cp.separa_referencia,
                cp.etiquetado, cp.error_tiqueteo, cp.homologacion,
                cp.precio, cp.novedad, cp.factura, cp.orden_compra_doc,
                cp.gancho, cp.tallero,
                cp.calidad_ponderada,
                cp.calidad_producto,
                cp.oportunidad_formula,
                cp.cantidad_formula,
                cp.puntaje_total,
                cp.observacion,
                CONVERT(VARCHAR(10), cp.created_at, 120) AS fecha_calificacion,
                cp.created_by
            FROM calificacionproveedor cp
            {$whereClause}
            ORDER BY cp.created_at DESC
        ";

        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        // Resolver usernames
        $userIds = array_values(array_unique(array_filter(array_column($rows, 'created_by'))));
        $users   = [];
        if (!empty($userIds)) {
            $namedParams  = [];
            $placeholders = [];
            foreach ($userIds as $k => $uid) {
                $key = ':uid' . $k;
                $placeholders[]   = $key;
                $namedParams[$key] = $uid;
            }
            $userRows = Yii::$app->db->createCommand(
                "SELECT id, username FROM [user] WHERE id IN (" . implode(',', $placeholders) . ")",
                $namedParams
            )->queryAll();
            foreach ($userRows as $u) {
                $users[$u['id']] = $u['username'];
            }
        }

        $spread = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet  = $spread->getActiveSheet();
        $sheet->setTitle('Historial');

        $cols = [
            'N° OC', 'Proveedor', 'Categoría', 'Subcategoría', 'Tipo Mercancía', 'Transportadora',
            'Revisado Por', 'Uds. Ordenadas', 'Uds. Entregadas', 'Fecha Cita', 'Fecha Entrega OC',
            'Caja/Bulto', 'Calibre', 'Rótulo', 'Contiene Docs',
            'Separa Tallas', 'Separa Color', 'Separa Referencia',
            'Etiquetado', 'Error Tiqueteo', 'Homologación',
            'Precio', 'Novedad', 'Factura', 'OC Doc',
            'Gancho', 'Tallero',
            'Cal. Criterios (30%)', 'Cal. Producto (30%)',
            'Oportunidad (10%)', 'Cantidad (30%)',
            'Puntaje Total', 'Letra',
            'Observación', 'Fecha Calificación', 'Calificado Por',
        ];

        $colLetter = 'A';
        foreach ($cols as $header) {
            $sheet->setCellValue($colLetter . '1', $header);
            $colLetter++;
        }

        $lastCol = chr(ord('A') + count($cols) - 1);
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'wrapText'   => true],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $rowNum = 2;
        foreach ($rows as $r) {
            $letra = \frontend\models\Calificacionproveedor::puntajeALetra($r['puntaje_total']);
            $sheet->fromArray([
                $r['numero_oc'], $r['proveedor'], $r['categoria'], $r['subcategoria'],
                $r['tipo_mercancia'], $r['transportadora'], $r['revisado_por'],
                (int)$r['unidades_ordenadas'], (int)$r['unidades_entregadas'],
                $r['fecha_cita'], $r['fecha_entrega_oc'],
                $r['caja_bulto'],    $r['calibre'],    $r['rotulo'],    $r['contiene_documentos'],
                $r['separa_tallas'], $r['separa_color'], $r['separa_referencia'],
                $r['etiquetado'],  $r['error_tiqueteo'], $r['homologacion'],
                $r['precio'],      $r['novedad'],     $r['factura'],   $r['orden_compra_doc'],
                $r['gancho'],      $r['tallero'],
                $r['calidad_ponderada'], $r['calidad_producto'],
                $r['oportunidad_formula'], $r['cantidad_formula'],
                $r['puntaje_total'], $letra,
                $r['observacion'], $r['fecha_calificacion'],
                $users[$r['created_by']] ?? 'N/A',
            ], null, 'A' . $rowNum);

            $bgColor = $letra === 'A' ? 'D9EAD3' : ($letra === 'B' ? 'FFF2CC' : 'FCE5CD');
            $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")
                  ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setRGB($bgColor);

            $rowNum++;
        }

        foreach (['A', 'B', 'G', 'AK'] as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $rango     = ($filtros['q_desde'] ?: 'inicio') . '_' . ($filtros['q_hasta'] ?: 'hoy');
        $filename  = "Historial_Calificaciones_{$rango}_" . date('Ymd') . ".xlsx";

        $tempFile = tempnam(sys_get_temp_dir(), 'hist_');
        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spread);
        $writer->save($tempFile);

        return Yii::$app->response->sendFile($tempFile, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tempFile) {
            @unlink($tempFile);
        });
    }

    // ---------------------------------------------------------------
    //  Datos de subcategoría vía AJAX (unidades al seleccionar subcategoría)
    // ---------------------------------------------------------------
    public function actionSubcategoriaData($id_oc, $subcategoria)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $sql = "
            SELECT
                SUM(d.cantidadPedida)  AS unidades_ordenadas,
                SUM(d.cantidadEntrada) AS unidades_entregadas,
                cat.nombre             AS categoria
            FROM ordendecompradetalle d
            INNER JOIN subcategoria sub ON sub.id = d.idSubcategoria
            INNER JOIN categoria    cat ON cat.id = d.idCategoria
            WHERE d.idOrdenCompra = :id AND sub.nombre = :sub
            GROUP BY cat.nombre
        ";
        $row = Yii::$app->db->createCommand($sql, [':id' => $id_oc, ':sub' => $subcategoria])->queryOne();

        return $row ?: ['unidades_ordenadas' => 0, 'unidades_entregadas' => 0, 'categoria' => ''];
    }

    // ---------------------------------------------------------------
    //  Crear calificación
    // ---------------------------------------------------------------
    public function actionCreate($id_oc = null)
    {
        $model = new Calificacionproveedor();
        $subcategorias = [];
        $yaCalificadas = [];

        // Pre-cargar datos desde la OC + programacion si viene el id
        if ($id_oc) {
            $sql = "
                SELECT TOP 1
                    oc.id                                           AS id,
                    ISNULL(td.nombre,'') + '-'
                        + FORMAT(oc.consecutivo,'00000000')         AS numero_oc,
                    oc.idProveedor                                  AS id_proveedor,
                    ISNULL(p.razonSocial,'')                        AS proveedor,
                    ISNULL(p.criterioMercancia,'')                  AS tipo_mercancia,
                    /* Fecha cita y fecha entrega real (agenda más reciente) */
                    (SELECT TOP 1 ag2.fechaCita
                     FROM agendaentregamercancia ag2
                     WHERE ag2.idOrdenCompra = oc.id
                     ORDER BY ag2.id DESC)                          AS fecha_entrega_cita,
                    (SELECT TOP 1 d4.fechaEntrega
                     FROM ordendecompradetalle d4
                     WHERE d4.idOrdenCompra = oc.id
                       AND d4.fechaEntrega IS NOT NULL
                     ORDER BY d4.id DESC)                           AS fecha_entrega_oc,
                    /* Transportadora de la agenda más reciente */
                    (SELECT TOP 1 tr2.nombre
                     FROM agendaentregamercancia ag3
                     INNER JOIN transportadora tr2 ON tr2.id = ag3.idTransportadora
                     WHERE ag3.idOrdenCompra = oc.id
                     ORDER BY ag3.id DESC)                          AS transportadora,
                    /* Empleado logístico que hizo el conteo (programacion más reciente) */
                    (SELECT TOP 1 em.nombreEmpleado
                     FROM agendaentregamercancia ag4
                     INNER JOIN programacionentregamercancia prog ON prog.idAgendaEntregaMercancia = ag4.id
                     INNER JOIN empleadologistica eml ON eml.id = prog.idEmpleadoLogistica
                     INNER JOIN empleado em ON em.id = eml.idEmpleado
                     WHERE ag4.idOrdenCompra = oc.id
                     ORDER BY prog.id DESC)                         AS revisado_por
                FROM ordendecompra oc
                LEFT JOIN proveedor     p  ON p.id  = oc.idProveedor
                LEFT JOIN tipodocumento td ON td.id = oc.idTipoDocumento
                WHERE oc.id = :id
            ";

            $datos = Yii::$app->db->createCommand($sql, [':id' => $id_oc])->queryOne();

            if ($datos) {
                $model->id_ordendecompra = $datos['id'];
                $model->numero_oc        = $datos['numero_oc'];
                $model->id_proveedor     = $datos['id_proveedor'];
                $model->proveedor        = $datos['proveedor'];
                $model->tipo_mercancia   = $datos['tipo_mercancia'];
                $model->transportadora   = $datos['transportadora'];
                $model->fecha_entrega_cita = $datos['fecha_entrega_cita']
                    ? substr($datos['fecha_entrega_cita'], 0, 10) : null;
                $model->fecha_entrega_oc = $datos['fecha_entrega_oc']
                    ? substr($datos['fecha_entrega_oc'], 0, 10) : null;
            }

            // Subcategorías distintas de la OC
            $subcategorias = Calificacionproveedor::subcategoriasDeOc($id_oc);

            // Si solo hay 1 subcategoría, pre-seleccionarla
            if (count($subcategorias) === 1) {
                $model->subcategoria        = $subcategorias[0]['subcategoria'];
                $model->categoria           = $subcategorias[0]['categoria'];
                $model->unidades_ordenadas  = (int)($subcategorias[0]['unidades_ordenadas'] ?? 0);
                $model->unidades_entregadas = (int)($subcategorias[0]['unidades_entregadas'] ?? 0);
            }

            // Calificaciones ya existentes por subcategoría
            $yaCalificadas = Calificacionproveedor::calificadasPorSubcategoria($id_oc);
        }

        // Revisado por = usuario logueado, siempre (no editable por el usuario)
        $model->revisado_por = Yii::$app->user->identity->username ?? '';

        if ($model->load(Yii::$app->request->post())) {
            $model->revisado_por = Yii::$app->user->identity->username ?? '';

            // Si ya existe calificación para esta subcategoría en esta OC, reemplazarla
            $existente = Calificacionproveedor::findOne([
                'id_ordendecompra' => $model->id_ordendecompra,
                'subcategoria'     => $model->subcategoria,
            ]);
            if ($existente && $existente->id !== $model->id) {
                $existente->delete();
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Calificación guardada correctamente.');
                // Si viene de una OC, redirigir al ponderado de la OC
                if ($model->id_ordendecompra) {
                    return $this->redirect(['ponderado', 'id_oc' => $model->id_ordendecompra]);
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
            Yii::$app->session->setFlash('error', 'Error al guardar. Verifique los datos.');
        }

        $proveedores = Proveedor::find()
            ->orderBy('razonSocial')
            ->select(['id', 'razonSocial'])
            ->asArray()
            ->all();
        $proveedoresMap = \yii\helpers\ArrayHelper::map($proveedores, 'id', 'razonSocial');

        return $this->render('create', [
            'model'          => $model,
            'proveedoresMap' => $proveedoresMap,
            'subcategorias'  => $subcategorias,
            'yaCalificadas'  => $yaCalificadas,
        ]);
    }

    // ---------------------------------------------------------------
    //  Resumen ponderado de una OC (todas sus subcategorías)
    // ---------------------------------------------------------------
    public function actionPonderado($id_oc)
    {
        $calificaciones = Calificacionproveedor::find()
            ->where(['id_ordendecompra' => $id_oc])
            ->orderBy(['subcategoria' => SORT_ASC])
            ->all();

        $ponderado = Calificacionproveedor::puntajePonderadoOc($id_oc);

        // Datos generales de la OC
        $ocDatos = Yii::$app->db->createCommand("
            SELECT ISNULL(td.nombre,'') + '-' + FORMAT(oc.consecutivo,'00000000') AS numero_oc,
                   ISNULL(p.razonSocial,'') AS proveedor
            FROM ordendecompra oc
            LEFT JOIN proveedor     p  ON p.id  = oc.idProveedor
            LEFT JOIN tipodocumento td ON td.id = oc.idTipoDocumento
            WHERE oc.id = :id
        ", [':id' => $id_oc])->queryOne();

        // Subcategorías pendientes de calificar
        $todasSubcats  = Calificacionproveedor::subcategoriasDeOc($id_oc);
        $subcatsCalif  = array_column($calificaciones, null, 'subcategoria');
        $subcatsPend   = array_filter($todasSubcats, fn($s) => !isset($subcatsCalif[$s['subcategoria']]));

        return $this->render('ponderado', [
            'calificaciones' => $calificaciones,
            'ponderado'      => $ponderado,
            'ocDatos'        => $ocDatos,
            'id_oc'          => $id_oc,
            'subcatsPend'    => array_values($subcatsPend),
        ]);
    }

    // ---------------------------------------------------------------
    //  Ver calificación
    // ---------------------------------------------------------------
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    // ---------------------------------------------------------------
    //  Editar calificación
    // ---------------------------------------------------------------
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Calificación actualizada.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $proveedores    = Proveedor::find()->orderBy('razonSocial')->select(['id','razonSocial'])->asArray()->all();
        $proveedoresMap = \yii\helpers\ArrayHelper::map($proveedores, 'id', 'razonSocial');

        $subcategorias = $model->id_ordendecompra
            ? Calificacionproveedor::subcategoriasDeOc($model->id_ordendecompra)
            : [];
        $yaCalificadas = $model->id_ordendecompra
            ? Calificacionproveedor::calificadasPorSubcategoria($model->id_ordendecompra)
            : [];

        return $this->render('create', [
            'model'          => $model,
            'proveedoresMap' => $proveedoresMap,
            'subcategorias'  => $subcategorias,
            'yaCalificadas'  => $yaCalificadas,
        ]);
    }

    // ---------------------------------------------------------------
    //  Eliminar
    // ---------------------------------------------------------------
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Calificación eliminada.');
        return $this->redirect(['index']);
    }

    // ---------------------------------------------------------------
    //  Helper: todas las categorías con sus subcategorías para dropdowns
    // ---------------------------------------------------------------
    protected static function getCatSubData()
    {
        $sql = "
            SELECT cat.nombre AS categoria, sub.nombre AS subcategoria
            FROM subcategoria sub
            INNER JOIN categoria cat ON cat.id = sub.idCategoria
            ORDER BY cat.nombre, sub.nombre
        ";
        $rows = Yii::$app->db->createCommand($sql)->queryAll();

        // Estructura: ['CatA' => ['Sub1','Sub2'], 'CatB' => [...]]
        $data = [];
        foreach ($rows as $r) {
            $data[$r['categoria']][] = $r['subcategoria'];
        }
        return $data;
    }

    // ---------------------------------------------------------------

    protected function findModel($id)
    {
        $model = Calificacionproveedor::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Calificación no encontrada.');
        }
        return $model;
    }
}
