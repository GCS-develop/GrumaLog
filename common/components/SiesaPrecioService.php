<?php

namespace common\components;

use Yii;

class SiesaPrecioService
{
    /**
     * Retorna items con extensiones y el precio "a imprimir":
     * - si hay vigente hoy -> vigente
     * - si no hay vigente -> futuro más cercano
     *
     * @param string $term
     * @param int $idCia
     * @param string $listaPvp
     * @param int $limit
     * @return array
     */
    public function buscar(string $term, int $idCia = 7, string $listaPvp = '001', int $limit = 200): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $db = Yii::$app->dbSiesa;

        $termLike = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';

        // Nota: TOP con parámetro no siempre funciona en SQL Server con bind.
        // Por seguridad dejamos el TOP fijo en 200 y controlas desde UI/paginación.
        $sql = "
DECLARE @id_cia SMALLINT = :id_cia;
DECLARE @id_lista_pvp CHAR(10) = :lista_pvp;
DECLARE @fecha_consulta DATETIME = GETDATE();

;WITH cte_ext AS (
    SELECT
        ext.f121_id_cia              AS id_cia,
        ext.f121_rowid_item          AS rowid_item,
        itm.f120_id                  AS item,
        itm.f120_referencia          AS referencia,
        itm.f120_descripcion         AS descItem,
        itm.f120_descripcion_corta   AS descCorta,
        ext.f121_id_ext1_detalle     AS color,
        ext.f121_id_ext2_detalle     AS talla
    FROM dbo.t121_mc_items_extensiones ext
    INNER JOIN dbo.t120_mc_items itm
        ON itm.f120_id_cia = ext.f121_id_cia
       AND itm.f120_rowid  = ext.f121_rowid_item
    WHERE ext.f121_id_cia = @id_cia
      AND ext.f121_ind_estado = 1
),
cte_match AS (
    SELECT TOP (200)
        e.*
    FROM cte_ext e
    WHERE
        CAST(e.item AS VARCHAR(50)) LIKE :termLike
        OR e.referencia LIKE :termLike
        OR e.descItem LIKE :termLike
        OR e.descCorta LIKE :termLike
        OR e.color LIKE :termLike
        OR e.talla LIKE :termLike
),
cte_precio AS (
    SELECT
        m.rowid_item,
        pe.estado_precio,
        pe.fecha_activacion,
        pe.fecha_inactivacion,
        pe.precio_pvp,
        pe.valor_impuesto,
        pe.precio_total
    FROM cte_match m
    OUTER APPLY (
        SELECT TOP(1)
            CASE
                WHEN p.f126_fecha_activacion <= @fecha_consulta
                 AND (p.f126_fecha_inactivacion IS NULL OR p.f126_fecha_inactivacion > @fecha_consulta)
                THEN 'VIGENTE'
                ELSE 'FUTURO'
            END AS estado_precio,
            p.f126_fecha_activacion AS fecha_activacion,
            p.f126_fecha_inactivacion AS fecha_inactivacion,
            p.f126_precio AS precio_pvp,
            ISNULL((
                SELECT SUM(i.f127_valor_impuesto)
                FROM dbo.t127_mc_items_precios_imp i
                WHERE i.f127_rowid_item_precio = p.f126_rowid
            ),0) AS valor_impuesto,
            p.f126_precio + ISNULL((
                SELECT SUM(i.f127_valor_impuesto)
                FROM dbo.t127_mc_items_precios_imp i
                WHERE i.f127_rowid_item_precio = p.f126_rowid
            ),0) AS precio_total
        FROM dbo.t126_mc_items_precios p
        WHERE p.f126_id_cia = @id_cia
          AND p.f126_id_lista_precio = @id_lista_pvp
          AND p.f126_rowid_item = m.rowid_item
          AND (
                (p.f126_fecha_activacion <= @fecha_consulta AND (p.f126_fecha_inactivacion IS NULL OR p.f126_fecha_inactivacion > @fecha_consulta))
                OR (p.f126_fecha_activacion > @fecha_consulta)
          )
        ORDER BY
            CASE
                WHEN p.f126_fecha_activacion <= @fecha_consulta
                 AND (p.f126_fecha_inactivacion IS NULL OR p.f126_fecha_inactivacion > @fecha_consulta)
                THEN 0 ELSE 1 END,
            p.f126_fecha_activacion ASC,
            p.f126_rowid DESC
    ) pe
)
SELECT
    m.item,
    m.referencia,
    m.descItem,
    m.descCorta,
    m.color      AS detalleExt1,
    m.talla      AS detalleExt2,
    m.rowid_item AS rowidItem,
    pr.estado_precio AS estadoPrecio,
    pr.precio_total  AS precio,
    pr.precio_pvp    AS precioBase,
    pr.valor_impuesto AS iva,
    pr.fecha_activacion AS fechaActivacion,
    pr.fecha_inactivacion AS fechaInactivacion
FROM cte_match m
LEFT JOIN cte_precio pr
    ON pr.rowid_item = m.rowid_item
ORDER BY m.item ASC, m.color ASC, m.talla ASC;
";

        $rows = $db->createCommand($sql, [
            ':id_cia' => $idCia,
            ':lista_pvp' => $listaPvp,
            ':termLike' => $termLike,
        ])->queryAll();

        // Normaliza a tipos seguros
        foreach ($rows as &$r) {
            $r['rowidItem'] = (int)($r['rowidItem'] ?? 0);
            $r['item']      = (string)($r['item'] ?? '');
            $r['precio']    = isset($r['precio']) ? (float)$r['precio'] : null;
            $r['iva']       = isset($r['iva']) ? (float)$r['iva'] : null;
        }

        return $rows;
    }

    /**
     * Precio "a imprimir" para un rowid_item:
     * vigente hoy o futuro más cercano.
     */
    public function precioImprimirPorRowidItem(int $rowidItem, int $idCia = 7, string $listaPvp = '001'): ?array
    {
        if ($rowidItem <= 0) return null;

        $db = Yii::$app->dbSiesa;

        $sql = "
DECLARE @fecha_consulta DATETIME = GETDATE();

SELECT TOP(1)
    p.f126_rowid_item AS rowidItem,
    p.f126_precio AS precioBase,
    ISNULL((
        SELECT SUM(i.f127_valor_impuesto)
        FROM dbo.t127_mc_items_precios_imp i
        WHERE i.f127_rowid_item_precio = p.f126_rowid
    ),0) AS iva,
    p.f126_precio + ISNULL((
        SELECT SUM(i.f127_valor_impuesto)
        FROM dbo.t127_mc_items_precios_imp i
        WHERE i.f127_rowid_item_precio = p.f126_rowid
    ),0) AS precio,
    CASE
        WHEN p.f126_fecha_activacion <= @fecha_consulta
         AND (p.f126_fecha_inactivacion IS NULL OR p.f126_fecha_inactivacion > @fecha_consulta)
        THEN 'VIGENTE'
        ELSE 'FUTURO'
    END AS estadoPrecio,
    p.f126_fecha_activacion AS fechaActivacion
FROM dbo.t126_mc_items_precios p
WHERE p.f126_id_cia = :id_cia
  AND p.f126_id_lista_precio = :lista_pvp
  AND p.f126_rowid_item = :rowid_item
  AND (
        (p.f126_fecha_activacion <= @fecha_consulta AND (p.f126_fecha_inactivacion IS NULL OR p.f126_fecha_inactivacion > @fecha_consulta))
        OR (p.f126_fecha_activacion > @fecha_consulta)
  )
ORDER BY
    CASE
        WHEN p.f126_fecha_activacion <= @fecha_consulta
         AND (p.f126_fecha_inactivacion IS NULL OR p.f126_fecha_inactivacion > @fecha_consulta)
        THEN 0 ELSE 1 END,
    p.f126_fecha_activacion ASC,
    p.f126_rowid DESC;
";

        $row = $db->createCommand($sql, [
            ':id_cia' => $idCia,
            ':lista_pvp' => $listaPvp,
            ':rowid_item' => $rowidItem,
        ])->queryOne();

        if (!$row) return null;

        $row['rowidItem'] = (int)$row['rowidItem'];
        $row['precio'] = (float)$row['precio'];
        $row['iva'] = (float)$row['iva'];
        $row['precioBase'] = (float)$row['precioBase'];

        return $row;
    }

    public function buscarConHistorial(string $term, int $idCia = 7, string $listaPvp = '001'): array
    {
        $term = trim($term);
        if ($term === '') return [];

        $db = Yii::$app->dbSiesa;
        $termLike = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';

        $sql = "
DECLARE @id_cia SMALLINT = :id_cia;
DECLARE @id_lista_pvp CHAR(10) = :lista_pvp;
DECLARE @fecha_consulta DATETIME = GETDATE();

;WITH cte_ext AS (
    SELECT
        ext.f121_id_cia              AS id_cia,
        ext.f121_rowid_item          AS rowid_item,
        itm.f120_id                  AS item,
        itm.f120_referencia          AS referencia,
        itm.f120_descripcion         AS descItem,
        itm.f120_descripcion_corta   AS descCorta,
        ext.f121_id_ext1_detalle     AS color,
        ext.f121_id_ext2_detalle     AS talla
    FROM dbo.t121_mc_items_extensiones ext
    INNER JOIN dbo.t120_mc_items itm
        ON itm.f120_id_cia = ext.f121_id_cia
       AND itm.f120_rowid  = ext.f121_rowid_item
    WHERE ext.f121_id_cia = @id_cia
      AND ext.f121_ind_estado = 1
),
cte_match AS (
    SELECT TOP (200) *
    FROM cte_ext e
    WHERE
        CAST(e.item AS VARCHAR(50)) LIKE :termLike
        OR e.referencia LIKE :termLike
        OR e.descItem LIKE :termLike
        OR e.descCorta LIKE :termLike
        OR e.color LIKE :termLike
        OR e.talla LIKE :termLike
),
cte_hist AS (
    SELECT
        m.rowid_item,

        MAX(CASE WHEN h.rn = 1 THEN h.precio_total END) AS precio1,
        MAX(CASE WHEN h.rn = 1 THEN h.estado_precio END) AS estado1,
        MAX(CASE WHEN h.rn = 1 THEN h.fecha_activacion END) AS fecha1,

        MAX(CASE WHEN h.rn = 2 THEN h.precio_total END) AS precio2,
        MAX(CASE WHEN h.rn = 2 THEN h.estado_precio END) AS estado2,
        MAX(CASE WHEN h.rn = 2 THEN h.fecha_activacion END) AS fecha2,

        MAX(CASE WHEN h.rn = 3 THEN h.precio_total END) AS precio3,
        MAX(CASE WHEN h.rn = 3 THEN h.estado_precio END) AS estado3,
        MAX(CASE WHEN h.rn = 3 THEN h.fecha_activacion END) AS fecha3,

        -- precio a imprimir (vigente hoy, si no hay, el futuro más cercano)
        MAX(CASE WHEN h.rn_imp = 1 THEN h.precio_total END) AS precioImprimir,
        MAX(CASE WHEN h.rn_imp = 1 THEN h.estado_precio END) AS estadoImprimir,
        MAX(CASE WHEN h.rn_imp = 1 THEN h.fecha_activacion END) AS fechaImprimir

    FROM cte_match m
    OUTER APPLY (
        SELECT
            ROW_NUMBER() OVER (ORDER BY p.f126_fecha_activacion DESC, p.f126_rowid DESC) AS rn,

            ROW_NUMBER() OVER (
                ORDER BY
                    CASE
                        WHEN p.f126_fecha_activacion <= @fecha_consulta
                         AND (p.f126_fecha_inactivacion IS NULL OR p.f126_fecha_inactivacion > @fecha_consulta)
                        THEN 0 ELSE 1 END,
                    p.f126_fecha_activacion ASC,
                    p.f126_rowid DESC
            ) AS rn_imp,

            p.f126_fecha_activacion AS fecha_activacion,
            CASE
                WHEN p.f126_fecha_activacion <= @fecha_consulta
                 AND (p.f126_fecha_inactivacion IS NULL OR p.f126_fecha_inactivacion > @fecha_consulta)
                THEN 'VIGENTE'
                WHEN p.f126_fecha_activacion > @fecha_consulta
                THEN 'FUTURO'
                ELSE 'VENCIDO'
            END AS estado_precio,

            p.f126_precio
            + ISNULL((
                SELECT SUM(i.f127_valor_impuesto)
                FROM dbo.t127_mc_items_precios_imp i
                WHERE i.f127_rowid_item_precio = p.f126_rowid
            ), 0) AS precio_total

        FROM dbo.t126_mc_items_precios p
        WHERE p.f126_id_cia = @id_cia
          AND p.f126_id_lista_precio = @id_lista_pvp
          AND p.f126_rowid_item = m.rowid_item
    ) h
    GROUP BY m.rowid_item
)
SELECT
    m.item,
    m.referencia,
    m.descItem,
    m.descCorta,
    m.color AS detalleExt1,
    m.talla AS detalleExt2,
    m.rowid_item AS rowidItem,

    h.precioImprimir,
    h.estadoImprimir,
    h.fechaImprimir,

    h.precio1, h.estado1, h.fecha1,
    h.precio2, h.estado2, h.fecha2,
    h.precio3, h.estado3, h.fecha3

FROM cte_match m
LEFT JOIN cte_hist h
    ON h.rowid_item = m.rowid_item
ORDER BY m.item ASC, m.color ASC, m.talla ASC;
";

        $rows = $db->createCommand($sql, [
            ':id_cia' => $idCia,
            ':lista_pvp' => $listaPvp,
            ':termLike' => $termLike,
        ])->queryAll();

        foreach ($rows as &$r) {
            $r['rowidItem'] = (int)($r['rowidItem'] ?? 0);

            foreach (['precioImprimir', 'precio1', 'precio2', 'precio3'] as $k) {
                $r[$k] = isset($r[$k]) ? (float)$r[$k] : null;
            }
        }

        return $rows;
    }
}
