<?php

namespace frontend\modules\programacion\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ArrayDataProvider;

class ZonasController extends Controller
{
    public function actionIndex()
    {
        // --- Filtros ---
        $filtroZona       = Yii::$app->request->get('zona', '');
        $filtroItem       = Yii::$app->request->get('item', '');
        $filtroOrden      = Yii::$app->request->get('numeroOrden', '');
        $filtroFechaDesde = Yii::$app->request->get('fechaDesde', '');
        $filtroFechaHasta = Yii::$app->request->get('fechaHasta', '');

        // --- JOINs reutilizables ---
        $joins = "
            FROM conteozona cz
            INNER JOIN zonas z                ON z.id   = cz.idZona
            INNER JOIN item it                ON it.id  = cz.idItem
            INNER JOIN talla tal              ON tal.id = it.idTalla
            INNER JOIN color col              ON col.id = it.idColor
            INNER JOIN conteoentregamercancia cem ON cem.id = cz.idConteoEntregaMercancia
            INNER JOIN programacionentregamercancia pem ON pem.id = cem.idProgramacionEntregaMercancia
            INNER JOIN agendaentregamercancia aem  ON aem.id = pem.idAgendaEntregaMercancia
            INNER JOIN ordendecompra oc            ON oc.id  = aem.idOrdenCompra
            INNER JOIN proveedor p                   ON p.id   = oc.idProveedor
            INNER JOIN tipodocumento td            ON td.id  = oc.idTipoDocumento
            LEFT  JOIN empleadologistica el        ON el.id  = cz.idEmpleadoLogistica
            LEFT  JOIN empleado emp               ON emp.id = el.idEmpleado
        ";

        // --- WHERE dinámico (el mismo para ambas queries) ---
        $where  = " WHERE 1 = 1";
        $params = [];

        if ($filtroZona !== '') {
            $where .= " AND z.codigo = :zona";
            $params[':zona'] = $filtroZona;
        }
        if ($filtroItem !== '') {
            $where .= " AND CAST(it.item AS NVARCHAR(20)) LIKE :item";
            $params[':item'] = '%' . $filtroItem . '%';
        }
        if ($filtroOrden !== '') {
            $where .= " AND CAST(oc.consecutivo AS NVARCHAR(50)) LIKE :orden";
            $params[':orden'] = '%' . $filtroOrden . '%';
        }
        if ($filtroFechaDesde !== '') {
            $where .= " AND CAST(cz.created_at AS DATE) >= :fechaDesde";
            $params[':fechaDesde'] = $filtroFechaDesde;
        }
        if ($filtroFechaHasta !== '') {
            $where .= " AND CAST(cz.created_at AS DATE) <= :fechaHasta";
            $params[':fechaHasta'] = $filtroFechaHasta;
        }

        // -------------------------------------------------------
        // QUERY PRINCIPAL — agrupada: 1 fila por zona+OC+item+color+talla+EAN+empleado
        // -------------------------------------------------------
        $sqlPrincipal = "
            SELECT
                z.codigo                                                         AS zona,
                td.codigo + '-' + CAST(oc.consecutivo AS NVARCHAR(50))          AS numeroOrden,
                p.razonSocial                                                          AS proveedor,
                it.item,
                it.descripcion,
                col.codigo                                                        AS color,
                tal.codigo                                                        AS talla,
                cz.codigoBarras,
                SUM(cz.unidades)                                                 AS unidades,
                COALESCE(emp.nombreEmpleado, '')                                 AS empleado,
                CONVERT(NVARCHAR(16), MAX(cz.created_at), 120)                  AS fechaAsignacion
            $joins
            $where
            GROUP BY
                z.codigo,
                td.codigo + '-' + CAST(oc.consecutivo AS NVARCHAR(50)),
                p.razonSocial,
                it.item,
                it.descripcion,
                col.codigo,
                tal.codigo,
                cz.codigoBarras,
                COALESCE(emp.nombreEmpleado, '')
            ORDER BY z.codigo, it.item, col.codigo, tal.codigo
        ";

        // -------------------------------------------------------
        // QUERY TOTALES — unidades y ítems distintos por zona
        // -------------------------------------------------------
        $sqlTotales = "
            SELECT
                z.codigo                    AS zona,
                SUM(cz.unidades)            AS totalUnidades,
                COUNT(DISTINCT cz.idItem)   AS totalItems
            $joins
            $where
            GROUP BY z.codigo
            ORDER BY z.codigo
        ";

        $data    = Yii::$app->db->createCommand($sqlPrincipal, $params)->queryAll();
        $totales = Yii::$app->db->createCommand($sqlTotales,   $params)->queryAll();

        // Lista de zonas para el dropdown del filtro
        $listaZonas = Yii::$app->db->createCommand(
            "SELECT codigo FROM zonas WHERE activo = 1 ORDER BY codigo"
        )->queryColumn();

        $dataProvider = new ArrayDataProvider([
            'allModels'  => $data,
            'pagination' => ['pageSize' => 50],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'listaZonas'   => $listaZonas,
            'totales'      => $totales,
            'filtros'      => [
                'zona'        => $filtroZona,
                'item'        => $filtroItem,
                'numeroOrden' => $filtroOrden,
                'fechaDesde'  => $filtroFechaDesde,
                'fechaHasta'  => $filtroFechaHasta,
            ],
        ]);
    }
}
