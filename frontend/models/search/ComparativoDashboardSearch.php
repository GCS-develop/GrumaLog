<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;

/**
 * ComparativoDashboardSearch
 *
 * Genera datos para el comparativo: OC Entrada vs Conteo Logística vs Traspasos.
 */
class ComparativoDashboardSearch extends Model
{
    public $desde;       // YYYY-MM-DD  (fechaDocumento en transferenciaordencompraexcel)
    public $hasta;       // YYYY-MM-DD
    public $tercero;     // código proveedor (transferenciaordencompraexcel.tercero)
    public $item;        // referencia item (opcional)

    public function rules()
    {
        return [
            [['desde', 'hasta', 'tercero', 'item'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'desde'   => 'Fecha Desde',
            'hasta'   => 'Fecha Hasta',
            'tercero' => 'Proveedor',
            'item'    => 'Item (referencia)',
        ];
    }

    /**
     * OCs del proveedor.
     * Usa la FK directa ordendecompra.idProveedor → proveedor.nit.
     * Muestra todas las OCs del proveedor; no filtra por fecha de OC
     * porque los períodos de la agenda difieren del período SIESA.
     */
    public function searchOcsPorProveedor($params, $tercero)
    {
        $this->load($params);

        $tz    = new \DateTimeZone('America/Bogota');
        $desde = $this->desde ?: (new \DateTime('now', $tz))->format('Y-m-01');
        $hasta = $this->hasta ?: (new \DateTime('now', $tz))->format('Y-m-d');

        $sql = "
            SELECT
                oc.id                                                                            AS idOc,
                LTRIM(RTRIM(td.codigo)) + '-'
                    + CAST(CAST(oc.consecutivo AS int) AS varchar(20))                           AS oc_gruma,
                CONVERT(varchar(10), oc.fecha, 23)                                               AS fecha_oc,
                MAX(ISNULL(el.nombre, '(sin agenda)'))                                           AS estado_legalizacion,
                MAX(ISNULL(aem.idEstadoLegalizacion, 0))                                         AS idEstadoLegalizacion,
                COUNT(DISTINCT fac.id)                                                           AS num_facturas,
                MAX(ISNULL(ped.cant_pedida, 0))                                                  AS cant_pedida,
                MAX(ISNULL(cont.cant_conteo, 0))                                                 AS cant_conteo
            FROM ordendecompra oc
            INNER JOIN tipodocumento td ON td.id = oc.idTipoDocumento
            INNER JOIN proveedor p      ON p.id  = oc.idProveedor
                AND LTRIM(RTRIM(p.nit)) = :tercero
            LEFT  JOIN agendaentregamercancia aem ON aem.idOrdenCompra = oc.id
            LEFT  JOIN estadolegalizacion el       ON el.id = aem.idEstadoLegalizacion
            LEFT  JOIN facturaentregamercancia fac ON fac.idAgendaEntregaMercancia = aem.id
            LEFT  JOIN (
                SELECT ocd.idOrdenCompra, SUM(ocd.cantidadPedida) AS cant_pedida
                FROM ordendecompradetalle ocd
                GROUP BY ocd.idOrdenCompra
            ) ped  ON ped.idOrdenCompra = oc.id
            LEFT  JOIN (
                SELECT aem2.idOrdenCompra,
                       SUM(cem.unidadesConteo * COALESCE(NULLIF(cem.unidadxPaquete, 0), 1)) AS cant_conteo
                FROM conteoentregamercancia cem
                INNER JOIN programacionentregamercancia pem ON pem.id  = cem.idProgramacionEntregaMercancia
                                                          AND pem.idUserConteo IS NOT NULL
                INNER JOIN agendaentregamercancia aem2      ON aem2.id = pem.idAgendaEntregaMercancia
                WHERE aem2.idEstadoLegalizacion = 2
                GROUP BY aem2.idOrdenCompra
            ) cont ON cont.idOrdenCompra = oc.id
            WHERE oc.fecha BETWEEN :desde AND :hasta
            GROUP BY oc.id, td.codigo, oc.consecutivo, oc.fecha
            ORDER BY oc.fecha DESC, oc.consecutivo DESC
        ";

        return \Yii::$app->db->createCommand($sql, [
            ':tercero' => trim($tercero),
            ':desde'   => $desde,
            ':hasta'   => $hasta,
        ])->queryAll();
    }

    /**
     * Items de una OC con comparativo Pedido OC vs Conteo Logística legalizado.
     * Agrupa por SKU: (item SIESA + talla + color).
     */
    public function searchItemsPorOc($idOc)
    {
        $sql = "
            SELECT
                CAST(i.item AS bigint)                                                    AS item_codigo,
                ISNULL(LTRIM(RTRIM(ta.codigo)), '—')                                      AS talla,
                ISNULL(LTRIM(RTRIM(co.codigo)), '—')                                      AS color,
                MAX(LTRIM(RTRIM(i.descripcion)))                                           AS descripcion,
                SUM(ocd.cantidadPedida)                                                   AS cant_pedida,
                ISNULL(MAX(cont.cant_conteo), 0)                                          AS cant_conteo
            FROM ordendecompradetalle ocd
            INNER JOIN item  i   ON i.id  = ocd.idItem
            LEFT  JOIN talla ta  ON ta.id = i.idTalla
            LEFT  JOIN color co  ON co.id = i.idColor
            LEFT  JOIN (
                SELECT
                    CAST(i2.item AS bigint)         AS item_siesa,
                    ISNULL(ta2.codigo, '')           AS talla_cod,
                    ISNULL(co2.codigo, '')           AS color_cod,
                    SUM(cem.unidadesConteo * COALESCE(NULLIF(ue.equivalencia, 0), 1)) AS cant_conteo
                FROM conteoentregamercancia cem
                INNER JOIN programacionentregamercancia pem ON pem.id = cem.idProgramacionEntregaMercancia
                INNER JOIN agendaentregamercancia aem        ON aem.id = pem.idAgendaEntregaMercancia
                INNER JOIN item i2   ON i2.id   = cem.idItem
                LEFT  JOIN talla ta2 ON ta2.id  = i2.idTalla
                LEFT  JOIN color co2 ON co2.id  = i2.idColor
                LEFT  JOIN unidadempaque ue ON ue.codigo = i2.unidadEmpaque
                WHERE aem.idOrdenCompra        = :idOc1
                  AND aem.idEstadoLegalizacion = 2
                GROUP BY CAST(i2.item AS bigint), ISNULL(ta2.codigo, ''), ISNULL(co2.codigo, '')
            ) cont ON cont.item_siesa = CAST(i.item AS bigint)
                  AND cont.talla_cod  = ISNULL(ta.codigo, '')
                  AND cont.color_cod  = ISNULL(co.codigo, '')
            WHERE ocd.idOrdenCompra = :idOc2
            GROUP BY CAST(i.item AS bigint), ta.codigo, co.codigo
            ORDER BY CAST(i.item AS bigint), ta.codigo, co.codigo
        ";

        $rows = \Yii::$app->db->createCommand($sql, [
            ':idOc1' => (int)$idOc,
            ':idOc2' => (int)$idOc,
        ])->queryAll();

        foreach ($rows as &$row) {
            $row['diferencia'] = (int)$row['cant_conteo'] - (int)$row['cant_pedida'];
        }
        unset($row);

        return $rows;
    }

    /**
     * Agrega el resultado al nivel de proveedor.
     * El conteo se calcula con una query dedicada a nivel de OC para evitar
     * el problema del join por idItem que subestima los conteos.
     */
    public function searchComparativoProveedores($params)
    {
        $items  = $this->searchComparativo($params);
        $result = [];

        foreach ($items as $row) {
            $key = trim($row['tercero'] ?? '');
            if (!isset($result[$key])) {
                $result[$key] = [
                    'tercero'        => $key,
                    'proveedor'      => $row['proveedor'],
                    'cant_entrada'   => 0,
                    'cant_conteo'    => 0,
                    'cant_traspasos' => 0,
                    'cant_tienda'    => 0,
                ];
            }
            $result[$key]['cant_entrada']   += (int)$row['cant_entrada'];
            // cant_conteo se reemplaza abajo con queryConteoPorProveedor
            $result[$key]['cant_traspasos'] += (int)$row['cant_traspasos'];
            $result[$key]['cant_tienda']    += (int)$row['cant_tienda'];
        }

        // Conteo preciso a nivel de OC: suma todos los ítems contados
        // en las OCs que tienen entrada SIESA en el período.
        foreach ($this->queryConteoPorProveedor($params) as $row) {
            $key = trim($row['tercero'] ?? '');
            if (isset($result[$key])) {
                $result[$key]['cant_conteo'] = (int)$row['cant_conteo'];
            }
        }

        foreach ($result as &$prov) {
            $prov['dif_conteo_entrada']   = $prov['cant_conteo']    - $prov['cant_entrada'];
            $prov['dif_traspasos_conteo'] = $prov['cant_traspasos'] - $prov['cant_conteo'];
            $prov['dif_tienda_traspaso']  = $prov['cant_tienda']    - $prov['cant_traspasos'];
        }
        unset($prov);

        usort($result, fn($a, $b) => strcmp($a['proveedor'], $b['proveedor']));
        return array_values($result);
    }

    /**
     * Conteo total por proveedor, enlazado vía las OCs que tienen
     * entrada SIESA en el período (tipoDocumento + consecutivo).
     * Evita el join por idItem que subestima el resultado.
     */
    private function queryConteoPorProveedor($params)
    {
        $this->load($params);

        $tz    = new \DateTimeZone('America/Bogota');
        $desde = $this->desde ?: (new \DateTime('now', $tz))->format('Y-m-01');
        $hasta = $this->hasta ?: (new \DateTime('now', $tz))->format('Y-m-d');

        $terceroFilter = '';
        $bindings = [
            ':desdeCP' => $desde,
            ':hastaCP' => $hasta,
        ];

        if (!empty($this->tercero)) {
            $terceroFilter          = "AND LTRIM(RTRIM(p.nit)) = :terceroCP";
            $bindings[':terceroCP'] = trim($this->tercero);
        }

        // Misma lógica que searchOcsPorProveedor pero agregada a nivel proveedor.
        // Filtra por oc.fecha para que el total coincida con la suma de OCs del modal.
        $sql = "
            SELECT
                LTRIM(RTRIM(p.nit))                                                       AS tercero,
                SUM(cem.unidadesConteo * COALESCE(NULLIF(cem.unidadxPaquete, 0), 1))      AS cant_conteo
            FROM ordendecompra oc
            INNER JOIN proveedor p ON p.id = oc.idProveedor
            INNER JOIN agendaentregamercancia aem_c
                    ON aem_c.idOrdenCompra        = oc.id
                   AND aem_c.idEstadoLegalizacion = 2
            INNER JOIN programacionentregamercancia pem_c
                    ON pem_c.idAgendaEntregaMercancia = aem_c.id
                   AND pem_c.idUserConteo IS NOT NULL
            INNER JOIN conteoentregamercancia cem
                    ON cem.idProgramacionEntregaMercancia = pem_c.id
            WHERE oc.fecha BETWEEN :desdeCP AND :hastaCP
              {$terceroFilter}
            GROUP BY LTRIM(RTRIM(p.nit))
        ";

        return \Yii::$app->db->createCommand($sql, $bindings)->queryAll();
    }

    /**
     * Devuelve los proveedores distintos presentes en las entradas OC,
     * mostrando la razón social cuando existe en la tabla proveedor.
     */
    public function getProveedores()
    {
        $rows = Yii::$app->db->createCommand(
            "SELECT DISTINCT
                 LTRIM(RTRIM(toce.tercero)) AS tercero,
                 ISNULL(LTRIM(RTRIM(p.razonSocial)), LTRIM(RTRIM(toce.tercero))) AS nombre
             FROM transferenciaordencompraexcel toce
             LEFT JOIN proveedor p ON LTRIM(RTRIM(p.nit)) = LTRIM(RTRIM(toce.tercero))
             WHERE toce.tercero IS NOT NULL AND LTRIM(RTRIM(toce.tercero)) <> ''
             ORDER BY nombre"
        )->queryAll();

        $list = [];
        foreach ($rows as $r) {
            $list[$r['tercero']] = $r['nombre'];
        }
        return $list;
    }

    /**
     * Comparativo por item + proveedor.
     *
     * Columnas:
     *   idItem, referencia, color, talla, proveedor,
     *   cant_entrada   (transferenciaordencompraexcel)
     *   cant_conteo    (conteoentregamercancia)
     *   cant_traspasos (traspasodetalle  – salida bodega)
     *   cant_tienda    (traspasodetalletienda – recepción en tienda)
     */
    public function searchComparativo($params)
    {
        $this->load($params);

        $tz     = new \DateTimeZone('America/Bogota');
        $desde  = $this->desde ?: (new \DateTime('now', $tz))->format('Y-m-01');
        $hasta  = $this->hasta ?: (new \DateTime('now', $tz))->format('Y-m-d');

        // Fecha inicio/fin para traspasos (datetime range)
        $dtDesde = $desde . ' 00:00:00';
        $dtHasta = $hasta . ' 23:59:59';

        $terceroFilter    = '';
        $itemFilterEntr   = '';
        $itemFilterCont   = '';
        $itemFilterTras   = '';
        $itemFilterTienda = '';
        // ODBC SQL Server no permite reusar parámetros nombrados en la misma query,
        // por eso cada subquery usa nombres únicos
        $bindings = [
            ':desde'    => $desde,
            ':hasta'    => $hasta,
            ':desdeC'   => $desde,
            ':hastaC'   => $hasta,
            ':dtDesde2' => $dtDesde,
            ':dtHasta2' => $dtHasta,
            ':dtDesde3' => $dtDesde,
            ':dtHasta3' => $dtHasta,
        ];

        if (!empty($this->tercero)) {
            $terceroFilter        = "AND LTRIM(RTRIM(toce.tercero)) = :tercero";
            $bindings[':tercero'] = trim($this->tercero);
        }

        if (!empty($this->item)) {
            $itemRef               = '%' . trim($this->item) . '%';
            $itemFilterEntr        = "AND CAST(i.item  AS varchar(50)) LIKE :itemRef1";
            $itemFilterCont        = "AND CAST(i2.item AS varchar(50)) LIKE :itemRef2";
            $itemFilterTras        = "AND CAST(i3.item AS varchar(50)) LIKE :itemRef3";
            $itemFilterTienda      = "AND CAST(i4.item AS varchar(50)) LIKE :itemRef4";
            $bindings[':itemRef1'] = $itemRef;
            $bindings[':itemRef2'] = $itemRef;
            $bindings[':itemRef3'] = $itemRef;
            $bindings[':itemRef4'] = $itemRef;
        }

        $sql = "
            SELECT
                toce_agg.idItem,
                toce_agg.item_codigo,
                toce_agg.descripcion,
                toce_agg.talla,
                toce_agg.color,
                toce_agg.tercero,
                toce_agg.proveedor,
                toce_agg.cant_entrada,
                ISNULL(cont_agg.cant_conteo,    0) AS cant_conteo,
                ISNULL(tras_agg.cant_traspasos, 0) AS cant_traspasos,
                ISNULL(tienda_agg.cant_tienda,  0) AS cant_tienda,
                (ISNULL(cont_agg.cant_conteo,    0) - toce_agg.cant_entrada)               AS dif_conteo_entrada,
                (ISNULL(tras_agg.cant_traspasos, 0) - ISNULL(cont_agg.cant_conteo, 0))     AS dif_traspasos_conteo,
                (ISNULL(tienda_agg.cant_tienda,  0) - ISNULL(tras_agg.cant_traspasos, 0))  AS dif_tienda_traspaso
            FROM (
                SELECT
                    toce.item                                                           AS idItem,
                    CAST(i.item AS bigint)                                              AS item_codigo,
                    LTRIM(RTRIM(i.descripcion))                                         AS descripcion,
                    ISNULL(LTRIM(RTRIM(ta.codigo)), '—')                                AS talla,
                    ISNULL(LTRIM(RTRIM(co.codigo)), '—')                                AS color,
                    LTRIM(RTRIM(toce.tercero))                                          AS tercero,
                    ISNULL(LTRIM(RTRIM(p.razonSocial)), LTRIM(RTRIM(toce.tercero)))    AS proveedor,
                    SUM(toce.cantidadBase)                                              AS cant_entrada
                FROM transferenciaordencompraexcel toce
                INNER JOIN item i    ON i.id   = toce.item
                LEFT  JOIN talla ta  ON ta.id  = i.idTalla
                LEFT  JOIN color co  ON co.id  = i.idColor
                LEFT  JOIN proveedor p ON LTRIM(RTRIM(p.nit)) = LTRIM(RTRIM(toce.tercero))
                WHERE toce.fechaDocumento BETWEEN :desde AND :hasta
                  {$terceroFilter}
                  {$itemFilterEntr}
                GROUP BY toce.item, i.item, i.descripcion, ta.codigo, co.codigo, toce.tercero, p.razonSocial
            ) toce_agg

            LEFT JOIN (
                -- Conteos de agendas LEGALIZADAS enlazados vía número de OC de la entrada SIESA.
                -- Se usa tipoDocumentoOrdenCompra + consecutivoOrdenCompra para identificar la OC
                -- en GRUMALog, evitando depender de la coincidencia de numeroFactura.
                SELECT
                    cem.idItem,
                    SUM(cem.unidadesConteo * COALESCE(NULLIF(ue_c.equivalencia, 0), 1)) AS cant_conteo
                FROM transferenciaordencompraexcel toce_c
                INNER JOIN tipodocumento td_c
                        ON LTRIM(RTRIM(td_c.codigo)) = LTRIM(RTRIM(toce_c.tipoDocumentoOrdenCompra))
                INNER JOIN ordendecompra oc_c
                        ON oc_c.consecutivo     = toce_c.consecutivoOrdenCompra
                       AND oc_c.idTipoDocumento = td_c.id
                INNER JOIN agendaentregamercancia aem_c
                        ON aem_c.idOrdenCompra        = oc_c.id
                       AND aem_c.idEstadoLegalizacion = 2
                INNER JOIN programacionentregamercancia pem_c
                        ON pem_c.idAgendaEntregaMercancia = aem_c.id
                INNER JOIN conteoentregamercancia cem
                        ON cem.idProgramacionEntregaMercancia = pem_c.id
                INNER JOIN item i2    ON i2.id       = cem.idItem
                LEFT  JOIN unidadempaque ue_c ON ue_c.codigo = i2.unidadEmpaque
                WHERE toce_c.fechaDocumento BETWEEN :desdeC AND :hastaC
                  {$itemFilterCont}
                GROUP BY cem.idItem
            ) cont_agg ON cont_agg.idItem = toce_agg.idItem

            LEFT JOIN (
                SELECT
                    td.idItem,
                    SUM(td.cantidad * COALESCE(NULLIF(ue_t.equivalencia, 0), 1)) AS cant_traspasos
                FROM traspasodetalle td
                INNER JOIN traspaso t     ON t.id          = td.idTraspaso AND t.idEstado <> 2
                INNER JOIN item i3        ON i3.id         = td.idItem
                LEFT  JOIN unidadempaque ue_t ON ue_t.codigo = i3.unidadEmpaque
                WHERE TRY_CAST(t.created_at AS datetime2) BETWEEN :dtDesde2 AND :dtHasta2
                  {$itemFilterTras}
                GROUP BY td.idItem
            ) tras_agg ON tras_agg.idItem = toce_agg.idItem

            LEFT JOIN (
                SELECT
                    tdt.idItem,
                    SUM(tdt.cantidad * COALESCE(NULLIF(ue_ti.equivalencia, 0), 1)) AS cant_tienda
                FROM traspasodetalletienda tdt
                INNER JOIN traspaso t4      ON t4.id           = tdt.idTraspaso AND t4.idEstado <> 2
                INNER JOIN item i4          ON i4.id           = tdt.idItem
                LEFT  JOIN unidadempaque ue_ti ON ue_ti.codigo = i4.unidadEmpaque
                WHERE TRY_CAST(t4.created_at AS datetime2) BETWEEN :dtDesde3 AND :dtHasta3
                  {$itemFilterTienda}
                GROUP BY tdt.idItem
            ) tienda_agg ON tienda_agg.idItem = toce_agg.idItem

            ORDER BY toce_agg.item_codigo, toce_agg.talla, toce_agg.color
        ";

        return Yii::$app->db->createCommand($sql, $bindings)->queryAll();
    }

    /**
     * Detalle de traspasos por item: a qué tiendas salió, quién contó en bodega
     * y cuánto recibió la tienda (traspasodetalletienda).
     */
    public function searchTraspasoDetalle($params, $idItem)
    {
        $this->load($params);

        $tz      = new \DateTimeZone('America/Bogota');
        $desde   = $this->desde ?: (new \DateTime('now', $tz))->format('Y-m-01');
        $hasta   = $this->hasta ?: (new \DateTime('now', $tz))->format('Y-m-d');
        $dtDesde = $desde . ' 00:00:00';
        $dtHasta = $hasta . ' 23:59:59';

        $sql = "
            SELECT
                t.id                                                                        AS idTraspaso,
                ds.f350_consec_docto                                                        AS consecutivo_siesa,
                bo.nombre                                                                   AS bodega_origen,
                bd.nombre                                                                   AS bodega_destino,
                LTRIM(RTRIM(bd.codigo))                                                     AS codigo_destino,
                td.cantidad * COALESCE(NULLIF(ue.equivalencia, 0), 1)                      AS cantidad_bodega,
                u.username                                                                  AS usuario_bodega,
                CONVERT(varchar(16), TRY_CAST(td.created_at AS datetime2), 120)            AS fecha_bodega,
                CASE WHEN tdt.cantidad IS NOT NULL
                     THEN tdt.cantidad * COALESCE(NULLIF(ue.equivalencia, 0), 1)
                     ELSE NULL END                                                          AS cantidad_tienda,
                ut.username                                                                 AS usuario_tienda,
                CONVERT(varchar(16), TRY_CAST(tdt.created_at AS datetime2), 120)           AS fecha_tienda,
                (ISNULL(tdt.cantidad, 0) - td.cantidad) * COALESCE(NULLIF(ue.equivalencia, 0), 1) AS dif_tienda_bodega
            FROM traspasodetalle td
            INNER JOIN traspaso t    ON t.id          = td.idTraspaso AND t.idEstado <> 2
            LEFT  JOIN bodegas bo    ON bo.id         = t.idBodegaOrigen
            LEFT  JOIN bodegas bd    ON bd.id         = t.idBodegaDestino
            LEFT  JOIN [user]  u     ON u.id          = td.created_by
            LEFT  JOIN traspasodetalletienda tdt
                                     ON tdt.idTraspaso = td.idTraspaso
                                    AND tdt.idItem     = td.idItem
            LEFT  JOIN [user]  ut    ON ut.id         = tdt.created_by
            LEFT  JOIN item    i     ON i.id          = td.idItem
            LEFT  JOIN unidadempaque ue ON ue.codigo  = i.unidadEmpaque
            LEFT  JOIN documentosiesa ds ON ds.idGruma = t.id
            WHERE td.idItem = :idItem
              AND TRY_CAST(t.created_at AS datetime2) BETWEEN :dtDesde AND :dtHasta
            ORDER BY td.created_at DESC
        ";

        return Yii::$app->db->createCommand($sql, [
            ':idItem'  => (int)$idItem,
            ':dtDesde' => $dtDesde,
            ':dtHasta' => $dtHasta,
        ])->queryAll();
    }

    /**
     * Facturas (del proveedor) asociadas a un item en el período.
     * Enlace: transferenciaordencompraexcel.numeroFactura = facturaentregamercancia.numeroFactura
     * Muestra cant_entrada (SIESA) y cant_conteo (legalizados en GRUMALog) por factura.
     */
    public function searchOcs($params, $idItem)
    {
        $this->load($params);

        $tz    = new \DateTimeZone('America/Bogota');
        $desde = $this->desde ?: (new \DateTime('now', $tz))->format('Y-m-01');
        $hasta = $this->hasta ?: (new \DateTime('now', $tz))->format('Y-m-d');

        $sql = "
            SELECT
                LTRIM(RTRIM(toce.numeroFactura))                                           AS numero_factura,
                fac.id                                                                     AS idFactura,
                CASE WHEN fac.id IS NULL
                     THEN '(sin registro GRUMALog)'
                     ELSE ISNULL(
                              LTRIM(RTRIM(td_oc.codigo)) + '-' + CAST(CAST(oc.consecutivo AS int) AS varchar(20)),
                              '(sin OC GRUMALog)'
                          )
                END                                                                        AS oc_gruma,
                CASE WHEN fac.id IS NULL
                     THEN '(sin registro GRUMALog)'
                     ELSE ISNULL(el.nombre, '(sin estado)')
                END                                                                        AS estado_legalizacion,
                aem.idEstadoLegalizacion,
                SUM(toce.cantidadBase)                                                     AS cant_entrada,
                ISNULL(cont_fac.cant_conteo, 0)                                            AS cant_conteo,
                (ISNULL(cont_fac.cant_conteo, 0) - SUM(toce.cantidadBase))                 AS diferencia
            FROM transferenciaordencompraexcel toce
            LEFT  JOIN facturaentregamercancia fac
                    ON LTRIM(RTRIM(fac.numeroFactura)) = LTRIM(RTRIM(toce.numeroFactura))
            LEFT  JOIN agendaentregamercancia aem ON aem.id = fac.idAgendaEntregaMercancia
            LEFT  JOIN ordendecompra oc            ON oc.id  = aem.idOrdenCompra
            LEFT  JOIN tipodocumento td_oc         ON td_oc.id = oc.idTipoDocumento
            LEFT  JOIN estadolegalizacion el        ON el.id = aem.idEstadoLegalizacion
            LEFT  JOIN (
                SELECT fac2.id AS idFactura,
                       SUM(cem.unidadesConteo * COALESCE(NULLIF(ue_f.equivalencia, 0), 1)) AS cant_conteo
                FROM conteoentregamercancia cem
                INNER JOIN programacionentregamercancia pem_f ON pem_f.id = cem.idProgramacionEntregaMercancia
                INNER JOIN agendaentregamercancia aem_f        ON aem_f.id = pem_f.idAgendaEntregaMercancia
                INNER JOIN facturaentregamercancia fac2        ON fac2.idAgendaEntregaMercancia = aem_f.id
                LEFT  JOIN item i_f          ON i_f.id      = cem.idItem
                LEFT  JOIN unidadempaque ue_f ON ue_f.codigo = i_f.unidadEmpaque
                WHERE cem.idItem = :idItemCont
                  AND aem_f.idEstadoLegalizacion = 2
                GROUP BY fac2.id
            ) cont_fac ON cont_fac.idFactura = fac.id
            WHERE toce.item = :idItemOcs
              AND toce.fechaDocumento BETWEEN :desdeOcs AND :hastaOcs
            GROUP BY LTRIM(RTRIM(toce.numeroFactura)), fac.id, oc.id, oc.consecutivo,
                     td_oc.codigo, aem.idEstadoLegalizacion, el.nombre, cont_fac.cant_conteo
            ORDER BY LTRIM(RTRIM(toce.numeroFactura))
        ";

        return Yii::$app->db->createCommand($sql, [
            ':idItemOcs'  => (int)$idItem,
            ':desdeOcs'   => $desde,
            ':hastaOcs'   => $hasta,
            ':idItemCont' => (int)$idItem,
        ])->queryAll();
    }

    /**
     * Detalle de conteos por factura e item: agrupado por SKU (talla+color).
     * Agrupa todos los barcodes con el mismo código SIESA.
     * Muestra todos los conteos (sin filtro de legalización) para diagnóstico.
     * También retorna la cantidadPedida de la OC GRUMALog para comparar.
     */
    public function searchConteoOc($idFactura, $idItem)
    {
        // Obtener el código SIESA del item para agrupar todos sus barcodes
        $itemCodigo = (int)\Yii::$app->db->createCommand(
            "SELECT CAST(item AS bigint) FROM item WHERE id = :id",
            [':id' => (int)$idItem]
        )->queryScalar();

        // $idFactura = 0 → modo "todos los conteos del item" (sin filtro de factura)
        $cantPedida = 0;
        if ($idFactura > 0) {
            $sqlPedida = "
                SELECT ISNULL(SUM(ocd.cantidadPedida), 0) AS cant_pedida
                FROM ordendecompradetalle ocd
                INNER JOIN item i_ped          ON i_ped.id = ocd.idItem
                INNER JOIN agendaentregamercancia aem_q ON aem_q.idOrdenCompra = ocd.idOrdenCompra
                INNER JOIN facturaentregamercancia fac_q ON fac_q.idAgendaEntregaMercancia = aem_q.id
                WHERE fac_q.id = :idFacturaPed
                  AND CAST(i_ped.item AS bigint) = :itemCodigoPed
            ";
            $cantPedida = (int)\Yii::$app->db->createCommand($sqlPedida, [
                ':idFacturaPed'  => (int)$idFactura,
                ':itemCodigoPed' => $itemCodigo,
            ])->queryScalar();
        }

        // JOIN a factura: solo cuando se filtra por factura específica
        if ($idFactura > 0) {
            $facJoin   = "INNER JOIN facturaentregamercancia fac_coc
                                  ON fac_coc.idAgendaEntregaMercancia = aem_coc.id
                                 AND fac_coc.id = :idFacturaCoc";
            $legFilter = '';
            $params    = [':idFacturaCoc' => (int)$idFactura, ':itemCodigo' => $itemCodigo];
        } else {
            // Sin factura → todos los conteos legalizados del item
            $facJoin   = '';
            $legFilter = 'AND aem_coc.idEstadoLegalizacion = 2';
            $params    = [':itemCodigo' => $itemCodigo];
        }

        $sql = "
            SELECT
                CAST(i_coc.item AS bigint)                                                       AS item_codigo,
                ISNULL(LTRIM(RTRIM(ta_coc.codigo)), '—')                                         AS talla,
                ISNULL(LTRIM(RTRIM(co_coc.codigo)), '—')                                         AS color,
                ISNULL(
                    LTRIM(RTRIM(td_oc.codigo)) + '-' + CAST(CAST(oc_coc.consecutivo AS int) AS varchar(20)),
                    '(sin OC GRUMALog)'
                )                                                                                AS oc_gruma,
                SUM(cem_coc.unidadesConteo)                                                      AS cant_paquetes,
                CAST(MAX(COALESCE(NULLIF(ue_coc.equivalencia, 0), 1)) AS decimal(10,2))          AS equivalencia,
                SUM(cem_coc.unidadesConteo * COALESCE(NULLIF(ue_coc.equivalencia, 0), 1))       AS cant_unidades
            FROM conteoentregamercancia cem_coc
            INNER JOIN programacionentregamercancia pem_coc ON pem_coc.id = cem_coc.idProgramacionEntregaMercancia
            INNER JOIN agendaentregamercancia aem_coc        ON aem_coc.id = pem_coc.idAgendaEntregaMercancia
            {$facJoin}
            LEFT  JOIN ordendecompra   oc_coc  ON oc_coc.id    = aem_coc.idOrdenCompra
            LEFT  JOIN tipodocumento   td_oc   ON td_oc.id     = oc_coc.idTipoDocumento
            LEFT  JOIN item            i_coc   ON i_coc.id     = cem_coc.idItem
            LEFT  JOIN talla           ta_coc  ON ta_coc.id    = i_coc.idTalla
            LEFT  JOIN color           co_coc  ON co_coc.id    = i_coc.idColor
            LEFT  JOIN unidadempaque   ue_coc  ON ue_coc.codigo = i_coc.unidadEmpaque
            WHERE CAST(i_coc.item AS bigint) = :itemCodigo
              {$legFilter}
            GROUP BY i_coc.item, ta_coc.codigo, co_coc.codigo,
                     oc_coc.id, oc_coc.consecutivo, td_oc.codigo
            ORDER BY i_coc.item, ta_coc.codigo, co_coc.codigo, oc_coc.consecutivo
        ";

        $detalle = \Yii::$app->db->createCommand($sql, $params)->queryAll();

        return [
            'cant_pedida' => $cantPedida,
            'detalle'     => $detalle,
        ];
    }
}
