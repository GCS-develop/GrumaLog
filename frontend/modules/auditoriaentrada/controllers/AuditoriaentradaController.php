<?php

namespace frontend\modules\auditoriaentrada\controllers;

use frontend\models\Auditoriaentrada;
use frontend\models\Auditoriaentradadetalle;
use frontend\models\Transferenciaordencompraexcel;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AuditoriaentradaController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'finalizar'              => ['POST'],
                    'reabrir'                => ['POST'],
                    'anular-conteo-entrada'  => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $filtroEstado      = Yii::$app->request->get('estado', '');
        $filtroConsecutivo = Yii::$app->request->get('consecutivo', '');
        $filtroDesde       = Yii::$app->request->get('desde', '');
        $filtroHasta       = Yii::$app->request->get('hasta', '');

        $query = Auditoriaentrada::find()->orderBy(['id' => SORT_DESC]);
        if ($filtroEstado !== '') {
            $query->andWhere(['idestado' => (int)$filtroEstado]);
        }
        if ($filtroConsecutivo !== '') {
            $query->andWhere(['consecutivoOrdenCompra' => (int)$filtroConsecutivo]);
        }
        if ($filtroDesde !== '') {
            $query->andWhere(['>=', 'created_at', $filtroDesde . ' 00:00:00']);
        }
        if ($filtroHasta !== '') {
            $query->andWhere(['<=', 'created_at', $filtroHasta . ' 23:59:59']);
        }
        $auditorias = $query->all();

        $statsMap             = [];
        $espMap               = [];
        $creatorMap           = [];
        $operariosMap         = [];
        $conteoProgramacionMap = [];
        if (!empty($auditorias)) {
            $ids = array_column($auditorias, 'id');
            $statsRaw = Yii::$app->db->createCommand("
                SELECT d.idauditoriaentrada,
                       COUNT(DISTINCT d.created_by)                         as numOperarios,
                       SUM(d.cantidad)                                       as totalPaquetes,
                       SUM(d.cantidad * ISNULL(ue.equivalencia, 1))         as totalUnidades,
                       COUNT(d.id)                                           as totalEscaneos
                FROM auditoriaentradadetalle d
                LEFT JOIN item i  ON i.codigoBarras = d.ean
                LEFT JOIN unidadempaque ue ON ue.codigo = i.unidadEmpaque
                WHERE d.idauditoriaentrada IN (" . implode(',', array_map('intval', $ids)) . ")
                GROUP BY d.idauditoriaentrada
            ")->queryAll();
            foreach ($statsRaw as $s) {
                $statsMap[$s['idauditoriaentrada']] = $s;
            }

            $transIds = array_unique(array_column($auditorias, 'idTransferenciaerp'));
            $espRaw = Yii::$app->db->createCommand("
                SELECT idTransferenciaerp, SUM(cantidadBase) as totalEsperado
                FROM transferenciaordencompraexcel
                WHERE idTransferenciaerp IN (" . implode(',', array_map('intval', $transIds)) . ")
                GROUP BY idTransferenciaerp
            ")->queryAll();
            foreach ($espRaw as $e) {
                $espMap[$e['idTransferenciaerp']] = (int)$e['totalEsperado'];
            }

            // Quién creó la auditoría (auditor)
            $creatorRaw = Yii::$app->db->createCommand("
                SELECT a.id, u.username
                FROM auditoriaentrada a
                LEFT JOIN [user] u ON u.id = a.created_by
                WHERE a.id IN (" . implode(',', array_map('intval', $ids)) . ")
            ")->queryAll();
            $creatorMap = [];
            foreach ($creatorRaw as $c) {
                $creatorMap[$c['id']] = $c['username'];
            }

            // Quiénes hicieron el conteo (operarios que escanearon)
            $operariosRaw = Yii::$app->db->createCommand("
                SELECT d.idauditoriaentrada, u.username
                FROM auditoriaentradadetalle d
                INNER JOIN [user] u ON u.id = d.created_by
                WHERE d.idauditoriaentrada IN (" . implode(',', array_map('intval', $ids)) . ")
                GROUP BY d.idauditoriaentrada, u.username
            ")->queryAll();
            $operariosMap = [];
            foreach ($operariosRaw as $o) {
                $operariosMap[$o['idauditoriaentrada']][] = $o['username'];
            }

            // Quién hizo el conteo en el sistema de programación (operario de bodega)
            $conteoProgramacionRaw = Yii::$app->db->createCommand("
                SELECT ae.id, u.username
                FROM auditoriaentrada ae
                INNER JOIN ordendecompra oc ON oc.consecutivo = ae.consecutivoOrdenCompra
                INNER JOIN agendaentregamercancia ag ON ag.idOrdenCompra = oc.id
                INNER JOIN programacionentregamercancia pe ON pe.idAgendaEntregaMercancia = ag.id
                INNER JOIN userconteo ucc ON ucc.id = pe.idUserConteo
                INNER JOIN [user] u ON u.id = ucc.idUser
                WHERE ae.id IN (" . implode(',', array_map('intval', $ids)) . ")
                GROUP BY ae.id, u.username
            ")->queryAll();
            $conteoProgramacionMap = [];
            foreach ($conteoProgramacionRaw as $row) {
                $conteoProgramacionMap[$row['id']][] = $row['username'];
            }
        }

        $totalAbiertas    = Auditoriaentrada::find()->where(['idestado' => Auditoriaentrada::ESTADO_ABIERTA])->count();
        $totalFinalizadas = Auditoriaentrada::find()->where(['idestado' => Auditoriaentrada::ESTADO_FINALIZADA])->count();

        return $this->render('index', [
            'auditorias'       => $auditorias,
            'filtroDesde'      => $filtroDesde,
            'filtroHasta'      => $filtroHasta,
            'statsMap'         => $statsMap,
            'espMap'           => $espMap,
            'creatorMap'            => $creatorMap,
            'operariosMap'          => $operariosMap,
            'conteoProgramacionMap' => $conteoProgramacionMap,
            'totalAbiertas'         => $totalAbiertas,
            'totalFinalizadas' => $totalFinalizadas,
            'filtroEstado'     => $filtroEstado,
            'filtroConsecutivo'=> $filtroConsecutivo,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        $expectedRows = Transferenciaordencompraexcel::find()
            ->select(['item', 'color', 'talla', 'SUM(cantidadBase) as esperado'])
            ->where(['idTransferenciaerp' => $model->idTransferenciaerp])
            ->groupBy(['item', 'color', 'talla'])
            ->asArray()
            ->all();

        $expected = [];
        foreach ($expectedRows as $row) {
            $key = $row['item'] . '|' . trim($row['color']) . '|' . trim($row['talla']);
            $expected[$key] = [
                'item'     => $row['item'],
                'color'    => trim($row['color']),
                'talla'    => trim($row['talla']),
                'esperado' => (int)$row['esperado'],
            ];
        }

        $scannedRows = Yii::$app->db->createCommand("
            SELECT d.item, d.color, d.talla,
                   SUM(d.cantidad)                               as paquetes,
                   SUM(d.cantidad * ISNULL(ue.equivalencia, 1)) as unidades
            FROM auditoriaentradadetalle d
            LEFT JOIN item i  ON i.codigoBarras = d.ean
            LEFT JOIN unidadempaque ue ON ue.codigo = i.unidadEmpaque
            WHERE d.idauditoriaentrada = :id
            GROUP BY d.item, d.color, d.talla
        ", [':id' => $id])->queryAll();

        $scanned = [];
        foreach ($scannedRows as $row) {
            $key = trim($row['item']) . '|' . trim($row['color']) . '|' . trim($row['talla']);
            $scanned[$key] = ['paquetes' => (int)$row['paquetes'], 'unidades' => (int)$row['unidades']];
        }

        $comparativo = [];
        foreach ($expected as $key => $data) {
            $sc = $scanned[$key] ?? ['paquetes' => 0, 'unidades' => 0];
            $comparativo[] = [
                'item'       => $data['item'],
                'color'      => $data['color'],
                'talla'      => $data['talla'],
                'esperado'   => $data['esperado'],
                'escaneado'  => $sc['unidades'],
                'paquetes'   => $sc['paquetes'],
                'diferencia' => $sc['unidades'] - $data['esperado'],
            ];
        }
        foreach ($scanned as $key => $sc) {
            if (!isset($expected[$key])) {
                [$item, $color, $talla] = explode('|', $key, 3);
                $comparativo[] = [
                    'item'       => $item,
                    'color'      => $color,
                    'talla'      => $talla,
                    'esperado'   => 0,
                    'escaneado'  => $sc['unidades'],
                    'paquetes'   => $sc['paquetes'],
                    'diferencia' => $sc['unidades'],
                ];
            }
        }

        usort($comparativo, function ($a, $b) {
            return [$a['item'], $a['talla'], $a['color']] <=> [$b['item'], $b['talla'], $b['color']];
        });

        $porUsuario = Yii::$app->db->createCommand("
            SELECT u.username,
                   SUM(d.cantidad)                               as totalPaquetes,
                   SUM(d.cantidad * ISNULL(ue.equivalencia, 1)) as totalUnidades,
                   COUNT(d.id)                                    as totalEscaneos,
                   MIN(d.created_at)                             as inicio,
                   MAX(d.updated_at)                             as ultimoEscaneo
            FROM auditoriaentradadetalle d
            INNER JOIN [user] u ON u.id = d.created_by
            LEFT JOIN item i  ON i.codigoBarras = d.ean
            LEFT JOIN unidadempaque ue ON ue.codigo = i.unidadEmpaque
            WHERE d.idauditoriaentrada = :id
            GROUP BY u.username
            ORDER BY totalUnidades DESC
        ", [':id' => $id])->queryAll();

        // Desglose por usuario y SKU para identificar quién contó qué
        $porUsuarioSkuRaw = Yii::$app->db->createCommand("
            SELECT d.item, d.color, d.talla, u.username,
                   SUM(d.cantidad * ISNULL(ue.equivalencia, 1)) as unidades
            FROM auditoriaentradadetalle d
            INNER JOIN [user] u ON u.id = d.created_by
            LEFT JOIN item i  ON i.codigoBarras = d.ean
            LEFT JOIN unidadempaque ue ON ue.codigo = i.unidadEmpaque
            WHERE d.idauditoriaentrada = :id
            GROUP BY d.item, d.color, d.talla, u.username
            ORDER BY d.item, d.color, d.talla, u.username
        ", [':id' => $id])->queryAll();

        $porUsuarioSku = [];
        foreach ($porUsuarioSkuRaw as $row) {
            $key = trim($row['item']) . '|' . trim($row['color']) . '|' . trim($row['talla']);
            $porUsuarioSku[$key][] = ['username' => $row['username'], 'unidades' => (int)$row['unidades']];
        }

        // Quién hizo el conteo en el sistema de programación para esta auditoría
        $conteoProgramacionRaw = Yii::$app->db->createCommand("
            SELECT u.username
            FROM auditoriaentrada ae
            INNER JOIN ordendecompra oc ON oc.consecutivo = ae.consecutivoOrdenCompra
            INNER JOIN agendaentregamercancia ag ON ag.idOrdenCompra = oc.id
            INNER JOIN programacionentregamercancia pe ON pe.idAgendaEntregaMercancia = ag.id
            INNER JOIN userconteo ucc ON ucc.id = pe.idUserConteo
            INNER JOIN [user] u ON u.id = ucc.idUser
            WHERE ae.id = :id
            GROUP BY u.username
        ", [':id' => $id])->queryAll();
        $contadoresProgramacion = array_column($conteoProgramacionRaw, 'username');

        // Qué contó cada operario de entrada (conteoentregamercancia) por SKU y por usuario
        $conteoEntradaRaw = Yii::$app->db->createCommand("
            SELECT CONVERT(int, it.item) as item,
                   col.codigo AS color,
                   tal.codigo AS talla,
                   u.username,
                   SUM(cem.unidadesConteo) as unidadesEntrada
            FROM conteoentregamercancia cem
            INNER JOIN item it  ON it.id  = cem.idItem
            INNER JOIN talla tal ON tal.id = it.idTalla
            INNER JOIN color col ON col.id = it.idColor
            INNER JOIN programacionentregamercancia pe ON pe.id = cem.idProgramacionEntregaMercancia
            INNER JOIN userconteo ucc ON ucc.id = pe.idUserConteo
            INNER JOIN [user] u ON u.id = ucc.idUser
            INNER JOIN agendaentregamercancia ag ON ag.id = pe.idAgendaEntregaMercancia
            INNER JOIN ordendecompra oc ON oc.id = ag.idOrdenCompra
            WHERE oc.consecutivo = :consecutivo
            GROUP BY CONVERT(int, it.item), col.codigo, tal.codigo, u.username
            ORDER BY CONVERT(int, it.item), tal.codigo, col.codigo, u.username
        ", [':consecutivo' => $model->consecutivoOrdenCompra])->queryAll();
        $conteoEntradaSku     = [];  // key → total unidades
        $conteoEntradaSkuUser = [];  // key → [{username, unidades}]
        foreach ($conteoEntradaRaw as $row) {
            $key = trim($row['item']) . '|' . trim($row['color']) . '|' . trim($row['talla']);
            $conteoEntradaSku[$key] = ($conteoEntradaSku[$key] ?? 0) + (int)$row['unidadesEntrada'];
            $conteoEntradaSkuUser[$key][] = ['username' => $row['username'], 'unidades' => (int)$row['unidadesEntrada']];
        }

        return $this->render('view', [
            'model'                  => $model,
            'comparativo'            => $comparativo,
            'porUsuario'             => $porUsuario,
            'porUsuarioSku'          => $porUsuarioSku,
            'contadoresProgramacion' => $contadoresProgramacion,
            'conteoEntradaSku'       => $conteoEntradaSku,
            'conteoEntradaSkuUser'   => $conteoEntradaSkuUser,
        ]);
    }

    public function actionAnularConteoEntrada($id)
    {
        $model = $this->findModel($id);

        // Borrar todos los registros de conteoentregamercancia ligados a esta OC
        Yii::$app->db->createCommand("
            DELETE FROM conteoentregamercancia
            WHERE idProgramacionEntregaMercancia IN (
                SELECT pe.id
                FROM programacionentregamercancia pe
                INNER JOIN agendaentregamercancia ag ON ag.id = pe.idAgendaEntregaMercancia
                INNER JOIN ordendecompra oc ON oc.id = ag.idOrdenCompra
                WHERE oc.consecutivo = :consecutivo
            )
        ", [':consecutivo' => $model->consecutivoOrdenCompra])->execute();

        // Regresar programaciones a estado en-conteo (codigo=1) para permitir recontar
        Yii::$app->db->createCommand("
            UPDATE programacionentregamercancia
            SET idEstado = (SELECT id FROM estadoprogramacion WHERE codigo = 1)
            WHERE id IN (
                SELECT pe.id
                FROM programacionentregamercancia pe
                INNER JOIN agendaentregamercancia ag ON ag.id = pe.idAgendaEntregaMercancia
                INNER JOIN ordendecompra oc ON oc.id = ag.idOrdenCompra
                WHERE oc.consecutivo = :consecutivo
            )
        ", [':consecutivo' => $model->consecutivoOrdenCompra])->execute();

        Yii::$app->session->setFlash('success', 'Conteo de entrada anulado. El operario puede realizar un nuevo conteo desde la app.');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionFinalizar($id)
    {
        $model = $this->findModel($id);
        $model->idestado = Auditoriaentrada::ESTADO_FINALIZADA;
        $model->save(false);
        Yii::$app->session->setFlash('success', 'Auditoría finalizada correctamente.');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionReabrir($id)
    {
        $model = $this->findModel($id);
        $model->idestado = Auditoriaentrada::ESTADO_ABIERTA;
        $model->save(false);
        Yii::$app->session->setFlash('success', 'Auditoría reabierta para nuevos escaneos.');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionExportar()
    {
        $filtroEstado      = Yii::$app->request->get('estado', '');
        $filtroConsecutivo = Yii::$app->request->get('consecutivo', '');
        $filtroDesde       = Yii::$app->request->get('desde', '');
        $filtroHasta       = Yii::$app->request->get('hasta', '');

        $query = Auditoriaentrada::find()->orderBy(['id' => SORT_ASC]);
        if ($filtroEstado !== '')      { $query->andWhere(['idestado' => (int)$filtroEstado]); }
        if ($filtroConsecutivo !== '') { $query->andWhere(['consecutivoOrdenCompra' => (int)$filtroConsecutivo]); }
        if ($filtroDesde !== '')       { $query->andWhere(['>=', 'created_at', $filtroDesde . ' 00:00:00']); }
        if ($filtroHasta !== '')       { $query->andWhere(['<=', 'created_at', $filtroHasta . ' 23:59:59']); }
        $auditorias = $query->all();

        $spread = new Spreadsheet();
        $sh     = $spread->getActiveSheet();
        $sh->setTitle('Auditoría Entradas');

        // Cabecera
        $headers = ['OC', 'Fecha', 'Estado', 'Auditor', 'Conteo Entrada', 'Item', 'Color', 'Talla',
                    'UNDentradas', 'Cto.Entrada', 'Paquetes', 'Auditado (uds)', 'Diferencia'];
        $sh->fromArray($headers, null, 'A1');

        // Estilo cabecera
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sh->getStyle('A1:M1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($auditorias as $aud) {
            // Datos de usuarios
            $auditorName = Yii::$app->db->createCommand(
                "SELECT u.username FROM auditoriaentrada a LEFT JOIN [user] u ON u.id = a.created_by WHERE a.id = :id",
                [':id' => $aud->id]
            )->queryScalar() ?? '—';

            $contadoresEntrada = Yii::$app->db->createCommand("
                SELECT u.username
                FROM auditoriaentrada ae
                INNER JOIN ordendecompra oc ON oc.consecutivo = ae.consecutivoOrdenCompra
                INNER JOIN agendaentregamercancia ag ON ag.idOrdenCompra = oc.id
                INNER JOIN programacionentregamercancia pe ON pe.idAgendaEntregaMercancia = ag.id
                INNER JOIN userconteo ucc ON ucc.id = pe.idUserConteo
                INNER JOIN [user] u ON u.id = ucc.idUser
                WHERE ae.id = :id GROUP BY u.username
            ", [':id' => $aud->id])->queryColumn();
            $conteoEntradaStr = implode(', ', $contadoresEntrada);

            $ocLabel = $aud->tipoDocumentoOrdenCompra . '-' . $aud->consecutivoOrdenCompra;
            $fecha   = date('d/m/Y H:i', strtotime($aud->created_at));
            $estado  = $aud->estadoLabel;

            // SKUs esperados
            $expectedRows = Transferenciaordencompraexcel::find()
                ->select(['item', 'color', 'talla', 'SUM(cantidadBase) as esperado'])
                ->where(['idTransferenciaerp' => $aud->idTransferenciaerp])
                ->groupBy(['item', 'color', 'talla'])->asArray()->all();
            $expected = [];
            foreach ($expectedRows as $er) {
                $k = $er['item'] . '|' . trim($er['color']) . '|' . trim($er['talla']);
                $expected[$k] = (int)$er['esperado'];
            }

            // SKUs auditados
            $scannedRows = Yii::$app->db->createCommand("
                SELECT d.item, d.color, d.talla,
                       SUM(d.cantidad) as paquetes,
                       SUM(d.cantidad * ISNULL(ue.equivalencia,1)) as unidades
                FROM auditoriaentradadetalle d
                LEFT JOIN item i ON i.codigoBarras = d.ean
                LEFT JOIN unidadempaque ue ON ue.codigo = i.unidadEmpaque
                WHERE d.idauditoriaentrada = :id
                GROUP BY d.item, d.color, d.talla
            ", [':id' => $aud->id])->queryAll();
            $scanned = [];
            foreach ($scannedRows as $sr) {
                $k = trim($sr['item']) . '|' . trim($sr['color']) . '|' . trim($sr['talla']);
                $scanned[$k] = ['paquetes' => (int)$sr['paquetes'], 'unidades' => (int)$sr['unidades']];
            }

            // SKUs conteo entrada
            $entradaRows = Yii::$app->db->createCommand("
                SELECT CONVERT(int, it.item) as item, col.codigo AS color, tal.codigo AS talla,
                       SUM(cem.unidadesConteo) as unidadesEntrada
                FROM conteoentregamercancia cem
                INNER JOIN item it  ON it.id  = cem.idItem
                INNER JOIN talla tal ON tal.id = it.idTalla
                INNER JOIN color col ON col.id = it.idColor
                INNER JOIN programacionentregamercancia pe ON pe.id = cem.idProgramacionEntregaMercancia
                INNER JOIN agendaentregamercancia ag ON ag.id = pe.idAgendaEntregaMercancia
                INNER JOIN ordendecompra oc ON oc.id = ag.idOrdenCompra
                WHERE oc.consecutivo = :consecutivo
                GROUP BY CONVERT(int, it.item), col.codigo, tal.codigo
            ", [':consecutivo' => $aud->consecutivoOrdenCompra])->queryAll();
            $entrada = [];
            foreach ($entradaRows as $er) {
                $k = trim($er['item']) . '|' . trim($er['color']) . '|' . trim($er['talla']);
                $entrada[$k] = (int)$er['unidadesEntrada'];
            }

            // Unión de todas las claves
            $allKeys = array_unique(array_merge(array_keys($expected), array_keys($scanned), array_keys($entrada)));
            usort($allKeys, function($a, $b) { return strnatcmp($a, $b); });

            if (empty($allKeys)) {
                // Auditoría sin datos — fila vacía
                $sh->fromArray([$ocLabel, $fecha, $estado, $auditorName, $conteoEntradaStr,
                    '—', '—', '—', 0, '', 0, 0, 0], null, 'A' . $row);
                $row++;
                continue;
            }

            $firstRow = $row;
            foreach ($allKeys as $k) {
                [$item, $color, $talla] = explode('|', $k, 3);
                $esp   = $expected[$k] ?? 0;
                $ent   = isset($entrada[$k]) ? $entrada[$k] : '';
                $paq   = $scanned[$k]['paquetes'] ?? 0;
                $aud_u = $scanned[$k]['unidades']  ?? 0;
                $dif   = $aud_u - $esp;

                $sh->fromArray([
                    $ocLabel, $fecha, $estado, $auditorName, $conteoEntradaStr,
                    $item, $color, $talla, $esp, $ent, $paq, $aud_u, $dif
                ], null, 'A' . $row);

                // Color diferencia
                $difColor = $dif == 0 ? '27AE60' : ($dif < 0 ? 'E74C3C' : 'E67E22');
                $sh->getStyle('M' . $row)->getFont()->getColor()->setRGB($difColor);
                $sh->getStyle('M' . $row)->getFont()->setBold(true);

                $row++;
            }

            // Borde de separación entre auditorías
            if ($row > $firstRow) {
                $sh->getStyle('A' . $firstRow . ':M' . ($row - 1))
                   ->getBorders()->getBottom()->setBorderStyle('thin');
            }
        }

        // Autosize columnas
        foreach (range('A', 'M') as $col) {
            $sh->getColumnDimension($col)->setAutoSize(true);
        }
        $sh->getColumnDimension('E')->setWidth(25); // Conteo Entrada (usuarios, puede ser largo)

        $filename = 'Auditoria_Entradas_' . date('Ymd_His') . '.xlsx';
        $writer   = new Xlsx($spread);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return Yii::$app->response->sendContentAsFile(
            $content,
            $filename,
            ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
             'inline'   => false]
        );
    }

    protected function findModel($id)
    {
        if (($model = Auditoriaentrada::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La auditoría no existe.');
    }
}
